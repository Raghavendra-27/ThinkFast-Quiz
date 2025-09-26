@echo off
echo ========================================
echo Think Fast Quiz - Installation Script
echo ========================================
echo.

echo Checking prerequisites...
echo.

REM Check if MySQL is installed
mysql --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ MySQL is not installed or not in PATH
    echo Please install MySQL from https://dev.mysql.com/downloads/mysql/
    echo.
    pause
    exit /b 1
) else (
    echo ✅ MySQL found
)

REM Check if PHP is installed
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ PHP is not installed or not in PATH
    echo Please install PHP from https://www.php.net/downloads.php
    echo.
    pause
    exit /b 1
) else (
    echo ✅ PHP found
)

echo.
echo ========================================
echo Setting up database...
echo ========================================
echo.

echo Please enter your MySQL root password:
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS quiz_app;"

if %errorlevel% neq 0 (
    echo ❌ Failed to create database
    echo Please check your MySQL credentials and try again
    pause
    exit /b 1
)

echo ✅ Database created successfully

echo.
echo Importing database schema...
mysql -u root -p quiz_app < database\init.sql

if %errorlevel% neq 0 (
    echo ❌ Failed to import database schema
    echo Please check the database/init.sql file
    pause
    exit /b 1
)

echo ✅ Database schema imported successfully

echo.
echo ========================================
echo Testing database connection...
echo ========================================
echo.

php test-db-connection.php

echo.
echo ========================================
echo Installation completed!
echo ========================================
echo.
echo To start the quiz application:
echo 1. Run: start-server.bat
echo 2. Open browser and go to: http://localhost:8000
echo.
echo For testing:
echo - Visit: http://localhost:8000/test-api.html
echo.
pause





