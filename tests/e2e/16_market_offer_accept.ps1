. (Join-Path $PSScriptRoot "_helpers.ps1")

$root = Resolve-Path (Join-Path $PSScriptRoot "..\..")
$flag = Ensure-InstalledFlag -Root $root

$env:E2E_USERNAME = "e2e_o1"
$env:E2E_PASSWORD = "e2e_pass_12345"
$env:E2E_EMAIL = "e2e_o1@travianz.local"
$creds1 = Bootstrap-User -Root $root
$uid1 = Get-UserId -Root $root -Username $creds1.User
$vref1 = & php (Join-Path $root "tests\e2e\db_tools.php") get_main_village_id $uid1
$vref1 = [int] ($vref1 | Select-Object -First 1)

$env:E2E_USERNAME = "e2e_o2"
$env:E2E_PASSWORD = "e2e_pass_67890"
$env:E2E_EMAIL = "e2e_o2@travianz.local"
$creds2 = Bootstrap-User -Root $root
$uid2 = Get-UserId -Root $root -Username $creds2.User
$vref2 = & php (Join-Path $root "tests\e2e\db_tools.php") get_main_village_id $uid2
$vref2 = [int] ($vref2 | Select-Object -First 1)

& php (Join-Path $root "tests\e2e\db_tools.php") set_building_level $vref1 17 17 1 | Out-Null
& php (Join-Path $root "tests\e2e\db_tools.php") set_building_level $vref2 17 17 1 | Out-Null

$port = Get-FreeTcpPort
$baseUrl = "http://127.0.0.1:$port"
$server = $null

try {
    Write-Host "T16: Start server $baseUrl"
    $server = Start-TestServer -Root $root -Port $port
    $s1 = New-WebSession
    Login-Session -BaseUrl $baseUrl -Session $s1 -Username $creds1.User -Password $creds1.Pass | Out-Null
    $page1 = Invoke-Req -Uri "$baseUrl/build.php?id=17&t=2" -TimeoutSec 20 -WebSession $s1
    Assert-True ($page1.StatusCode -eq 200) "market page seller failed"
    $offerBody = @{
        ft  = "mk2"
        id  = "17"
        rid1= "1"
        m1  = "50"
        rid2= "2"
        m2  = "50"
    }
    Invoke-Req -Uri "$baseUrl/build.php?id=17&t=2" -Method Post -Body $offerBody -TimeoutSec 20 -WebSession $s1 | Out-Null
    $offerId = & php (Join-Path $root "tests\e2e\db_tools.php") get_last_market_offer $vref1
    $offerId = [int] ($offerId | Select-Object -First 1)
    Assert-True ($offerId -gt 0) "offer not created"

    $s2 = New-WebSession
    Login-Session -BaseUrl $baseUrl -Session $s2 -Username $creds2.User -Password $creds2.Pass | Out-Null
    $page2 = Invoke-Req -Uri "$baseUrl/build.php?id=17&t=1" -TimeoutSec 20 -WebSession $s2
    Assert-True ($page2.StatusCode -eq 200) "market page buyer failed"
    $chk = [regex]::Match((Decode-Body $page2), 'name=\"a\"[^>]*value=\"([^\"]+)\"')
    $a = $chk.Groups[1].Value
    Assert-True (-not [string]::IsNullOrWhiteSpace($a)) "missing mchecker"
    $acceptUri = "$baseUrl/build.php?id=17&t=1&a=$a&g=$offerId"
    $accResp = Invoke-Req -Uri $acceptUri -TimeoutSec 20 -WebSession $s2
    Assert-True ($accResp.StatusCode -eq 200) "accept offer failed"

    Write-Host "OK: T16"
    exit 0
} finally {
    if ($server) {
        Stop-TestServer -Server $server
        Cleanup-ServerLogs -Server $server
    }
    Cleanup-InstalledFlag -FlagInfo $flag
}
