param(
    [int] $Iterations = 80
)

. (Join-Path $PSScriptRoot "_helpers.ps1")

$root = Resolve-Path (Join-Path $PSScriptRoot "..\..")
$flag = Ensure-InstalledFlag -Root $root
$creds = Bootstrap-User -Root $root

$port = Get-FreeTcpPort
$baseUrl = "http://127.0.0.1:$port"
$server = $null

try {
    Write-Host "T05: Start server $baseUrl"
    $server = Start-TestServer -Root $root -Port $port
    $session = New-WebSession

    Write-Host "T05: Login"
    Login-Session -BaseUrl $baseUrl -Session $session -Username $creds.User -Password $creds.Pass | Out-Null

    $endpoints = @(
        "/dorf1.php",
        "/dorf2.php",
        "/karte.php",
        "/ajax.php?f=k7&x=0&y=0&xx=0&yy=0"
    )

    for ($i = 1; $i -le $Iterations; $i++) {
        $p = $endpoints[($i - 1) % $endpoints.Count]
        $r = Invoke-Req -Uri "$baseUrl$p" -TimeoutSec 30 -WebSession $session
        Assert-True ($r.StatusCode -eq 200) "Stress $p failed (HTTP $($r.StatusCode))"
        $body = Decode-Body $r
        Assert-NoPhpErrors -Body $body -Context "stress $p"
        if ($p -like "*ajax.php*") {
            $contentType = [string] $r.Headers['Content-Type']
            Assert-True ($contentType -match 'application/json') "stress ajax unexpected content-type"
            $null = $body | ConvertFrom-Json
        }
        if (($i % 20) -eq 0) {
            Write-Host "T05: $i/$Iterations"
        }
    }

    Write-Host "OK: T05"
    exit 0
} finally {
    if ($server) {
        Stop-TestServer -Server $server
        Assert-NoPhpErrorsInLogs -Server $server -Context "T05"
        Cleanup-ServerLogs -Server $server
    }
    Cleanup-InstalledFlag -FlagInfo $flag
}
