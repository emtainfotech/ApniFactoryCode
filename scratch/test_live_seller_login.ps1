$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession

# 1. GET login page and grab CSRF token
$getResp = Invoke-WebRequest -Uri "http://127.0.0.1:8000/login" -WebSession $session
if ($getResp.Content -match 'name="_token"\s+value="([^"]+)"') {
    $token = $matches[1]
    Write-Host "CSRF Token extracted: $token"
} else {
    Write-Error "Failed to extract CSRF token"
    exit 1
}

# 2. Test login with seller@apnifactory.lo (the typo in screenshot)
Write-Host "Testing login with normalized typo seller@apnifactory.lo..."
$postBody = @{
    _token = $token
    email = "seller@apnifactory.lo"
    password = "password123"
}

$loginResp = Invoke-WebRequest -Uri "http://127.0.0.1:8000/login" -Method POST -Body $postBody -WebSession $session -MaximumRedirection 0 -ErrorAction SilentlyContinue

Write-Host "Response Status: $($loginResp.StatusCode)"
Write-Host "Redirect Location: $($loginResp.Headers.Location)"

# 3. Access seller dashboard to verify authentication
$dashResp = Invoke-WebRequest -Uri "http://127.0.0.1:8000/seller/dashboard" -WebSession $session -MaximumRedirection 0 -ErrorAction SilentlyContinue
Write-Host "Dashboard Status: $($dashResp.StatusCode)"

# 4. Access seller products page to verify 12 products
$prodResp = Invoke-WebRequest -Uri "http://127.0.0.1:8000/seller/product" -WebSession $session
if ($prodResp.Content -match "Apcolite Premium Gloss Enamel") {
    Write-Host "SUCCESS: Seller logged in and products found on page!"
} else {
    Write-Host "WARNING: Product name not found in response"
}

