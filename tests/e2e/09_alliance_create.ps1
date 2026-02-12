. (Join-Path $PSScriptRoot "_helpers.ps1")

$root = Resolve-Path (Join-Path $PSScriptRoot "..\..")
$flag = Ensure-InstalledFlag -Root $root
$creds = Bootstrap-User -Root $root

$uid = Get-UserId -Root $root -Username $creds.User
$vref = & php (Join-Path $root "tests\e2e\db_tools.php") get_main_village_id $uid
if ($LASTEXITCODE -ne 0) { throw "get_main_village_id failed" }
$vref = [int] ($vref | Select-Object -First 1)

& php (Join-Path $root "tests\e2e\db_tools.php") set_embassy_level $vref 3 | Out-Null
if ($LASTEXITCODE -ne 0) { throw "set_embassy_level failed" }

$port = Get-FreeTcpPort
$baseUrl = "http://127.0.0.1:$port"
$server = $null

try {
    Write-Host "T09: Start server $baseUrl"
    $server = Start-TestServer -Root $root -Port $port
    $session = New-WebSession

    Write-Host "T09: Login"
    Login-Session -BaseUrl $baseUrl -Session $session -Username $creds.User -Password $creds.Pass | Out-Null

    Write-Host "T09: Open Embassy"
    $embPage = Invoke-Req -Uri "$baseUrl/build.php?gid=18" -TimeoutSec 20 -WebSession $session
    Assert-True ($embPage.StatusCode -eq 200) "build.php?gid=18 failed (HTTP $($embPage.StatusCode))"
    $embBody = Decode-Body $embPage
    Assert-NoPhpErrors -Body $embBody -Context "build.php?gid=18"

    $tag = "T" + ([guid]::NewGuid().ToString("N").Substring(0,6))
    $name = "Alliance " + ([guid]::NewGuid().ToString("N").Substring(0,6))
    $body = @{
        ft    = "ali1"
        ally1 = $tag
        ally2 = $name
        s     = "1"
    }

    Write-Host "T09: Create alliance ($tag)"
    $createResp = Invoke-Req -Uri "$baseUrl/build.php?gid=18" -Method Post -Body $body -TimeoutSec 20 -WebSession $session
    Assert-True ($createResp.StatusCode -eq 200) "Alliance creation POST failed (HTTP $($createResp.StatusCode))"
    $createBody = Decode-Body $createResp
    Assert-NoPhpErrors -Body $createBody -Context "build.php create alliance"

    $aid = & php (Join-Path $root "tests\e2e\db_tools.php") get_alliance_id_by_tag $tag
    if ($LASTEXITCODE -ne 0) { throw "get_alliance_id_by_tag failed" }
    $aid = [int] ($aid | Select-Object -First 1)
    Assert-True ($aid -gt 0) "Alliance was not created in DB"

    $logCount = & php (Join-Path $root "tests\e2e\db_tools.php") count_ali_log_for_aid $aid
    if ($LASTEXITCODE -ne 0) { throw "count_ali_log_for_aid failed" }
    $logCount = [int] ($logCount | Select-Object -First 1)
    Assert-True ($logCount -ge 1) "Alliance log not created"

    Write-Host "OK: T09"
    exit 0
} finally {
    if ($server) {
        Stop-TestServer -Server $server
        Assert-NoPhpErrorsInLogs -Server $server -Context "T09"
        Cleanup-ServerLogs -Server $server
    }
    Cleanup-InstalledFlag -FlagInfo $flag
}
