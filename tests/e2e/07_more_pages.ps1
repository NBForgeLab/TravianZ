. (Join-Path $PSScriptRoot "_helpers.ps1")

$root = Resolve-Path (Join-Path $PSScriptRoot "..\..")
$flag = Ensure-InstalledFlag -Root $root
$creds = Bootstrap-User -Root $root

$port = Get-FreeTcpPort
$baseUrl = "http://127.0.0.1:$port"
$server = $null

try {
    Write-Host "T07: Start server $baseUrl"
    $server = Start-TestServer -Root $root -Port $port
    $session = New-WebSession

    Write-Host "T07: Login"
    Login-Session -BaseUrl $baseUrl -Session $session -Username $creds.User -Password $creds.Pass | Out-Null

    $uid = Get-UserId -Root $root -Username $creds.User
    Assert-True ($uid -gt 0) "Could not resolve user id"

    $paths = @(
        "/allianz.php",
        "/plus.php",
        "/spieler.php?uid=$uid",
        "/spieler.php?uid=$uid&s=1",
        "/spieler.php?uid=$uid&s=2",
        "/build.php?id=1",
        "/build.php?id=17",
        "/build.php?id=19",
        "/build.php?id=30"
    )

    foreach ($p in $paths) {
        Write-Host "T07: GET $p"
        $r = Invoke-Req -Uri "$baseUrl$p" -TimeoutSec 40 -WebSession $session
        Assert-True ($r.StatusCode -eq 200) "$p failed (HTTP $($r.StatusCode))"
        $body = Decode-Body $r
        Assert-NoPhpErrors -Body $body -Context $p
    }

    Write-Host "OK: T07"
    exit 0
} finally {
    if ($server) {
        Stop-TestServer -Server $server
        Assert-NoPhpErrorsInLogs -Server $server -Context "T07"
        Cleanup-ServerLogs -Server $server
    }
    Cleanup-InstalledFlag -FlagInfo $flag
}
