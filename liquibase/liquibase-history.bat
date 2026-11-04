@echo off
title Liquibase History - Maraña Crochet Atelier
echo ========================================================
echo   Consultando Historial de Despliegues Liquibase
echo ========================================================
cd /D "%~dp0"

where liquibase >nul 2>nul
if %ERRORLEVEL% equ 0 (
    call liquibase history
) else if exist "C:\xampp\liquibase\liquibase.bat" (
    call "C:\xampp\liquibase\liquibase.bat" history
) else (
    echo Error: No se encontro Liquibase en el PATH ni en C:\xampp\liquibase\liquibase.bat
)
echo.
pause
