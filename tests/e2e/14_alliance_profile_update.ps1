. (Join-Path $PSScriptRoot "_helpers.ps1")

$root = Resolve-Path (Join-Path $PSScriptRoot "..\..")
$flag = Ensure-InstalledFlag -Root $root

$env:E2E_USERNAME = "e2e_e1"
$env:E2E_PASSWORD = "e2e_pass_12345"
$env:E2E_EMAIL = "e2e_e1@travianz.local"
$creds1 = Bootstrap-User -Root $root
$uid1 = Get-UserId -Root $root -Username $creds1.User

$vref1 = & php (Join-Path $root "tests\e2e\db_tools.php") get_main_village_id $uid1
if ($LASTEXITCODE -ne 0) { throw "get_main_village_id failed" }
$vref1 = [int] ($vref1 | Select-Object -First 1)
& php (Join-Path $root "tests\e2e\db_tools.php") set_embassy_level $vref1 3 | Out-Null
if ($LASTEXITCODE -ne 0) { throw "set_embassy_level failed" }

$port = Get-FreeTcpPort
$baseUrl = "http://127.0.0.1:$port"
$server = $null

try {
    Write-Host "T14: Start server $baseUrl"
    $server = Start-TestServer -Root $root -Port $port
    $s1 = New-WebSession
    Login-Session -BaseUrl $baseUrl -Session $s1 -Username $creds1.User -Password $creds1.Pass | Out-Null

    $tag = "P" + ([guid]::NewGuid().ToString("N").Substring(0,6))
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

    $desc = "Alliance Desc " + ([guid]::NewGuid().ToString("N").Substring(0,6))
    $notice = "Alliance Notice " + ([guid]::NewGuid().ToString("N").Substring(0,6))
    $profileBody = @{ s="1"; o="3"; be1=$desc; be2=$notice }
    $profileResp = Invoke-Req -Uri "$baseUrl/allianz.php" -Method Post -Body $profileBody -TimeoutSec 20 -WebSession $s1
    Assert-True ($profileResp.StatusCode -eq 200) "profile update POST failed"

    $alliJson = & php (Join-Path $root "tests\e2e\db_tools.php") get_alliance_json $aid
    $alli = $alliJson | ConvertFrom-Json
    Assert-True ($alli.desc -eq $desc) "desc not updated"
    Assert-True ($alli.notice -eq $notice) "notice not updated"

    Write-Host "OK: T14"
    exit 0
} finally {
    if ($server) {
        Stop-TestServer -Server $server
        Cleanup-ServerLogs -Server $server
    }
    Cleanup-InstalledFlag -FlagInfo $flag
}
