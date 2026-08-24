# ============================================================
# ApniFactory - Local Development Setup Script
# ============================================================
# This script sets up the project for LOCAL development only.
# It does NOT touch production URLs, DB, or code.
# ============================================================

$ErrorActionPreference = "Continue"
$phpPath   = ""
$mysqlPath = ""

Write-Host ""
Write-Host "======================================================" -ForegroundColor Cyan
Write-Host "   ApniFactory - Local Dev Setup" -ForegroundColor Cyan
Write-Host "======================================================" -ForegroundColor Cyan
Write-Host ""

# ---- 1. Find PHP ----
Write-Host "[1/8] Locating PHP..." -ForegroundColor Yellow
$phpCandidates = @(
    "C:\Program Files\PHP\php.exe",
    "C:\PHP\php.exe",
    "C:\xampp\php\php.exe",
    "C:\laragon\bin\php\php.exe",
    "$env:LOCALAPPDATA\Microsoft\WinGet\Links\php.exe",
    "$env:LOCALAPPDATA\Programs\PHP\php.exe",
    "$env:LOCALAPPDATA\Microsoft\WinGet\Packages\PHP.PHP.8.2_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"
)

foreach ($p in $phpCandidates) {
    if (Test-Path $p) { $phpPath = $p; break }
}

if (-not $phpPath) {
    # Try to find in PATH after winget install
    $found = Get-Command php -ErrorAction SilentlyContinue
    if ($found) { $phpPath = $found.Source }
}

if (-not $phpPath) {
    # Last resort: scan common winget install dirs
    $wingetLinks = "$env:LOCALAPPDATA\Microsoft\WinGet\Links"
    if (Test-Path $wingetLinks) {
        $phpExe = Get-ChildItem $wingetLinks -Filter "php.exe" -ErrorAction SilentlyContinue | Select-Object -First 1
        if ($phpExe) { $phpPath = $phpExe.FullName }
    }
}

if (-not $phpPath) {
    Write-Host "ERROR: PHP not found. Please restart PowerShell and run this script again." -ForegroundColor Red
    Write-Host "       (winget adds PHP to PATH but needs a shell restart)" -ForegroundColor Yellow
    exit 1
}

Write-Host "   Found PHP: $phpPath" -ForegroundColor Green
$phpVersion = & $phpPath --version 2>&1 | Select-Object -First 1
Write-Host "   Version  : $phpVersion" -ForegroundColor Gray

# ---- 2. Find MySQL ----
Write-Host "[2/8] Locating MySQL..." -ForegroundColor Yellow
$mysqlCandidates = @(
    "C:\Program Files\MariaDB 12.3\bin\mysql.exe",
    "C:\Program Files\MySQL\MySQL Server 8.4\bin\mysql.exe",
    "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe",
    "C:\xampp\mysql\bin\mysql.exe",
    "C:\laragon\bin\mysql\mysql-8.0\bin\mysql.exe"
)

foreach ($m in $mysqlCandidates) {
    if (Test-Path $m) { $mysqlPath = $m; break }
}

if (-not $mysqlPath) {
    $found = Get-Command mysql -ErrorAction SilentlyContinue
    if ($found) { $mysqlPath = $found.Source }
}

if (-not $mysqlPath) {
    Write-Host "ERROR: MySQL not found. Please ensure MySQL is installed and running." -ForegroundColor Red
    Write-Host "       If just installed, restart PowerShell and try again." -ForegroundColor Yellow
    exit 1
}

Write-Host "   Found MySQL: $mysqlPath" -ForegroundColor Green

# ---- 3. Setup .env ----
Write-Host "[3/8] Setting up local .env file..." -ForegroundColor Yellow
if (Test-Path ".env.local") {
    Copy-Item ".env.local" ".env" -Force
    Write-Host "   Copied .env.local -> .env (local settings, NO production DB)" -ForegroundColor Green
} else {
    Write-Host "   ERROR: .env.local not found!" -ForegroundColor Red
    exit 1
}

# ---- 4. Enable PHP extensions ----
Write-Host "[4/8] Enabling required PHP extensions..." -ForegroundColor Yellow
$phpIniPath = (& $phpPath -r "echo php_ini_loaded_file();" 2>&1)
if (-not $phpIniPath -or $phpIniPath -notlike "*.ini") {
    # Find php.ini in PHP dir
    $phpDir = Split-Path $phpPath
    $phpIniPath = Join-Path $phpDir "php.ini"
    if (-not (Test-Path $phpIniPath)) {
        $phpIniSamplePath = Join-Path $phpDir "php.ini-development"
        if (Test-Path $phpIniSamplePath) {
            Copy-Item $phpIniSamplePath $phpIniPath
            Write-Host "   Created php.ini from php.ini-development" -ForegroundColor Green
        }
    }
}

