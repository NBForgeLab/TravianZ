<?php
// don't let SQL time out when 30-500 seconds (depending on php.ini) is not enough
@set_time_limit(0);

if (!headers_sent()) {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

if (session_status() === PHP_SESSION_NONE) {
    if (!headers_sent()) {
        @session_set_cookie_params([
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
    @session_start();
}
if (empty($_SESSION['tz_csrf'])) {
    try {
        $_SESSION['tz_csrf'] = bin2hex(random_bytes(32));
    } catch (Throwable $e) {
        $_SESSION['tz_csrf'] = bin2hex(pack('N8', mt_rand(), mt_rand(), mt_rand(), mt_rand(), mt_rand(), mt_rand(), mt_rand(), mt_rand()));
    }
}

require_once __DIR__ . '/templates/script.tpl';

if (!isset($_GET['s'])) {
    $_GET['s'] = 0;
}
$tz = (isset($_GET['t'])) ? (int) $_GET['t'] : 8;
switch ($tz) {
    case 1: $t_zone = "Africa/Dakar"; break;
    case 2: $t_zone = "America/New_York"; break;
    case 3: $t_zone = "Antarctica/Casey"; break;
    case 4: $t_zone = "Arctic/Longyearbyen"; break;
    case 5: $t_zone = "Asia/Kuala_Lumpur"; break;
    case 6: $t_zone = "Atlantic/Azores"; break;
    case 7: $t_zone = "Australia/Melbourne"; break;
    case 8: $t_zone = "Europe/Bucharest"; break;
    case 9: $t_zone = "Europe/London"; break;
    case 10: $t_zone = "Europe/Bratislava"; break;
    case 11: $t_zone = "Indian/Maldives"; break;
    case 12: $t_zone = "Pacific/Fiji"; break;
    case 13: $t_zone = "America/Sao_Paulo"; break;
    case 14: $t_zone = "Europe/Zurich"; break;
    default: $t_zone = "America/Sao_Paulo"; break;
}
date_default_timezone_set($t_zone);

$tzStep = max(0, min(5, (int) $_GET['s']));
$tzSteps = ['Intro', 'Configuration', 'Database', 'World Data', 'Accounts', 'End'];
$tzPercent = round((($tzStep + 1) / count($tzSteps)) * 100, 2);
$tzBuild = 'v11.0.0 (Installer Revamped)';
?>
<!DOCTYPE html>
<html lang="en" dir="ltr" data-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light dark">
<title>TravianZ Installation</title>
<link rel="icon" href="../favicon.ico">
<?php tz_install_head_assets(); ?>
</head>
<body>
<div class="tz-app">

  <header class="tz-header">
    <span class="tz-logo" aria-hidden="true">
      <svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M16 2 4 7v8c0 7.5 5.2 14.5 12 16.5C22.8 29.5 28 22.5 28 15V7L16 2Z" fill="#22c55e"/>
        <path d="m11 16 3.5 3.5L21.5 12" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </span>
    <div class="tz-header-text">
      <h1>TravianZ installation</h1>
      <p>Setup wizard &middot; <?= htmlspecialchars($tzBuild, ENT_QUOTES, 'UTF-8') ?></p>
    </div>
    <div class="tz-header-actions">
      <button type="button" class="tz-icon-btn" id="tzThemeToggle" aria-pressed="false" title="Toggle theme" aria-label="Toggle colour theme">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M20 14.5A8.5 8.5 0 0 1 9.5 4a8.5 8.5 0 1 0 10.5 10.5Z"/></svg>
      </button>
      <a class="tz-help" href="https://github.com/Shadowss/TravianZ/wiki" target="_blank" rel="noopener noreferrer">Help</a>
    </div>
  </header>

  <nav class="tz-steps" aria-label="Installation progress">
    <?php include __DIR__ . '/templates/menu.tpl'; ?>
  </nav>
  <div class="tz-progress" role="progressbar" aria-label="Installation progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= (int) $tzPercent ?>">
    <span style="width:<?= $tzPercent ?>%"></span>
  </div>

  <main class="tz-main" id="tzMain">
    <div class="tz-step-head">
      <h2><?= htmlspecialchars($tzSteps[$tzStep], ENT_QUOTES, 'UTF-8') ?></h2>
      <p class="tz-eyebrow">Step <?= $tzStep + 1 ?> of <?= count($tzSteps) ?></p>
    </div>

<?php
$tzRoot = dirname(__DIR__);
$tzUnwritable = [];
if (!is_dir($tzRoot . '/GameEngine') || !is_writable($tzRoot . '/GameEngine')) {
    $tzUnwritable[] = 'GameEngine/';
}
if (!is_dir($tzRoot . '/GameEngine/Admin/Mods') || !is_writable($tzRoot . '/GameEngine/Admin/Mods')) {
    $tzUnwritable[] = 'GameEngine/Admin/Mods/';
}
if (!is_dir($tzRoot . '/var') || !is_writable($tzRoot . '/var')) {
    $tzUnwritable[] = 'var/';
}

if (!empty($tzUnwritable)) {
    echo '<div class="tz-alert tz-alert-error"><strong>ERROR!</strong><br>The following directories cannot be written by the web server: <code>' . htmlspecialchars(implode(', ', $tzUnwritable), ENT_QUOTES, 'UTF-8') . '</code>.<br>Grant the web server write access to these directories, then refresh this page.</div>';
} elseif (file_exists($tzRoot . '/var/installed')) {
    echo '<div class="tz-alert tz-alert-error"><strong>ERROR!</strong><br>Installation appears to have been completed. If this is a mistake, remove the <code>var/installed</code> file.</div>';
} else {
    switch ($tzStep) {
        case 0: include __DIR__ . '/templates/greet.tpl'; break;
        case 1: include __DIR__ . '/templates/config.tpl'; break;
        case 2: include __DIR__ . '/templates/dataform.tpl'; break;
        case 3: include __DIR__ . '/templates/wdata.tpl'; break;
        case 4: include __DIR__ . '/templates/accounts.tpl'; break;
        case 5: include __DIR__ . '/templates/end.tpl'; break;
    }
}
?>
  </main>

  <footer class="tz-footer">
    <span>&copy; 2010 - 2026 TravianZ &middot; All rights reserved</span>
    <span>Server build <b><?= htmlspecialchars($tzBuild, ENT_QUOTES, 'UTF-8') ?></b></span>
  </footer>

</div>
<?php tz_install_body_assets(); ?>
</body>
</html>
