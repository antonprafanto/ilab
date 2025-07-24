@echo off
echo ========================================
echo   ILab UNMUL - Copy Files to XAMPP
echo ========================================
echo.

REM Check if XAMPP directory exists
if not exist "C:\xampp\htdocs\" (
    echo ERROR: XAMPP htdocs directory not found!
    echo Please install XAMPP first.
    pause
    exit /b 1
)

echo Copying files to XAMPP htdocs...

REM Create ilab directory if not exists
if not exist "C:\xampp\htdocs\ilab\" (
    mkdir "C:\xampp\htdocs\ilab"
)

REM Copy all files
echo Copying project files...
xcopy "%~dp0*" "C:\xampp\htdocs\ilab\" /E /H /C /I /Y

echo.
echo ========================================
echo   Files copied successfully!
echo ========================================
echo.
echo Next steps:
echo 1. Open browser and go to: http://localhost/ilab/index_local.php
echo 2. Click "Auto Setup Database"
echo 3. Start testing!
echo.
echo Press any key to open the testing dashboard...
pause >nul

REM Open browser to testing dashboard
start http://localhost/ilab/index_local.php

echo.
echo Testing dashboard opened in browser.
echo If browser didn't open, manually go to: http://localhost/ilab/index_local.php
pause