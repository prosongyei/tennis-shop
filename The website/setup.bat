@echo off
cd /d "%~dp0"
echo ===================================================
echo   INITIALIZING BADMINTON SHOP LARAVEL APPLICATION   
echo ===================================================
echo.
echo [1/5] Installing Laravel dependencies...
call composer install --no-interaction

echo.
echo [2/5] Generating Application Key...
call php artisan key:generate --force

echo.
echo [2.5/5] Initializing Clean SQLite Database...
call php test_write.php

echo.
echo [3/5] Running Database Migrations...
call php artisan migrate --force

echo.
echo [4/5] Seeding Products, Categories, Brands and Accounts...
call php artisan db:seed --force

echo.
echo [5/5] Creating Storage Symbolic Link...
call php artisan storage:link

echo.
echo ===================================================
echo   SETUP COMPLETE! Starting Local Web Server...
echo ===================================================
echo   Storefront:      http://localhost:8000
echo   POS Register:    http://localhost:8000/pos
echo   Admin Dashboard: http://localhost:8000/admin
echo.
echo   Demo Accounts:
echo   - Admin:    admin@badminton.com / password123
echo   - Cashier:  cashier@badminton.com / password123
echo   - Customer: customer@badminton.com / password123
echo ===================================================
echo.
call php artisan serve
pause
