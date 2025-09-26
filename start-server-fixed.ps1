Write-Host "Starting PHP server with correct configuration..." -ForegroundColor Green
Write-Host ""
Write-Host "Make sure MySQL is running before starting the server." -ForegroundColor Yellow
Write-Host ""

# Start PHP server with explicit configuration file
& "C:\php-8.4.12\php.exe" -c "C:\php-8.4.12\php.ini" -S localhost:8000

Write-Host "Press any key to continue..." -ForegroundColor Cyan
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")

