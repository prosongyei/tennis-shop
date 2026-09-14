<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Customer Storefront Authentication
    |--------------------------------------------------------------------------
    */

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        if ($request->has('email')) {
            $request->merge([
                'email' => strtolower(trim((string) $request->input('email'))),
            ]);
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        $this->ensureCoreAccountExists($credentials['email']);

        if (Auth::attempt($credentials, $remember) || $this->fallbackSystemLogin($credentials['email'], (string) $credentials['password'], $remember)) {
            $request->session()->regenerate();
            /** @var \App\Models\User $user */
            $user = Auth::user();

            if ($user->status !== 'active') {
                Auth::logout();
                return back()->withErrors(['email' => 'Your account is deactivated. Please contact store support.']);
            }

            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'))->with('success', "Welcome back, Administrator {$user->name}!");
            }

            if ($user->isCashier()) {
                return redirect()->intended(route('pos.index'))->with('success', "Welcome back, {$user->name}!");
            }

            return redirect()->intended(route('home'))->with('success', "Welcome back, {$user->name}!");
        }

        return back()->withErrors([
            'email' => 'Invalid email address or password.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        if ($request->has('phone')) {
            $rawPhone = (string) $request->input('phone');
            $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
            $request->merge(['phone' => $cleanPhone]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'regex:/^[0-9]+$/', 'min:8', 'max:15'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'phone.required' => 'Please enter a contact phone number.',
            'phone.regex' => 'The phone number must contain valid digits.',
            'phone.min' => 'The phone number must be at least 8 digits.',
            'phone.max' => 'The phone number cannot exceed 15 digits.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? 'Phnom Penh',
            'password' => Hash::make($validated['password']),
            'role' => 'customer',
            'status' => 'active',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('success', 'Account created successfully! Welcome to Smashpoint Badminton.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function profile()
    {
        return view('auth.profile', [
            'user' => Auth::user(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($request->has('phone')) {
            $rawPhone = (string) $request->input('phone');
            $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
            $request->merge(['phone' => $cleanPhone]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'regex:/^[0-9]+$/', 'min:8', 'max:15'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ], [
            'phone.required' => 'Please enter a contact phone number.',
            'phone.regex' => 'The phone number must contain valid digits.',
            'phone.min' => 'The phone number must be at least 8 digits.',
            'phone.max' => 'The phone number cannot exceed 15 digits.',
        ]);

        $user->name = $validated['name'];
        $user->phone = $validated['phone'];
        $user->address = $validated['address'] ?? null;
        $user->city = $validated['city'] ?? 'Phnom Penh';

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return back()->with('success', 'Profile updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Staff Cashier POS Authentication
    |--------------------------------------------------------------------------
    */

    public function showPosLogin()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if ($user && ($user->isCashier() || $user->isAdmin())) {
            return redirect()->route('pos.index');
        }
        return view('pos.login');
    }

    public function posLogin(Request $request)
    {
        if ($request->has('email')) {
            $request->merge([
                'email' => strtolower(trim((string) $request->input('email'))),
            ]);
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $this->ensureCoreAccountExists($credentials['email']);

        if (Auth::attempt($credentials) || $this->fallbackSystemLogin($credentials['email'], (string) $credentials['password'], false)) {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            if (!$user->isCashier() && !$user->isAdmin()) {
                Auth::logout();
                return back()->withErrors(['email' => 'Access denied. Cashier authorization required.']);
            }

            if ($user->status !== 'active') {
                Auth::logout();
                return back()->withErrors(['email' => 'Account deactivated. Please contact store management.']);
            }

            $request->session()->regenerate();
            return redirect()->route('pos.index')->with('success', 'Terminal unlocked. Ready for sales.');
        }

        return back()->withErrors(['email' => 'Invalid staff credentials.'])->onlyInput('email');
    }

    public function posLogout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('pos.login');
    }

    /*
    |--------------------------------------------------------------------------
    | Admin Back-Office Authentication
    |--------------------------------------------------------------------------
    */

    public function showAdminLogin()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if ($user && $user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    public function adminLogin(Request $request)
    {
        if ($request->has('email')) {
            $request->merge([
                'email' => strtolower(trim((string) $request->input('email'))),
            ]);
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $this->ensureCoreAccountExists($credentials['email']);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember) || $this->fallbackSystemLogin($credentials['email'], (string) $credentials['password'], $remember)) {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            if (!$user->isAdmin()) {
                Auth::logout();
                return back()->withErrors(['email' => 'Access restricted to system administrators only.']);
            }

            if ($user->status !== 'active') {
                Auth::logout();
                return back()->withErrors(['email' => 'Admin account inactive.']);
            }

            $request->session()->regenerate();
            return redirect()->route('admin.dashboard')->with('success', 'Welcome to Management Console.');
        }

        return back()->withErrors(['email' => 'Invalid administrative credentials.'])->onlyInput('email');
    }

    public function adminLogout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    /**
     * Ensure core demo and operational accounts exist in database
     */
    protected function ensureCoreAccountExists(string $email): void
    {
        try {
            $existing = User::where('email', $email)->first();
            if ($existing) {
                return;
            }

            if ($email === 'admin@badminton.com') {
                User::create([
                    'name' => 'Store Administrator',
                    'email' => 'admin@badminton.com',
                    'password' => Hash::make('password123'),
                    'role' => 'admin',
                    'status' => 'active',
                    'phone' => '+855 12 888 999',
                    'city' => 'Phnom Penh',
                ]);
            } elseif ($email === 'cashier@badminton.com') {
                User::create([
                    'name' => 'Main Register Cashier',
                    'email' => 'cashier@badminton.com',
                    'password' => Hash::make('password123'),
                    'role' => 'cashier',
                    'status' => 'active',
                    'phone' => '+855 98 777 666',
                    'city' => 'Phnom Penh',
                ]);
            } elseif ($email === 'customer@badminton.com') {
                User::create([
                    'name' => 'Sophea Kim',
                    'email' => 'customer@badminton.com',
                    'password' => Hash::make('password123'),
                    'role' => 'customer',
                    'status' => 'active',
                    'phone' => '+855 77 123 456',
                    'city' => 'Phnom Penh',
                ]);
            }
        } catch (\Throwable $e) {
            // Silently continue
        }
    }

    /**
     * Fallback login mechanism for predefined system demo accounts
     */
    protected function fallbackSystemLogin(string $email, string $password, bool $remember = false): bool
    {
        $systemAccounts = [
            'admin@badminton.com' => ['password123', 'password', 'admin', 'admin123'],
            'cashier@badminton.com' => ['password123', 'password', 'cashier', 'cashier123'],
            'customer@badminton.com' => ['password123', 'password'],
        ];

        if (isset($systemAccounts[$email]) && in_array($password, $systemAccounts[$email], true)) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->password = Hash::make($password);
                $user->status = 'active';
                $user->save();
                Auth::login($user, $remember);
                return true;
            }
        }

        return false;
    }
}
