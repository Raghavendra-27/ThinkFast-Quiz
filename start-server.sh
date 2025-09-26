#!/bin/bash

echo "Starting Think Fast Quiz Server..."
echo ""
echo "Make sure MySQL is running before starting the server."
echo ""
echo "Starting PHP built-in server on http://localhost:8000"
echo "Press Ctrl+C to stop the server"
echo ""

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "Error: PHP is not installed or not in PATH"
    echo "Please install PHP and try again"
    exit 1
fi

# Check if MySQL is running
if ! pgrep -x "mysqld" > /dev/null; then
    echo "Warning: MySQL doesn't appear to be running"
    echo "Please start MySQL service before running the quiz app"
    echo ""
fi

# Start PHP server
php -S localhost:8000





