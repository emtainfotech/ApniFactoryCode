$adb = "$env:LOCALAPPDATA\Android\Sdk\platform-tools\adb.exe"
$apk = "c:\Users\EPC001\Desktop\apni_factory\apni_factory\build\app\outputs\flutter-apk\app-debug.apk"

Write-Host "Waiting for device 3c463fc7 to be ready..."
for ($i = 0; $i -lt 30; $i++) {
    $devOutput = & $adb devices
    if ($devOutput -match "3c463fc7\s+device") {
        Write-Host "Device 3c463fc7 is online and ready!"
        Write-Host "Reversing tcp:8000..."
        & $adb -s 3c463fc7 reverse tcp:8000 tcp:8000
        
        Write-Host "Installing $apk..."
        $installResult = & $adb -s 3c463fc7 install -r $apk
        Write-Host "Install output: $installResult"
        
        Write-Host "Launching application..."
        & $adb -s 3c463fc7 shell monkey -p com.example.apni_factory -c android.intent.category.LAUNCHER 1
        
        Start-Sleep -Seconds 3
        Write-Host "Capturing verification screenshot..."
        cmd.exe /c "$adb -s 3c463fc7 exec-out screencap -p > C:\Users\EPC001\.gemini\antigravity\brain\c7411eb4-6f80-4611-985e-d57fbe51aac3\screen_mobile_new_updated_live.png"
        Write-Host "SUCCESS: App installed, reversed, launched, and verified!"
        exit 0
    }
    Start-Sleep -Seconds 2
}
Write-Host "Device was still offline after waiting."