if (Test-Path $phpIniPath) {
    $phpIni = Get-Content $phpIniPath -Raw
    $extensions = @("pdo_mysql", "mysqli", "mbstring", "openssl", "tokenizer", "xml", "ctype", "json", "fileinfo", "zip")
    foreach ($ext in $extensions) {
        if ($phpIni -match ";extension=$ext") {
            $phpIni = $phpIni -replace ";extension=$ext", "extension=$ext"
            Write-Host "   Enabled: $ext" -ForegroundColor Green
        }
    }
    # Also enable extension_dir if commented
    $phpDir = Split-Path $phpPath
    $extDir = Join-Path $phpDir "ext"
    if ((Test-Path $extDir) -and ($phpIni -match ";extension_dir = ""ext""")) {
        $phpIni = $phpIni -replace ';extension_dir = "ext"', 'extension_dir = "ext"'
        Write-Host "   Enabled: extension_dir = ext" -ForegroundColor Green
    }
    Set-Content $phpIniPath $phpIni
} else {
    Write-Host "   WARNING: php.ini not found. Extensions may need manual enabling." -ForegroundColor Yellow
}

# ---- 5. Composer Install ----
Write-Host "[5/8] Installing Composer dependencies..." -ForegroundColor Yellow
if (-not (Test-Path "vendor")) {
    $composer = Get-Command composer -ErrorAction SilentlyContinue
    if ($composer) {
        & composer install --no-interaction
    } else {
        Write-Host "   Downloading Composer..." -ForegroundColor Yellow
        $composerSetup = "$env:TEMP\composer-setup.php"
        Invoke-WebRequest "https://getcomposer.org/installer" -OutFile $composerSetup
        & $phpPath $composerSetup --install-dir="$env:LOCALAPPDATA\Programs\composer" --filename=composer
        $composerPhar = "$env:LOCALAPPDATA\Programs\composer\composer"
        if (Test-Path "$composerPhar.phar") {
            & $phpPath "$composerPhar.phar" install --no-interaction
        }
    }
} else {
    Write-Host "   vendor/ already exists, skipping composer install." -ForegroundColor Gray
}

# ---- 6. Create Local Database ----
Write-Host "[6/8] Creating local database 'apnifactory_local'..." -ForegroundColor Yellow
try {
    $mysqlBin = Split-Path $mysqlPath
    $mysqladmin = Join-Path $mysqlBin "mysqladmin.exe"
    
    # Try creating DB with no password (default local root)
    & $mysqlPath -u root -e "CREATE DATABASE IF NOT EXISTS apnifactory_local CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>&1
    Write-Host "   Database 'apnifactory_local' created/verified." -ForegroundColor Green
} catch {
    Write-Host "   WARNING: Could not create database automatically." -ForegroundColor Yellow
    Write-Host "   Please create manually: CREATE DATABASE apnifactory_local;" -ForegroundColor Yellow
}

# ---- 7. Run Migrations & Seeders ----
Write-Host "[7/8] Running migrations and seeding demo data..." -ForegroundColor Yellow
& $phpPath artisan migrate --force 2>&1
Write-Host ""
& $phpPath artisan db:seed --force 2>&1

# ---- 8. Storage Link ----
Write-Host "[8/8] Creating storage symlink..." -ForegroundColor Yellow
& $phpPath artisan storage:link 2>&1

# ---- Done ----
Write-Host ""
Write-Host "======================================================" -ForegroundColor Green
Write-Host "   LOCAL SETUP COMPLETE!" -ForegroundColor Green
Write-Host "======================================================" -ForegroundColor Green
Write-Host ""
Write-Host "  Local Admin Login:" -ForegroundColor Cyan
Write-Host "    URL     : http://localhost:8000/admin" -ForegroundColor White
Write-Host "    Email   : admin@apnifactory.local" -ForegroundColor White
Write-Host "    Password: admin@123" -ForegroundColor White
Write-Host ""
Write-Host "  Test Customer Login:" -ForegroundColor Cyan
Write-Host "    Email   : customer@apnifactory.local" -ForegroundColor White
Write-Host "    Password: customer@123" -ForegroundColor White
Write-Host ""
Write-Host "  Starting local server at http://localhost:8000 ..." -ForegroundColor Yellow
Write-Host "  Press Ctrl+C to stop the server." -ForegroundColor Gray
Write-Host ""

& $phpPath artisan serve --host=127.0.0.1 --port=8000
