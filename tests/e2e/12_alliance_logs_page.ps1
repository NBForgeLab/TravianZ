. (Join-Path $PSScriptRoot "_helpers.ps1")

$root = Resolve-Path (Join-Path $PSScriptRoot "..\..")
$flag = Ensure-InstalledFlag -Root $root

$env:E2E_USERNAME = "e2e_c1"
$env:E2E_PASSWORD = "e2e_pass_12345"
$env:E2E_EMAIL = "e2e_c1@travianz.local"
$creds1 = Bootstrap-User -Root $root
$uid1 = Get-UserId -Root $root -Username $creds1.User

$env:E2E_USERNAME = "e2e_c2"
$env:E2E_PASSWORD = "e2e_pass_67890"
$env:E2E_EMAIL = "e2e_c2@travianz.local"
$creds2 = Bootstrap-User -Root $root
$uid2 = Get-UserId -Root $root -Username $creds2.User

$vref1 = & php (Join-Path $root "tests\e2e\db_tools.php") get_main_village_id $uid1
if ($LASTEXITCODE -ne 0) { throw "get_main_village_id failed" }
$vref1 = [int] ($vref1 | Select-Object -First 1)
& php (Join-Path $root "tests\e2e\db_tools.php") set_embassy_level $vref1 3 | Out-Null
if ($LASTEXITCODE -ne 0) { throw "set_embassy_level failed" }

$port = Get-FreeTcpPort
$baseUrl = "http://127.0.0.1:$port"
$server = $null

try {
    Write-Host "T12: Start server $baseUrl"
    $server = Start-TestServer -Root $root -Port $port
    $s1 = New-WebSession
    Login-Session -BaseUrl $baseUrl -Session $s1 -Username $creds1.User -Password $creds1.Pass | Out-Null

    $tag = "L" + ([guid]::NewGuid().ToString("N").Substring(0,6))
    $name = "Alliance " + ([guid]::NewGuid().ToString("N").Substring(0,6))
    $createBody = @{ ft="ali1"; ally1=$tag; ally2=$name; s="1" }
    Invoke-Req -Uri "$baseUrl/build.php?gid=18" -Method Post -Body $createBody -TimeoutSec 20 -WebSession $s1 | Out-Null
    $aid = 0
    for ($i=0; $i -lt 20 -and $aid -le 0; $i++) {
        Start-Sleep -Milliseconds 200
        $a = & php (Join-Path $root "tests\e2e\db_tools.php") get_alliance_id_by_tag $tag
        if ($LASTEXITCODE -eq 0) { $aid = [int] ($a | Select-Object -First 1) }
    }
    Assert-True ($aid -gt 0) "aid invalid"

    $inviteBody = @{ s="1"; o="4"; a="4"; a_name=$creds2.User }
    Invoke-Req -Uri "$baseUrl/allianz.php" -Method Post -Body $inviteBody -TimeoutSec 20 -WebSession $s1 | Out-Null
    $invId = & php (Join-Path $root "tests\e2e\db_tools.php") get_invitation_id_for_user $uid2
    $s2 = New-WebSession
    Login-Session -BaseUrl $baseUrl -Session $s2 -Username $creds2.User -Password $creds2.Pass | Out-Null
    Invoke-Req -Uri "$baseUrl/allianz.php?a=3&d=$invId" -TimeoutSec 20 -WebSession $s2 | Out-Null

    $page = Invoke-Req -Uri "$baseUrl/allianz.php" -TimeoutSec 20 -WebSession $s1
    Assert-True ($page.StatusCode -eq 200) "allianz.php failed"
    $body = Decode-Body $page
    Assert-NoPhpErrors -Body $body -Context "allianz.php logs"
    Assert-True ($body -match 'has joined the alliance|has invited|has changed the alliance name|has been expelled|has deleted the invitation|has rejected the invitation|The alliance has been founded') "alliance log entries not visible"

    Write-Host "OK: T12"
    exit 0
} finally {
    if ($server) {
        Stop-TestServer -Server $server
        Cleanup-ServerLogs -Server $server
    }
    Cleanup-InstalledFlag -FlagInfo $flag
}
