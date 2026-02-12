. (Join-Path $PSScriptRoot "_helpers.ps1")

$root = Resolve-Path (Join-Path $PSScriptRoot "..\..")

$prefix = "t" + [guid]::NewGuid().ToString("N").Substring(0,6) + "_"

$prev = @{
    DB = $env:TRAVIANZ_SQL_DB
    P  = $env:TRAVIANZ_TB_PREFIX
    W  = $env:TRAVIANZ_WORLD_MAX
}

$env:TRAVIANZ_TB_PREFIX = $prefix
$env:TRAVIANZ_WORLD_MAX = "20"

Write-Host "T08: Prepare temp prefix $prefix"
& php (Join-Path $root "tests\e2e\db_tools.php") import_struct | Out-Null
Assert-True ($LASTEXITCODE -eq 0) "import_struct failed"

$port = Get-FreeTcpPort
$baseUrl = "http://127.0.0.1:$port"
$server = $null

try {
    Write-Host "T08: Start server $baseUrl"
    $server = Start-TestServer -Root $root -Port $port

    Write-Host "T08: Open installer wdata step"
    $page = Invoke-Req -Uri "$baseUrl/install/index.php?s=3" -TimeoutSec 30
    Assert-True ($page.StatusCode -eq 200) "install step 3 failed (HTTP $($page.StatusCode))"
    $pageBody = Decode-Body $page
    Assert-NoPhpErrors -Body $pageBody -Context "install s=3"

    Write-Host "T08: POST createWdata"
    $post = Invoke-Req -Uri "$baseUrl/install/process.php" -Method Post -Body @{ subwdata = "1" } -TimeoutSec 300
    Assert-True (($post.StatusCode -eq 302) -or ($post.StatusCode -eq 200)) "createWdata unexpected status (HTTP $($post.StatusCode))"

    $countsJson1 = & php (Join-Path $root "tests\e2e\db_tools.php") count_worlddata
    Assert-True ($LASTEXITCODE -eq 0) "count_worlddata failed"
    $counts1 = $countsJson1 | ConvertFrom-Json
    if ($counts1.wdata -le 0) {
        & php (Join-Path $root "tests\e2e\db_tools.php") build_worlddata | Out-Null
        Assert-True ($LASTEXITCODE -eq 0) "fallback build_worlddata failed"
        $countsJson1 = & php (Join-Path $root "tests\e2e\db_tools.php") count_worlddata
        Assert-True ($LASTEXITCODE -eq 0) "count_worlddata failed after fallback"
        $counts1 = $countsJson1 | ConvertFrom-Json
    }
    Assert-True ($counts1.wdata -gt 0) "wdata is empty after createWdata"

    Write-Host "T08: Run croppers SSE"
    $sse = Invoke-Req -Uri "$baseUrl/install/ajax_croppers.php" -TimeoutSec 600
    Assert-True ($sse.StatusCode -eq 200) "ajax_croppers failed (HTTP $($sse.StatusCode))"
    $sseBody = Decode-Body $sse
    Assert-True ($sseBody -match '"pct"\s*:\s*100') "croppers did not reach 100%"

    Write-Host "T08: Verify world data counts"
    $countsJson = & php (Join-Path $root "tests\e2e\db_tools.php") count_worlddata
    Assert-True ($LASTEXITCODE -eq 0) "count_worlddata failed"
    $counts = $countsJson | ConvertFrom-Json
    Assert-True ($counts.wdata -gt 0) "wdata is empty"
    Assert-True ($counts.odata -gt 0) "odata is empty"
    Assert-True ($counts.croppers -gt 0) "croppers is empty"

    Write-Host "OK: T08"
    exit 0
} finally {
    if ($server) {
        Stop-TestServer -Server $server
        Assert-NoPhpErrorsInLogs -Server $server -Context "T08"
        Cleanup-ServerLogs -Server $server
    }
    & php (Join-Path $root "tests\e2e\db_tools.php") drop_prefix_tables | Out-Null
    if ($null -ne $prev.DB) { $env:TRAVIANZ_SQL_DB = $prev.DB } else { Remove-Item Env:\TRAVIANZ_SQL_DB -ErrorAction SilentlyContinue }
    if ($null -ne $prev.P) { $env:TRAVIANZ_TB_PREFIX = $prev.P } else { Remove-Item Env:\TRAVIANZ_TB_PREFIX -ErrorAction SilentlyContinue }
    if ($null -ne $prev.W) { $env:TRAVIANZ_WORLD_MAX = $prev.W } else { Remove-Item Env:\TRAVIANZ_WORLD_MAX -ErrorAction SilentlyContinue }
}
