. (Join-Path $PSScriptRoot "_helpers.ps1")

$root = Resolve-Path (Join-Path $PSScriptRoot "..\..")
$flag = Ensure-InstalledFlag -Root $root

$env:E2E_USERNAME = "e2e_m1"
$env:E2E_PASSWORD = "e2e_pass_12345"
$env:E2E_EMAIL = "e2e_m1@travianz.local"
$creds1 = Bootstrap-User -Root $root
$uid1 = Get-UserId -Root $root -Username $creds1.User
$vref1 = & php (Join-Path $root "tests\e2e\db_tools.php") get_main_village_id $uid1
$vref1 = [int] ($vref1 | Select-Object -First 1)

$env:E2E_USERNAME = "e2e_m2"
$env:E2E_PASSWORD = "e2e_pass_67890"
$env:E2E_EMAIL = "e2e_m2@travianz.local"
$creds2 = Bootstrap-User -Root $root
$uid2 = Get-UserId -Root $root -Username $creds2.User
$vref2 = & php (Join-Path $root "tests\e2e\db_tools.php") get_main_village_id $uid2
$vref2 = [int] ($vref2 | Select-Object -First 1)

& php (Join-Path $root "tests\e2e\db_tools.php") set_building_level $vref1 17 17 1 | Out-Null

$port = Get-FreeTcpPort
$baseUrl = "http://127.0.0.1:$port"
$server = $null

try {
    Write-Host "T15: Start server $baseUrl"
    $server = Start-TestServer -Root $root -Port $port
    $s1 = New-WebSession
    Login-Session -BaseUrl $baseUrl -Session $s1 -Username $creds1.User -Password $creds1.Pass | Out-Null

    $page = Invoke-Req -Uri "$baseUrl/build.php?id=17" -TimeoutSec 20 -WebSession $s1
    Assert-True ($page.StatusCode -eq 200) "build.php?id=17 failed"
    $sendBody = @{
        ft      = "mk1"
        id      = "17"
        r1      = "50"
        r2      = "50"
        r3      = "0"
        r4      = "0"
        send3   = "1"
        getwref = "$vref2"
    }
    $resp = Invoke-Req -Uri "$baseUrl/build.php?id=17" -Method Post -Body $sendBody -TimeoutSec 20 -WebSession $s1
    Assert-True ($resp.StatusCode -eq 200) "market send failed"

    Write-Host "OK: T15"
    exit 0
} finally {
    if ($server) {
        Stop-TestServer -Server $server
        Cleanup-ServerLogs -Server $server
    }
    Cleanup-InstalledFlag -FlagInfo $flag
}
