$ErrorActionPreference = "Stop"

function Assert-True {
    param(
        [Parameter(Mandatory = $true)][bool] $Condition,
        [Parameter(Mandatory = $true)][string] $Message
    )
    if (-not $Condition) {
        throw $Message
    }
}

function Get-FreeTcpPort {
    $listener = [System.Net.Sockets.TcpListener]::new([System.Net.IPAddress]::Loopback, 0)
    $listener.Start()
    $port = $listener.LocalEndpoint.Port
    $listener.Stop()
    return $port
}

function Wait-HttpOk {
    param(
        [Parameter(Mandatory = $true)][string] $Url,
        [int] $Retries = 30,
        [int] $DelayMs = 250
    )
    for ($i = 0; $i -lt $Retries; $i++) {
        try {
            $r = Invoke-WebRequest -Uri $Url -UseBasicParsing -TimeoutSec 2 -SkipHttpErrorCheck
            if ($r.StatusCode -ge 200 -and $r.StatusCode -lt 500) {
                return
            }
        } catch {
        }
        Start-Sleep -Milliseconds $DelayMs
    }
    throw "Timed out waiting for server: $Url"
}

$root = Resolve-Path (Join-Path $PSScriptRoot "..\..")

Write-Host "E2E: PHP modules"
$phpIni = & php --ini
Assert-True ($LASTEXITCODE -eq 0) "php --ini failed"

$modules = (& php -m) | ForEach-Object { $_.Trim() } | Where-Object { $_ -ne "" }
Assert-True ($modules -contains "mysqli") "mysqli extension not loaded"
Assert-True ($modules -contains "mbstring") "mbstring extension not loaded"

Write-Host "E2E: DB smoke"
& php (Join-Path $root "tests\e2e\db_smoke.php")
Assert-True ($LASTEXITCODE -eq 0) "DB smoke test failed"

$installedPath = Join-Path $root "var\installed"
$createdInstalled = $false
if (-not (Test-Path $installedPath)) {
    New-Item -ItemType File -Path $installedPath -Force | Out-Null
    $createdInstalled = $true
}

Write-Host "E2E: Bootstrap test user"
& php (Join-Path $root "tests\e2e\bootstrap_user.php")
Assert-True ($LASTEXITCODE -eq 0) "Bootstrap user failed"

$port = Get-FreeTcpPort
$baseUrl = "http://127.0.0.1:$port"

Write-Host "E2E: Start PHP server ($baseUrl)"
$server = Start-Process -FilePath "php" -ArgumentList @("-S", "127.0.0.1:$port", "router.php") -WorkingDirectory $root -PassThru -NoNewWindow
try {
    Wait-HttpOk -Url "$baseUrl/"

    Write-Host "E2E: HTTP smoke"
    $homeResp = Invoke-WebRequest -Uri "$baseUrl/" -UseBasicParsing -TimeoutSec 10 -SkipHttpErrorCheck
    Assert-True ($homeResp.StatusCode -eq 200) "Home page failed (HTTP $($homeResp.StatusCode))"

    $session = [Microsoft.PowerShell.Commands.WebRequestSession]::new()

    Write-Host "E2E: Login flow"
    $loginPage = Invoke-WebRequest -Uri "$baseUrl/login.php" -UseBasicParsing -TimeoutSec 10 -SkipHttpErrorCheck -WebSession $session
    Assert-True ($loginPage.StatusCode -eq 200) "login.php failed (HTTP $($loginPage.StatusCode))"

    $csrfMatch = [regex]::Match($loginPage.Content, 'name=\"csrf\"[^>]*value=\"([^\"]+)\"')
    $csrf = $csrfMatch.Groups[1].Value
    Assert-True (-not [string]::IsNullOrWhiteSpace($csrf)) "CSRF token missing on login page"

    $e2eUser = if ($env:E2E_USERNAME) { $env:E2E_USERNAME } else { "e2e_user" }
    $e2ePass = if ($env:E2E_PASSWORD) { $env:E2E_PASSWORD } else { "e2e_pass_12345" }
    $loginBody = @{
        ft   = "a4"
        user = $e2eUser
        pw   = $e2ePass
        csrf = $csrf
        s1   = "login"
    }
    $afterLogin = Invoke-WebRequest -Uri "$baseUrl/login.php" -Method Post -Body $loginBody -UseBasicParsing -TimeoutSec 15 -SkipHttpErrorCheck -WebSession $session
    Assert-True ($afterLogin.StatusCode -eq 200) "Post-login navigation failed (HTTP $($afterLogin.StatusCode))"

    $dorf1 = Invoke-WebRequest -Uri "$baseUrl/dorf1.php" -UseBasicParsing -TimeoutSec 15 -SkipHttpErrorCheck -WebSession $session
    Assert-True ($dorf1.StatusCode -eq 200) "dorf1.php failed after login (HTTP $($dorf1.StatusCode))"
    Assert-True ($dorf1.Content -match 'logout\.php') "dorf1.php does not look like an authenticated page"

    Write-Host "E2E: Map page"
    $karte = Invoke-WebRequest -Uri "$baseUrl/karte.php" -UseBasicParsing -TimeoutSec 15 -SkipHttpErrorCheck -WebSession $session
    Assert-True ($karte.StatusCode -eq 200) "karte.php failed (HTTP $($karte.StatusCode))"
    Assert-True ($karte.Content -match '<title>.*World Map.*</title>') "karte.php returned unexpected content"

    $map = Invoke-WebRequest -Uri "$baseUrl/ajax.php?f=k7&x=0&y=0&xx=0&yy=0" -UseBasicParsing -TimeoutSec 10 -SkipHttpErrorCheck -WebSession $session
    Assert-True ($map.StatusCode -eq 200) "Map ajax failed (HTTP $($map.StatusCode))"
    $mapBody = if ($map.Content -is [byte[]]) { [System.Text.Encoding]::UTF8.GetString($map.Content) } else { [string]$map.Content }
    $contentType = [string] $map.Headers['Content-Type']
    Assert-True ($contentType -match 'application/json') "Map ajax unexpected content-type"
    Assert-True ($mapBody.TrimStart() -match '^\[') "Map ajax returned unexpected body"
    $null = $mapBody | ConvertFrom-Json

    Write-Host "E2E: Logout flow"
    $logout = Invoke-WebRequest -Uri "$baseUrl/logout.php" -UseBasicParsing -TimeoutSec 15 -SkipHttpErrorCheck -WebSession $session
    Assert-True ($logout.StatusCode -eq 200) "logout.php failed (HTTP $($logout.StatusCode))"
    Assert-True ($logout.Content -match 'Logout successful') "logout.php returned unexpected content"

    $dorf1AfterLogout = Invoke-WebRequest -Uri "$baseUrl/dorf1.php" -UseBasicParsing -TimeoutSec 15 -SkipHttpErrorCheck -WebSession $session
    Assert-True ($dorf1AfterLogout.StatusCode -eq 200) "dorf1.php after logout failed (HTTP $($dorf1AfterLogout.StatusCode))"
    Assert-True (-not ($dorf1AfterLogout.Content -match 'logout\.php')) "dorf1.php still looks authenticated after logout"

    Write-Host "OK: E2E smoke passed"
    exit 0
} finally {
    if ($null -ne $server -and -not $server.HasExited) {
        Stop-Process -Id $server.Id -Force
    }
    if ($createdInstalled -and (Test-Path $installedPath)) {
        Remove-Item -Path $installedPath -Force
    }
}
