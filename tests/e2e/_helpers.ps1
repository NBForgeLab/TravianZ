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

function Assert-NoPhpErrors {
    param(
        [Parameter(Mandatory = $true)][string] $Body,
        [Parameter(Mandatory = $true)][string] $Context
    )
    $patterns = @(
        'PHP Warning:',
        'PHP Fatal error:',
        'Fatal error:',
        'Uncaught ',
        'Deprecated:',
        'Notice:'
    )
    foreach ($p in $patterns) {
        if ($Body -match [regex]::Escape($p)) {
            throw "$Context يحتوي مخرجات خطأ PHP: $p"
        }
    }
}

function Invoke-Req {
    param(
        [Parameter(Mandatory = $true)][string] $Uri,
        [string] $Method = "GET",
        [hashtable] $Body = $null,
        $WebSession = $null,
        [int] $TimeoutSec = 15
    )

    $params = @{
        Uri           = $Uri
        Method        = $Method
        UseBasicParsing = $true
        TimeoutSec    = $TimeoutSec
    }
    if ($null -ne $Body) { $params.Body = $Body }
    if ($null -ne $WebSession) { $params.WebSession = $WebSession }
    if ($PSVersionTable.PSVersion.Major -ge 7) { $params.SkipHttpErrorCheck = $true }

    try {
        return Invoke-WebRequest @params
    } catch {
        $resp = $_.Exception.Response
        if ($null -ne $resp -and $resp -is [System.Net.HttpWebResponse]) {
            $stream = $resp.GetResponseStream()
            $reader = New-Object System.IO.StreamReader($stream)
            $content = $reader.ReadToEnd()
            return [pscustomobject]@{
                StatusCode   = [int] $resp.StatusCode
                Headers      = $resp.Headers
                Content      = $content
                BaseResponse = $resp
            }
        }
        throw
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
        [int] $Retries = 40,
        [int] $DelayMs = 250
    )
    for ($i = 0; $i -lt $Retries; $i++) {
        try {
            $r = Invoke-Req -Uri $Url -TimeoutSec 2
            if ($r.StatusCode -ge 200 -and $r.StatusCode -lt 500) {
                return
            }
        } catch {
        }
        Start-Sleep -Milliseconds $DelayMs
    }
    throw "Timeout: $Url"
}

function Start-TestServer {
    param(
        [Parameter(Mandatory = $true)][string] $Root,
        [Parameter(Mandatory = $true)][int] $Port
    )
    $outLog = [System.IO.Path]::Combine([System.IO.Path]::GetTempPath(), "travianz-e2e-php-out-$Port.log")
    $errLog = [System.IO.Path]::Combine([System.IO.Path]::GetTempPath(), "travianz-e2e-php-err-$Port.log")
    if (Test-Path $outLog) { Remove-Item $outLog -Force }
    if (Test-Path $errLog) { Remove-Item $errLog -Force }

    $proc = Start-Process -FilePath "php" -ArgumentList @("-S", "127.0.0.1:$Port", "router.php") -WorkingDirectory $Root -PassThru -NoNewWindow -RedirectStandardOutput $outLog -RedirectStandardError $errLog
    Wait-HttpOk -Url "http://127.0.0.1:$Port/"
    return @{ Process = $proc; OutLog = $outLog; ErrLog = $errLog }
}

function Stop-TestServer {
    param([Parameter(Mandatory = $true)] $Server)
    $proc = $Server.Process
    if ($null -ne $proc -and -not $proc.HasExited) {
        Stop-Process -Id $proc.Id -Force
        try {
            Wait-Process -Id $proc.Id -Timeout 3 -ErrorAction SilentlyContinue | Out-Null
        } catch {
        }
    }
}

function Assert-NoPhpErrorsInLogs {
    param(
        [Parameter(Mandatory = $true)] $Server,
        [Parameter(Mandatory = $true)][string] $Context
    )

    $text = ""
    if (Test-Path $Server.OutLog) { $text += (Get-Content -Raw -Path $Server.OutLog) }
    if (Test-Path $Server.ErrLog) { $text += (Get-Content -Raw -Path $Server.ErrLog) }

    if ($text -match 'PHP Warning:' -or $text -match 'PHP Fatal error:' -or $text -match 'Fatal error:' -or $text -match 'Uncaught ' -or $text -match 'Deprecated:' -or $text -match 'Notice:') {
        throw "سيرفر PHP أنتج أخطاء أثناء $Context"
    }
}

