@echo off
title Liquibase Status - Maraña Crochet Atelier
echo ========================================================
echo   Consultando Estado de Migraciones Liquibase
echo ========================================================
cd /D "%~dp0"

where liquibase >nul 2>nul
if %ERRORLEVEL% equ 0 (
    call liquibase status
) else if exist "C:\xampp\liquibase\liquibase.bat" (
    call "C:\xampp\liquibase\liquibase.bat" status
) else (
    echo Error: No se encontro Liquibase en el PATH ni en C:\xampp\liquibase\liquibase.bat
)
echo.
pause
