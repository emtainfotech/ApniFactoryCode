$loginUrl = "http://127.0.0.1:8000/login"
$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession

Write-Host "1. Fetching login page CSRF token..."
$loginPage = Invoke-WebRequest -Uri $loginUrl -WebSession $session -UseBasicParsing
if ($loginPage.Content -match 'name="_token"\s+value="([^"]+)"') {
    $token = $matches[1]
    Write-Host "   Found CSRF token: $token"
} else {
    Write-Host "   Could not find CSRF token"
}

Write-Host "2. Submitting seller login..."
$loginBody = @{
    _token = $token
    email = "seller@apnifactory.local"
    password = "password123"
}

$loginResponse = Invoke-WebRequest -Uri $loginUrl -Method POST -Body $loginBody -WebSession $session -MaximumRedirection 0 -ErrorAction SilentlyContinue

Write-Host "   Login HTTP Status: $($loginResponse.StatusCode) (Redirect expected 302)"

$sellerPages = @(
    "/seller/dashboard",
    "/seller/profile",
    "/seller/brand",
    "/seller/category",
    "/seller/product",
    "/seller/addproduct",
    "/seller/paint-pricing",
    "/seller/order",
    "/seller/creditnotes",
    "/seller/wallet",
    "/seller/bankdetail",
    "/seller/ticketsupport",
    "/seller/notifications",
    "/seller/citynotallow",
    "/seller/coupon",
    "/seller/advertisement",
    "/seller/faq",
    "/seller/help",
    "/customer/app-preview"
)

Write-Host "`n3. Testing Seller Pages..."
foreach ($p in $sellerPages) {
    $sw = [System.Diagnostics.Stopwatch]::StartNew()
    try {
        $res = Invoke-WebRequest -Uri "http://127.0.0.1:8000$p" -WebSession $session -UseBasicParsing -TimeoutSec 15
        $sw.Stop()
        Write-Host ("   {0,-28} | Status: {1} | Time: {2,6:N0} ms | Size: {3} bytes" -f $p, $res.StatusCode, $sw.ElapsedMilliseconds, $res.RawContentLength) -ForegroundColor Green
    } catch {
        $sw.Stop()
        Write-Host ("   {0,-28} | ERROR: {1} | Time: {2,6:N0} ms" -f $p, $_.Exception.Message, $sw.ElapsedMilliseconds) -ForegroundColor Red
    }
}

$adminPages = @(
    "/admin/login",
    "/admin",
    "/admin/orders",
    "/admin/products",
    "/admin/companies",
    "/admin/customers",
    "/admin/brands",
    "/admin/wallets",
    "/admin/transections",
    "/admin/categories",
    "/admin/box-packings",
    "/admin/shade-cards",
    "/admin/coupons",
    "/admin/advertisements",
    "/admin/sliders",
    "/admin/faqs",
    "/admin/feedback",
    "/admin/tickets",
    "/admin/pages"
)

Write-Host "`n4. Testing Admin Pages (Anonymous/Redirect check)..."
foreach ($p in $adminPages) {
    $sw = [System.Diagnostics.Stopwatch]::StartNew()
    try {
        $res = Invoke-WebRequest -Uri "http://127.0.0.1:8000$p" -UseBasicParsing -TimeoutSec 15 -MaximumRedirection 0 -ErrorAction SilentlyContinue
        $sw.Stop()
        Write-Host ("   {0,-28} | Status: {1} | Time: {2,6:N0} ms" -f $p, $res.StatusCode, $sw.ElapsedMilliseconds) -ForegroundColor Cyan
    } catch {
        $sw.Stop()
        Write-Host ("   {0,-28} | Status: {1} | Time: {2,6:N0} ms" -f $p, $_.Exception.Response.StatusCode.value__, $sw.ElapsedMilliseconds) -ForegroundColor Yellow
    }
}

