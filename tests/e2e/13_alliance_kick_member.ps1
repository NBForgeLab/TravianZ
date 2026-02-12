. (Join-Path $PSScriptRoot "_helpers.ps1")

$root = Resolve-Path (Join-Path $PSScriptRoot "..\..")
$flag = Ensure-InstalledFlag -Root $root

$env:E2E_USERNAME = "e2e_d1"
$env:E2E_PASSWORD = "e2e_pass_12345"
$env:E2E_EMAIL = "e2e_d1@travianz.local"
$creds1 = Bootstrap-User -Root $root
$uid1 = Get-UserId -Root $root -Username $creds1.User

$env:E2E_USERNAME = "e2e_d2"
$env:E2E_PASSWORD = "e2e_pass_67890"
$env:E2E_EMAIL = "e2e_d2@travianz.local"
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
    Write-Host "T13: Start server $baseUrl"
    $server = Start-TestServer -Root $root -Port $port
    $s1 = New-WebSession
    Login-Session -BaseUrl $baseUrl -Session $s1 -Username $creds1.User -Password $creds1.Pass | Out-Null

    $tag = "K" + ([guid]::NewGuid().ToString("N").Substring(0,6))
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

    $s2 = New-WebSession
    Login-Session -BaseUrl $baseUrl -Session $s2 -Username $creds2.User -Password $creds2.Pass | Out-Null
    $inviteBody = @{ s="1"; o="4"; a="4"; a_name=$creds2.User }
    Invoke-Req -Uri "$baseUrl/allianz.php" -Method Post -Body $inviteBody -TimeoutSec 20 -WebSession $s1 | Out-Null
    $invId = & php (Join-Path $root "tests\e2e\db_tools.php") get_invitation_id_for_user $uid2
    Invoke-Req -Uri "$baseUrl/allianz.php?a=3&d=$invId" -TimeoutSec 20 -WebSession $s2 | Out-Null

    $perm = & php (Join-Path $root "tests\e2e\db_tools.php") get_alli_permissions_json $uid2 $aid
    $permObj = $perm | ConvertFrom-Json
    Assert-True ($permObj.id -gt 0) "member permissions missing"

    $kickBody = @{ s="1"; o="2"; a_user=$uid2 }
    $kickResp = Invoke-Req -Uri "$baseUrl/allianz.php" -Method Post -Body $kickBody -TimeoutSec 20 -WebSession $s1
    Assert-True ($kickResp.StatusCode -eq 200) "kick POST failed"

    $user2Aid = & php (Join-Path $root "tests\e2e\db_tools.php") get_user_alliance $uid2
    $user2Aid = [int] ($user2Aid | Select-Object -First 1)
    Assert-True ($user2Aid -eq 0) "user2 still in alliance"

    $permAfter = & php (Join-Path $root "tests\e2e\db_tools.php") get_alli_permissions_json $uid2 $aid
    $permAfterObj = $permAfter | ConvertFrom-Json
    Assert-True ($permAfterObj.id -eq $null) "permissions row still exists"

    Write-Host "OK: T13"
    exit 0
} finally {
    if ($server) {
        Stop-TestServer -Server $server
        Cleanup-ServerLogs -Server $server
    }
    Cleanup-InstalledFlag -FlagInfo $flag
}
