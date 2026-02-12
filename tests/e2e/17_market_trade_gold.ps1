. (Join-Path $PSScriptRoot "_helpers.ps1")

$root = Resolve-Path (Join-Path $PSScriptRoot "..\..")
$flag = Ensure-InstalledFlag -Root $root

$env:E2E_USERNAME = "e2e_g1"
$env:E2E_PASSWORD = "e2e_pass_12345"
$env:E2E_EMAIL = "e2e_g1@travianz.local"
$creds1 = Bootstrap-User -Root $root
$uid1 = Get-UserId -Root $root -Username $creds1.User
$vref1 = & php (Join-Path $root "tests\e2e/db_tools.php") get_main_village_id $uid1
$vref1 = [int] ($vref1 | Select-Object -First 1)

& php (Join-Path $root "tests\e2e/db_tools.php") set_building_level $vref1 17 17 1 | Out-Null
& php (Join-Path $root "tests\e2e/db_tools.php") set_user_gold $uid1 10 | Out-Null

$port = Get-FreeTcpPort
$baseUrl = "http://127.0.0.1:$port"
$server = $null

try {
    Write-Host "T17: Start server $baseUrl"
    $server = Start-TestServer -Root $root -Port $port
    $s1 = New-WebSession
    Login-Session -BaseUrl $baseUrl -Session $s1 -Username $creds1.User -Password $creds1.Pass | Out-Null

    $page = Invoke-Req -Uri "$baseUrl/build.php?id=17&t=3" -TimeoutSec 20 -WebSession $s1
    Assert-True ($page.StatusCode -eq 200) "market gold page failed"
    $tradeBody = @{
        ft = "mk3"
        id = "17"
        m2 = @(1,1,1,1)
    }
    $before = & php (Join-Path $root "tests\\e2e\\db_tools.php") get_user_gold $uid1
    $resp = Invoke-Req -Uri "$baseUrl/build.php?id=17&t=3" -Method Post -Body $tradeBody -TimeoutSec 20 -WebSession $s1
    Assert-True ($resp.StatusCode -eq 200) "mk3 trade failed"
    $after = & php (Join-Path $root "tests\\e2e\\db_tools.php") get_user_gold $uid1
    $before = [int] ($before | Select-Object -First 1)
    $after  = [int] ($after  | Select-Object -First 1)
    Assert-True ($after -eq ($before - 3)) "gold not deducted by 3"

    Write-Host "OK: T17"
    exit 0
} finally {
    if ($server) {
        Stop-TestServer -Server $server
        Cleanup-ServerLogs -Server $server
    }
    Cleanup-InstalledFlag -FlagInfo $flag
}
