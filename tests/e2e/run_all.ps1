param(
    [ValidateSet("quick", "full")]
    [string] $Mode = "quick",
    [int] $StressIterations = 120
)

$ErrorActionPreference = "Stop"

$suitePrefix = "s" + [guid]::NewGuid().ToString("N").Substring(0,6) + "_"
$env:TRAVIANZ_TB_PREFIX = $suitePrefix
$env:TRAVIANZ_WORLD_MAX = if ($env:TRAVIANZ_WORLD_MAX) { $env:TRAVIANZ_WORLD_MAX } else { "20" }

Write-Host "BOOT: PREFIX=$suitePrefix WORLD_MAX=$($env:TRAVIANZ_WORLD_MAX)"
& php (Join-Path $PSScriptRoot "db_tools.php") import_struct | Out-Null
if ($LASTEXITCODE -ne 0) { throw "import_struct failed" }
& php (Join-Path $PSScriptRoot "db_tools.php") build_worlddata | Out-Null
if ($LASTEXITCODE -ne 0) { throw "build_worlddata failed" }

$tests = @(
    "01_basic_smoke.ps1",
    "02_auth_flow.ps1",
    "03_map_and_ajax.ps1",
    "04_pages_authenticated.ps1",
    "06_messages.ps1",
    "07_more_pages.ps1",
    "09_alliance_create.ps1",
    "10_alliance_invite_and_accept.ps1",
    "11_alliance_rename_and_permissions.ps1"
    "12_alliance_logs_page.ps1",
    "13_alliance_kick_member.ps1",
    "14_alliance_profile_update.ps1"
    "15_market_send_resources.ps1",
    "16_market_offer_accept.ps1",
    "17_market_trade_gold.ps1"
)

if ($Mode -eq "full") {
    $tests += "08_installer_worlddata.ps1"
    $tests += "05_stress.ps1"
}

$failed = 0
foreach ($t in $tests) {
    $path = Join-Path $PSScriptRoot $t
    Write-Host "RUN: $t"
    if ($t -eq "05_stress.ps1") {
        & $path -Iterations $StressIterations
    } else {
        & $path
    }
    if ($LASTEXITCODE -ne 0) {
        $failed++
        break
    }
}

if ($failed -ne 0) {
    Write-Host "FAIL: E2E suite"
    & php (Join-Path $PSScriptRoot "db_tools.php") drop_prefix_tables | Out-Null
    exit 1
}

& php (Join-Path $PSScriptRoot "db_tools.php") drop_prefix_tables | Out-Null
Write-Host "OK: E2E suite ($Mode)"
exit 0
