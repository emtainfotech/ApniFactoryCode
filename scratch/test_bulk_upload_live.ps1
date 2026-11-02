$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession

# 1. Login as Demo Seller
Write-Host "Logging in as Demo Seller..."
$loginPage = Invoke-WebRequest -Uri "http://127.0.0.1:8000/login" -WebSession $session
$token = [regex]::Match($loginPage.Content, 'name="_token"\s+value="([^"]+)"').Groups[1].Value

$body = @{
    _token = $token
    email = "seller@apnifactory.local"
    password = "password123"
}
$authResp = Invoke-WebRequest -Uri "http://127.0.0.1:8000/login" -Method POST -Body $body -WebSession $session -MaximumRedirection 0 -ErrorAction SilentlyContinue
Write-Host "Seller Auth Status: $($authResp.StatusCode) -> $($authResp.Headers.Location)"

# 2. Test Sample CSV Download
Write-Host "Testing Sample CSV Download..."
$sampleResp = Invoke-WebRequest -Uri "http://127.0.0.1:8000/seller/product/bulk-sample" -WebSession $session
Write-Host "Sample CSV Status: $($sampleResp.StatusCode)"
Write-Host "Sample CSV First Line: $($sampleResp.Content.Split("`n")[0])"

# 3. Test Bulk Upload View
Write-Host "Testing Bulk Upload View Page..."
$viewResp = Invoke-WebRequest -Uri "http://127.0.0.1:8000/seller/product/bulk-upload" -WebSession $session
Write-Host "Bulk Upload Page Status: $($viewResp.StatusCode)"

# 4. Create a test CSV to upload
$testCsvContent = @"
category_name,brand_name,product_name,title,description,hsn_code,tax_percent,box_packing,shade_or_color,seller_price,customer_mrp
Enamel Paints,Asian Paints Pro,ApniFactory Synthetic Enamel White,Synthetic High Gloss Enamel,Gloss solvent paint for surfaces,3208,18,1 Litre,Pure Brilliant White,380.00,475.00
Enamel Paints,Asian Paints Pro,ApniFactory Synthetic Enamel White,Synthetic High Gloss Enamel,Gloss solvent paint for surfaces,3208,18,4 Litres,Pure Brilliant White,1500.00,1875.00
Cotton Fabrics,Vardhman Textiles,Superfine Poplin Cotton 80s,100% Poplin Pure Cotton Fabric,High count poplin cotton for apparel,5208,5,50 Meters,Optic White,4500.00,5625.00
"@

$testCsvPath = "scratch/test_bulk_import.csv"
[System.IO.File]::WriteAllText($testCsvPath, $testCsvContent)

# 5. Extract CSRF token from bulk upload form
$uploadToken = [regex]::Match($viewResp.Content, 'name="_token"\s+value="([^"]+)"').Groups[1].Value
Write-Host "Upload Form CSRF Token: $uploadToken"

# 6. Post multipart form with CSV file
$boundary = [System.Guid]::NewGuid().ToString()
$LF = "`r`n"
$bodyBytes = New-Object System.IO.MemoryStream

$writer = New-Object System.IO.StreamWriter($bodyBytes)
$writer.Write("--$boundary$LF")
$writer.Write("Content-Disposition: form-data; name=`"_token`"$LF$LF$uploadToken$LF")
$writer.Write("--$boundary$LF")
$writer.Write("Content-Disposition: form-data; name=`"csv_file`"; filename=`"test_bulk_import.csv`"$LF")
$writer.Write("Content-Type: text/csv$LF$LF")
$writer.Write($testCsvContent)
$writer.Write("$LF--$boundary--$LF")
$writer.Flush()

$webRequest = [System.Net.HttpWebRequest]::Create("http://127.0.0.1:8000/seller/product/bulk-upload")
$webRequest.Method = "POST"
$webRequest.ContentType = "multipart/form-data; boundary=$boundary"
$webRequest.CookieContainer = $session.Cookies
$webRequest.AllowAutoRedirect = $false

$bodyArray = $bodyBytes.ToArray()
$webRequest.ContentLength = $bodyArray.Length
$reqStream = $webRequest.GetRequestStream()
$reqStream.Write($bodyArray, 0, $bodyArray.Length)
$reqStream.Close()

try {
    $uploadHttpResp = $webRequest.GetResponse()
    Write-Host "Upload Response Code: $($uploadHttpResp.StatusCode) -> Location: $($uploadHttpResp.Headers['Location'])"
} catch [System.Net.WebException] {
    $errResp = $_.Exception.Response
    Write-Host "Upload Response Code: $($errResp.StatusCode)"
    $reader = New-Object System.IO.StreamReader($errResp.GetResponseStream())
    Write-Host "Error details: $($reader.ReadToEnd().Substring(0, 500))"
}

# 7. Check if products appear in product list
$checkResp = Invoke-WebRequest -Uri "http://127.0.0.1:8000/seller/product?product=ApniFactory+Synthetic+Enamel+White" -WebSession $session
if ($checkResp.Content -match "ApniFactory Synthetic Enamel White") {
    Write-Host "SUCCESS: Bulk-imported product successfully verified in seller catalog!"
} else {
    Write-Host "Check result: Product not found in catalog query."
}

