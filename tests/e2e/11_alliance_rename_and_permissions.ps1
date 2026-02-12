. (Join-Path $PSScriptRoot "_helpers.ps1")

$root = Resolve-Path (Join-Path $PSScriptRoot "..\..")
$flag = Ensure-InstalledFlag -Root $root

$env:E2E_USERNAME = "e2e_b1"
$env:E2E_PASSWORD = "e2e_pass_12345"
$env:E2E_EMAIL = "e2e_b1@travianz.local"
$creds1 = Bootstrap-User -Root $root
$uid1 = Get-UserId -Root $root -Username $creds1.User

$env:E2E_USERNAME = "e2e_b2"
$env:E2E_PASSWORD = "e2e_pass_67890"
$env:E2E_EMAIL = "e2e_b2@travianz.local"
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
    Write-Host "T11: Start server $baseUrl"
    $server = Start-TestServer -Root $root -Port $port
    $s1 = New-WebSession
    Login-Session -BaseUrl $baseUrl -Session $s1 -Username $creds1.User -Password $creds1.Pass | Out-Null

    $tag = "R" + ([guid]::NewGuid().ToString("N").Substring(0,6))
    $name = "Alliance " + ([guid]::NewGuid().ToString("N").Substring(0,6))
    $createBody = @{ ft="ali1"; ally1=$tag; ally2=$name; s="1" }
    $createResp = Invoke-Req -Uri "$baseUrl/build.php?gid=18" -Method Post -Body $createBody -TimeoutSec 20 -WebSession $s1
    Assert-True ($createResp.StatusCode -eq 200) "create alliance failed"
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
    $invId = [int] ($invId | Select-Object -First 1)
    Invoke-Req -Uri "$baseUrl/allianz.php?a=3&d=$invId" -TimeoutSec 20 -WebSession $s2 | Out-Null

    $newTag = "RN" + ([guid]::NewGuid().ToString("N").Substring(0,4))
    $newName = "Renamed " + ([guid]::NewGuid().ToString("N").Substring(0,4))
    $pre = Invoke-Req -Uri "$baseUrl/allianz.php" -TimeoutSec 20 -WebSession $s1
    Assert-True ($pre.StatusCode -eq 200) "pre GET allianz failed"
    $renameBody = @{ s="1"; o="100"; ally1=$newTag; ally2=$newName }
    $renameResp = Invoke-Req -Uri "$baseUrl/allianz.php" -Method Post -Body $renameBody -TimeoutSec 20 -WebSession $s1
    Assert-True ($renameResp.StatusCode -eq 200) "rename POST failed"
    $alli = $null
    for ($i=0; $i -lt 10; $i++) {
        Start-Sleep -Milliseconds 200
        $alliJson = & php (Join-Path $root "tests\e2e\db_tools.php") get_alliance_json $aid
        $alli = $alliJson | ConvertFrom-Json
        if ($alli.tag -eq $newTag -and $alli.name -eq $newName) { break }
    }
    Assert-True ($alli.tag -eq $newTag) "tag not updated"
    Assert-True ($alli.name -eq $newName) "name not updated"

    $pre2 = Invoke-Req -Uri "$baseUrl/allianz.php" -TimeoutSec 20 -WebSession $s1
    Assert-True ($pre2.StatusCode -eq 200) "pre2 GET allianz failed"
    $permBody = @{ s="1"; o="1"; a="1"; a_user=$uid2; a_titel="Officer"; e1="1"; e2="1"; e3="1"; e4="1"; e5="1"; e6="1"; e7="1" }
    $permResp = Invoke-Req -Uri "$baseUrl/allianz.php" -Method Post -Body $permBody -TimeoutSec 20 -WebSession $s1
    Assert-True ($permResp.StatusCode -eq 200) "permissions POST failed"
    $permsJson = & php (Join-Path $root "tests\e2e\db_tools.php") get_alli_permissions_json $uid2 $aid
    $perms = $permsJson | ConvertFrom-Json
    Assert-True ($perms.rank -eq "Officer") "rank not updated"
    Assert-True ($perms.opt1 -eq "1" -and $perms.opt2 -eq "1" -and $perms.opt3 -eq "1" -and $perms.opt4 -eq "1" -and $perms.opt5 -eq "1" -and $perms.opt6 -eq "1" -and $perms.opt7 -eq "1") "options not updated"

    Write-Host "OK: T11"
    exit 0
} finally {
    if ($server) {
        Stop-TestServer -Server $server
        Cleanup-ServerLogs -Server $server
    }
    Cleanup-InstalledFlag -FlagInfo $flag
}
