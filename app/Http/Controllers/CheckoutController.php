<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Discount;
use App\Services\OrderService;
use App\Services\PaymentService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    protected OrderService $orderService;
    protected PaymentService $paymentService;

    public function __construct(OrderService $orderService, PaymentService $paymentService)
    {
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
    }

    protected function getActiveCart(Request $request): Cart
    {
        if (Auth::check()) {
            return Cart::firstOrCreate(['user_id' => Auth::id()]);
        }
        return Cart::firstOrCreate(['session_id' => $request->session()->getId(), 'user_id' => null]);
    }

    public function index(Request $request)
    {
        $cart = $this->getActiveCart($request);
        $cart->load(['items.product.brand', 'items.variant']);

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('warning', 'Your shopping cart is empty.');
        }

        $user = Auth::user();
        $provinces = [
            'Phnom Penh' => ['fee' => 1.50, 'label' => 'Phnom Penh (Same-Day Delivery - $1.50)'],
            'Kandal' => ['fee' => 2.00, 'label' => 'Kandal ($2.00)'],
            'Siem Reap' => ['fee' => 2.50, 'label' => 'Siem Reap ($2.50)'],
            'Battambang' => ['fee' => 2.50, 'label' => 'Battambang ($2.50)'],
            'Sihanoukville' => ['fee' => 2.50, 'label' => 'Preah Sihanouk ($2.50)'],
            'Kampot' => ['fee' => 2.50, 'label' => 'Kampot ($2.50)'],
            'Takeo' => ['fee' => 2.50, 'label' => 'Takeo ($2.50)'],
            'Kampong Cham' => ['fee' => 2.50, 'label' => 'Kampong Cham ($2.50)'],
            'Other Province' => ['fee' => 2.50, 'label' => 'Other Province via Virak Buntham / J&T ($2.50)'],
        ];

        return view('checkout.index', compact('cart', 'user', 'provinces'));
    }

    public function process(Request $request)
    {
        $cart = $this->getActiveCart($request);
        $cart->load(['items.product', 'items.variant']);

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your shopping cart is empty.');
        }

        // Sanitize phone number to digits before validation
        if ($request->has('customer_phone')) {
            $rawPhone = (string) $request->input('customer_phone');
            $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
            $request->merge(['customer_phone' => $cleanPhone]);
        }

        $rules = [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'regex:/^[0-9]+$/', 'min:8', 'max:15'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'province_city' => ['required', 'string'],
            'delivery_address' => ['required', 'string', 'max:500'],
            'delivery_method' => ['required', 'in:delivery,pickup'],
            'payment_method' => ['required', 'in:khqr,credit_card,cash_on_delivery'],
            'customer_note' => ['nullable', 'string', 'max:500'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
        ];

        if ($request->input('payment_method') === 'credit_card') {
            $rules['cardholder_name'] = ['required', 'string', 'min:3', 'max:100'];
            $rules['card_number'] = ['required', 'string', 'min:12', 'max:25'];
            $rules['card_expiry'] = ['required', 'string', 'max:7'];
            $rules['card_cvv'] = ['required', 'string', 'min:3', 'max:4'];
        }

        $messages = [
            'customer_phone.required' => 'Please enter a contact phone number.',
            'customer_phone.regex' => 'The phone number must contain valid digits.',
            'customer_phone.min' => 'The phone number must be at least 8 digits.',
            'customer_phone.max' => 'The phone number cannot exceed 15 digits.',
        ];

        $validated = $request->validate($rules, $messages);

        // Calculate delivery fee
        $deliveryFee = 0.00;
        if ($validated['delivery_method'] === 'delivery') {
            $deliveryFee = ($validated['province_city'] === 'Phnom Penh') ? 1.50 : 2.50;
        }

        // Coupon calculation if any
        $discountAmount = 0.00;
        if (!empty($validated['coupon_code'])) {
            $couponCode = strtoupper(trim($validated['coupon_code']));
            $coupon = Discount::where('code', $couponCode)
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                })
                ->first();

            if ($coupon) {
                if ($coupon->type === 'percentage') {
                    $discountAmount = ($cart->subtotal * ($coupon->value / 100));
                } else {
                    $discountAmount = min($cart->subtotal, (float) $coupon->value);
                }
            } elseif ($couponCode === 'TOS10' || $couponCode === 'SMASH10') {
                $discountAmount = ($cart->subtotal * 0.10);
            } elseif ($couponCode === 'WELCOME5') {
                $discountAmount = min($cart->subtotal, 5.00);
            }
        }

        try {
            $order = $this->orderService->createOrderFromCart(
                $cart,
                [
                    'name' => $validated['customer_name'],
                    'phone' => $validated['customer_phone'],
                    'email' => $validated['customer_email'],
                    'address' => $validated['delivery_address'],
                    'province_city' => $validated['province_city'],
                    'delivery_method' => $validated['delivery_method'],
                    'note' => $validated['customer_note'],
                ],
                $validated['payment_method'],
                $deliveryFee,
                $discountAmount
            );

            session([
                'last_order_number' => $order->order_number,
                'last_order_id' => $order->id,
            ]);

            // If Credit Card, simulate instant 3D-Secure approval
            if ($validated['payment_method'] === 'credit_card') {
                $rawCard = preg_replace('/\s+/', '', (string) $request->input('card_number'));
                $lastFour = substr($rawCard, -4);
                $cardBrand = 'Visa/MasterCard';
                if (str_starts_with($rawCard, '4')) {
                    $cardBrand = 'Visa';
                } elseif (str_starts_with($rawCard, '5')) {
                    $cardBrand = 'MasterCard';
                } elseif (str_starts_with($rawCard, '35')) {
                    $cardBrand = 'JCB';
                }

                $transactionId = 'AUTH-' . strtoupper(Str::random(10));
                $this->paymentService->markOrderAsPaid(
                    $order,
                    'credit_card',
                    $transactionId,
                    "{$cardBrand} ending in {$lastFour} (Cardholder: {$request->input('cardholder_name')})"
                );

                return redirect()->route('orders.show', $order->order_number)
                    ->with('success', "Payment of $" . number_format($order->total_amount, 2) . " approved via {$cardBrand} (•••• {$lastFour})! Order #{$order->order_number} is confirmed.");
            }

            // If KHQR selected, send customer directly to live KHQR screen
            if ($validated['payment_method'] === 'khqr') {
                return redirect()->route('payment.khqr', $order->order_number);
            }

            return redirect()->route('orders.show', $order->order_number)
                ->with('success', 'Order placed successfully! Thank you for your purchase.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Checkout failed: ' . $e->getMessage());
        }
    }
}
