. (Join-Path $PSScriptRoot "_helpers.ps1")

$root = Resolve-Path (Join-Path $PSScriptRoot "..\..")
$flag = Ensure-InstalledFlag -Root $root
$creds = Bootstrap-User -Root $root

$port = Get-FreeTcpPort
$baseUrl = "http://127.0.0.1:$port"
$server = $null

try {
    Write-Host "T06: Start server $baseUrl"
    $server = Start-TestServer -Root $root -Port $port
    $session = New-WebSession

    Write-Host "T06: Login"
    Login-Session -BaseUrl $baseUrl -Session $session -Username $creds.User -Password $creds.Pass | Out-Null

    $uid = Get-UserId -Root $root -Username $creds.User
    Assert-True ($uid -gt 0) "Could not resolve user id"

    $topic = "e2e_msg_" + [guid]::NewGuid().ToString("N").Substring(0,12)
    $body = @{
        ft      = "m2"
        an      = $creds.User
        be      = $topic
        message = "hello from e2e"
        c       = "3e9"
        p       = ""
    }

    Write-Host "T06: Send message"
    $send = Invoke-Req -Uri "$baseUrl/nachrichten.php" -Method Post -Body $body -TimeoutSec 30 -WebSession $session
    Assert-True (($send.StatusCode -eq 200) -or ($send.StatusCode -eq 302)) "Message send failed (HTTP $($send.StatusCode))"
    $sendBody = Decode-Body $send
    Assert-NoPhpErrors -Body $sendBody -Context "nachrichten.php send"

    $mid = & php (Join-Path $root "tests\e2e\db_tools.php") has_message $uid $topic
    Assert-True ($LASTEXITCODE -eq 0) "DB message check failed"
    Assert-True ([int]($mid | Select-Object -First 1) -gt 0) "Message not found in DB"

    Write-Host "OK: T06"
    exit 0
} finally {
    if ($server) {
        Stop-TestServer -Server $server
        Assert-NoPhpErrorsInLogs -Server $server -Context "T06"
        Cleanup-ServerLogs -Server $server
    }
    Cleanup-InstalledFlag -FlagInfo $flag
}
