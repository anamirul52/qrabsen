@echo off
title SIPRES - SMP NEGERI 2 MIJEN (Online Mode)
echo =================================================================
echo        SIPRES - SISTEM INFORMASI PRESENSI SMP NEGERI 2 MIJEN
echo                   Mode Online (Cloudflare Tunnel)
echo =================================================================
echo.
echo Menghubungkan aplikasi lokal Anda ke internet dengan HTTPS...
echo.
.\cloudflared.exe tunnel --url http://127.0.0.1:8000
pause
