@echo off
title SIPRES SMP NEGERI 2 MIJEN - Local Server
echo =================================================================
echo        SIPRES - SISTEM INFORMASI PRESENSI SMP NEGERI 2 MIJEN
echo                       Mode Server Lokal
echo =================================================================
echo.
echo 1. Memeriksa Database MariaDB...
tasklist /FI "IMAGENAME eq mariadbd.exe" 2>NUL | find /I /N "mariadbd.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo    Database MariaDB sudah berjalan aktif.
) else (
    echo    Menjalankan MariaDB server...
    start /B "" "C:\Program Files\MariaDB 13.0\bin\mariadbd.exe" --console
    timeout /t 3 /nobreak >nul
)

echo 2. Menjalankan Server Laravel (http://127.0.0.1:8000)...
echo.
echo Aplikasi dapat dibuka di browser: http://127.0.0.1:8000
echo Tekan Ctrl + C untuk menghentikan server.
echo =================================================================
php artisan serve --host=127.0.0.1 --port=8000
