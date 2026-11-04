@echo off
title Liquibase Update - Maraña Crochet Atelier
echo ========================================================
echo   Ejecutando Liquibase Update (Control de Cambios)
echo ========================================================
cd /D "%~dp0"

where liquibase >nul 2>nul
if %ERRORLEVEL% equ 0 (
    call liquibase update
) else if exist "C:\xampp\liquibase\liquibase.bat" (
    call "C:\xampp\liquibase\liquibase.bat" update
) else (
    echo Error: No se encontro Liquibase en el PATH ni en C:\xampp\liquibase\liquibase.bat
)
echo.
pause
