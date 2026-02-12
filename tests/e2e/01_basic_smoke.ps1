. (Join-Path $PSScriptRoot "_helpers.ps1")

$root = Resolve-Path (Join-Path $PSScriptRoot "..\..")

Write-Host "T01: PHP modules"
$modules = (& php -m) | ForEach-Object { $_.Trim() } | Where-Object { $_ -ne "" }
Assert-True ($modules -contains "mysqli") "mysqli extension not loaded"
Assert-True ($modules -contains "mbstring") "mbstring extension not loaded"

Write-Host "T01: DB smoke"
& php (Join-Path $root "tests\e2e\db_smoke.php")
Assert-True ($LASTEXITCODE -eq 0) "DB smoke failed"

$flag = Ensure-InstalledFlag -Root $root
$port = Get-FreeTcpPort
$baseUrl = "http://127.0.0.1:$port"

$server = $null
try {
    Write-Host "T01: Start server $baseUrl"
    $server = Start-TestServer -Root $root -Port $port

    $homeResp = Invoke-Req -Uri "$baseUrl/" -TimeoutSec 15
    Assert-True ($homeResp.StatusCode -eq 200) "Home failed (HTTP $($homeResp.StatusCode))"
    $homeBody = Decode-Body $homeResp
    Assert-NoPhpErrors -Body $homeBody -Context "home"

    $map = Invoke-Req -Uri "$baseUrl/ajax.php?f=k7&x=0&y=0&xx=0&yy=0" -TimeoutSec 15
    Assert-True ($map.StatusCode -eq 200) "Map ajax failed (HTTP $($map.StatusCode))"
    $mapBody = Decode-Body $map
    $contentType = [string] $map.Headers['Content-Type']
    Assert-True ($contentType -match 'application/json') "Map ajax unexpected content-type"
    $null = $mapBody | ConvertFrom-Json

    Write-Host "OK: T01"
    exit 0
} finally {
    if ($server) {
        Stop-TestServer -Server $server
        Assert-NoPhpErrorsInLogs -Server $server -Context "T01"
        Cleanup-ServerLogs -Server $server
    }
    Cleanup-InstalledFlag -FlagInfo $flag
}
