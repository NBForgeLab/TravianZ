. (Join-Path $PSScriptRoot "_helpers.ps1")

$root = Resolve-Path (Join-Path $PSScriptRoot "..\..")
$flag = Ensure-InstalledFlag -Root $root
$creds = Bootstrap-User -Root $root

$port = Get-FreeTcpPort
$baseUrl = "http://127.0.0.1:$port"
$server = $null

try {
    Write-Host "T03: Start server $baseUrl"
    $server = Start-TestServer -Root $root -Port $port
    $session = New-WebSession

    Write-Host "T03: Login"
    Login-Session -BaseUrl $baseUrl -Session $session -Username $creds.User -Password $creds.Pass | Out-Null

    Write-Host "T03: World map page"
    $karte = Invoke-Req -Uri "$baseUrl/karte.php" -TimeoutSec 20 -WebSession $session
    Assert-True ($karte.StatusCode -eq 200) "karte.php failed (HTTP $($karte.StatusCode))"
    $karteBody = Decode-Body $karte
    Assert-NoPhpErrors -Body $karteBody -Context "karte.php"
    Assert-True ($karteBody -match '<title>.*World Map.*</title>') "karte.php unexpected content"

    Write-Host "T03: Map ajax ranges"
    $coords = @(
        @{ x = 0; y = 0; xx = 0; yy = 0 },
        @{ x = 6; y = 6; xx = -6; yy = -6 },
        @{ x = 12; y = 0; xx = 0; yy = 0 },
        @{ x = -12; y = 0; xx = 0; yy = 0 }
    )
    foreach ($c in $coords) {
        $url = "$baseUrl/ajax.php?f=k7&x=$($c.x)&y=$($c.y)&xx=$($c.xx)&yy=$($c.yy)"
        $r = Invoke-Req -Uri $url -TimeoutSec 20 -WebSession $session
        Assert-True ($r.StatusCode -eq 200) "Map ajax failed (HTTP $($r.StatusCode))"
        $body = Decode-Body $r
        $contentType = [string] $r.Headers['Content-Type']
        Assert-True ($contentType -match 'application/json') "Map ajax unexpected content-type"
        $null = $body | ConvertFrom-Json
    }

    Write-Host "T03: Quest ajax"
    $qst = Invoke-Req -Uri "$baseUrl/ajax.php?f=qst" -TimeoutSec 20 -WebSession $session
    Assert-True ($qst.StatusCode -eq 200) "qst ajax failed (HTTP $($qst.StatusCode))"
    $qstBody = Decode-Body $qst
    Assert-NoPhpErrors -Body $qstBody -Context "ajax.php?f=qst"

    Write-Host "OK: T03"
    exit 0
} finally {
    if ($server) {
        Stop-TestServer -Server $server
        Assert-NoPhpErrorsInLogs -Server $server -Context "T03"
        Cleanup-ServerLogs -Server $server
    }
    Cleanup-InstalledFlag -FlagInfo $flag
}
