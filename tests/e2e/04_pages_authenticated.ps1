. (Join-Path $PSScriptRoot "_helpers.ps1")

$root = Resolve-Path (Join-Path $PSScriptRoot "..\..")
$flag = Ensure-InstalledFlag -Root $root
$creds = Bootstrap-User -Root $root

$port = Get-FreeTcpPort
$baseUrl = "http://127.0.0.1:$port"
$server = $null

try {
    Write-Host "T04: Start server $baseUrl"
    $server = Start-TestServer -Root $root -Port $port
    $session = New-WebSession

    Write-Host "T04: Login"
    Login-Session -BaseUrl $baseUrl -Session $session -Username $creds.User -Password $creds.Pass | Out-Null

    $paths = @(
        "/dorf1.php",
        "/dorf2.php",
        "/karte.php",
        "/spieler.php?uid=1",
        "/statistiken.php",
        "/nachrichten.php",
        "/berichte.php",
        "/build.php?id=1"
    )

    foreach ($p in $paths) {
        Write-Host "T04: GET $p"
        $r = Invoke-Req -Uri "$baseUrl$p" -TimeoutSec 30 -WebSession $session
        Assert-True ($r.StatusCode -eq 200) "$p failed (HTTP $($r.StatusCode))"
        $body = Decode-Body $r
        Assert-NoPhpErrors -Body $body -Context $p
    }

    Write-Host "OK: T04"
    exit 0
} finally {
    if ($server) {
        Stop-TestServer -Server $server
        Assert-NoPhpErrorsInLogs -Server $server -Context "T04"
        Cleanup-ServerLogs -Server $server
    }
    Cleanup-InstalledFlag -FlagInfo $flag
}
