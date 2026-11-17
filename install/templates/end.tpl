<?php

#################################################################################
##              -= YOU MAY NOT REMOVE OR CHANGE THIS NOTICE =-                 ##
## --------------------------------------------------------------------------- ##
##  Filename       : end.tpl                                                   ##
##  Type           : Install Panel Frontend & Backend                          ##
## --------------------------------------------------------------------------- ##
##  Developed by   : Dzoki (Original)                                          ##
##  Refactored by  : Shadow                                                    ##
##  Redesign by    : Shadow                                                    ##
## --------------------------------------------------------------------------- ##
##  License        : TravianZ Project                                          ##
##  Copyright      : TravianZ (c) 2010-2025. All rights reserved.              ##
## --------------------------------------------------------------------------- ##
#################################################################################

@include_once __DIR__ . '/../../GameEngine/config.php';
$time = time();
$tzInstallFolder = dirname(__DIR__);
$tzVarInstalled = dirname(__DIR__, 2) . '/var/installed';
if (!defined('TRAVIANZ_TEST_MODE')) {
    @rename($tzInstallFolder, dirname($tzInstallFolder) . '/installed_' . $time);
    @touch($tzVarInstalled);
}
$homepage = defined('HOMEPAGE') ? HOMEPAGE : '../';
?>
<div class="tz-card">
  <div class="tz-alert tz-alert-success">
    <strong>Installation complete.</strong> All six steps finished successfully.
  </div>
  <h3 class="tz-card-title">The installer is now locked</h3>
  <p class="tz-muted tz-lead">The <code>install/</code> folder was renamed to <code>installed_<?= $time ?></code> and the <code>var/installed</code> lock file was created, so the wizard cannot be run again. Delete the renamed folder from the server.</p>
  <p><a class="btn" href="<?= htmlspecialchars($homepage, ENT_QUOTES, 'UTF-8') ?>">Go to homepage</a></p>
</div>

<div class="tz-card">
  <h3 class="tz-card-title">Clean up on the server</h3>
  <p class="tz-note tz-lead">Remove the renamed installer folder and tighten permissions. Avoid world-writable directories.</p>
  <div class="tz-code">rm -R installed_*
chmod -R 755 GameEngine
chmod -R 775 GameEngine/Prevention GameEngine/Notes var/log</div>
</div>

<div class="tz-card">
  <h3 class="tz-card-title">Next steps</h3>
  <div class="grid-2">
    <div><strong>Secure your server</strong><p class="tz-note tz-mt-1">Protect /Admin with HTTP authentication, enable HTTPS and configure the cron job.</p></div>
    <div><strong>Read the docs</strong><p class="tz-note tz-mt-1">Speed settings, Natars and world configuration are documented in the GitHub Wiki.</p></div>
    <div><strong>Join the community</strong><p class="tz-note tz-mt-1">Get help, share mods and report bugs in GitHub Discussions.</p></div>
    <div><strong>Support the project</strong><p class="tz-note tz-mt-1">Star the repository if TravianZ is useful to you.</p></div>
  </div>
  <div class="tz-actions-row tz-mt-4">
    <a class="btn tz-btn-secondary" href="https://github.com/Shadowss/TravianZ" target="_blank" rel="noopener noreferrer">GitHub</a>
    <a class="btn tz-btn-secondary" href="https://github.com/Shadowss/TravianZ/wiki" target="_blank" rel="noopener noreferrer">Documentation</a>
  </div>
</div>
