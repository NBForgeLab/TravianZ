. (Join-Path $PSScriptRoot "_helpers.ps1")

$root = Resolve-Path (Join-Path $PSScriptRoot "..\..")
$flag = Ensure-InstalledFlag -Root $root
$creds = Bootstrap-User -Root $root

$port = Get-FreeTcpPort
$baseUrl = "http://127.0.0.1:$port"
$server = $null

try {
    Write-Host "T02: Start server $baseUrl"
    $server = Start-TestServer -Root $root -Port $port
    $session = New-WebSession

    Write-Host "T02: Login"
    Login-Session -BaseUrl $baseUrl -Session $session -Username $creds.User -Password $creds.Pass | Out-Null

    $dorf1 = Invoke-Req -Uri "$baseUrl/dorf1.php" -TimeoutSec 20 -WebSession $session
    Assert-True ($dorf1.StatusCode -eq 200) "dorf1.php failed (HTTP $($dorf1.StatusCode))"
    $dorfBody = Decode-Body $dorf1
    Assert-NoPhpErrors -Body $dorfBody -Context "dorf1.php"
    Assert-True ($dorfBody -match 'logout\.php') "dorf1.php does not look authenticated"

    Write-Host "T02: Logout"
    $logout = Invoke-Req -Uri "$baseUrl/logout.php" -TimeoutSec 20 -WebSession $session
    Assert-True ($logout.StatusCode -eq 200) "logout.php failed (HTTP $($logout.StatusCode))"
    $logoutBody = Decode-Body $logout
    Assert-NoPhpErrors -Body $logoutBody -Context "logout.php"
    Assert-True ($logoutBody -match 'Logout successful') "logout.php unexpected content"

    $after = Invoke-Req -Uri "$baseUrl/dorf1.php" -TimeoutSec 20 -WebSession $session
    Assert-True ($after.StatusCode -eq 200) "dorf1.php after logout failed (HTTP $($after.StatusCode))"
    $afterBody = Decode-Body $after
    Assert-NoPhpErrors -Body $afterBody -Context "dorf1.php after logout"
    Assert-True (-not ($afterBody -match 'logout\.php')) "dorf1.php still looks authenticated after logout"

    Write-Host "OK: T02"
    exit 0
} finally {
    if ($server) {
        Stop-TestServer -Server $server
        Assert-NoPhpErrorsInLogs -Server $server -Context "T02"
        Cleanup-ServerLogs -Server $server
    }
    Cleanup-InstalledFlag -FlagInfo $flag
}