function Cleanup-ServerLogs {
    param([Parameter(Mandatory = $true)] $Server)
    $paths = @($Server.OutLog, $Server.ErrLog) | Where-Object { $_ -and (Test-Path $_) }
    foreach ($p in $paths) {
        for ($i = 0; $i -lt 10; $i++) {
            try {
                if (Test-Path $p) { Remove-Item $p -Force -ErrorAction Stop }
                break
            } catch {
                Start-Sleep -Milliseconds 150
            }
        }
    }
}

function Ensure-InstalledFlag {
    param([Parameter(Mandatory = $true)][string] $Root)
    $path = Join-Path $Root "var\installed"
    $created = $false
    if (-not (Test-Path $path)) {
        New-Item -ItemType File -Path $path -Force | Out-Null
        $created = $true
    }
    return @{ Path = $path; Created = $created }
}

function Cleanup-InstalledFlag {
    param(
        [Parameter(Mandatory = $true)][hashtable] $FlagInfo
    )
    if ($FlagInfo.Created -and (Test-Path $FlagInfo.Path)) {
        Remove-Item -Path $FlagInfo.Path -Force
    }
}

function Decode-Body {
    param([Parameter(Mandatory = $true)] $Response)
    if ($Response.Content -is [byte[]]) {
        return [System.Text.Encoding]::UTF8.GetString($Response.Content)
    }
    return [string] $Response.Content
}

function Bootstrap-User {
    param([Parameter(Mandatory = $true)][string] $Root)
    & php (Join-Path $Root "tests\e2e\bootstrap_user.php") | Out-Null
    Assert-True ($LASTEXITCODE -eq 0) "Bootstrap user failed"
    $user = if ($env:E2E_USERNAME) { $env:E2E_USERNAME } else { "e2e_user" }
    $pass = if ($env:E2E_PASSWORD) { $env:E2E_PASSWORD } else { "e2e_pass_12345" }
    return @{ User = $user; Pass = $pass }
}

function Get-UserId {
    param(
        [Parameter(Mandatory = $true)][string] $Root,
        [Parameter(Mandatory = $true)][string] $Username
    )
    $id = & php (Join-Path $Root "tests\e2e\db_tools.php") get_user_id $Username
    if ($LASTEXITCODE -ne 0) { throw "get_user_id failed" }
    return [int] ($id | Select-Object -First 1)
}

function New-WebSession {
    return [Microsoft.PowerShell.Commands.WebRequestSession]::new()
}

function Get-CsrfFromLogin {
    param([Parameter(Mandatory = $true)][string] $Html)
    $m = [regex]::Match($Html, 'name=\"csrf\"[^>]*value=\"([^\"]+)\"')
    return $m.Groups[1].Value
}

function Login-Session {
    param(
        [Parameter(Mandatory = $true)][string] $BaseUrl,
        [Parameter(Mandatory = $true)] $Session,
        [Parameter(Mandatory = $true)][string] $Username,
        [Parameter(Mandatory = $true)][string] $Password
    )
    $loginPage = Invoke-Req -Uri "$BaseUrl/login.php" -TimeoutSec 15 -WebSession $Session
    Assert-True ($loginPage.StatusCode -eq 200) "login.php failed (HTTP $($loginPage.StatusCode))"
    $loginBodyHtml = Decode-Body $loginPage
    Assert-NoPhpErrors -Body $loginBodyHtml -Context "login.php"

    $csrf = Get-CsrfFromLogin -Html $loginBodyHtml
    Assert-True (-not [string]::IsNullOrWhiteSpace($csrf)) "Missing CSRF token on login page"

    $body = @{
        ft   = "a4"
        user = $Username
        pw   = $Password
        csrf = $csrf
        s1   = "login"
    }
    $resp = Invoke-Req -Uri "$BaseUrl/login.php" -Method Post -Body $body -TimeoutSec 20 -WebSession $Session
    Assert-True ($resp.StatusCode -eq 200) "Login POST did not reach a page (HTTP $($resp.StatusCode))"
    return $resp
}
