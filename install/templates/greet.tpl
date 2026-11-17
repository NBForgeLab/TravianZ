<?php

#################################################################################
##              -= YOU MAY NOT REMOVE OR CHANGE THIS NOTICE =-                 ##
## --------------------------------------------------------------------------- ##
##  Filename       : greet.tpl                                                 ##
##  Type           : Install Panel Frontend & Backend                          ##
## --------------------------------------------------------------------------- ##
##  Developed by   : Dzoki (Original)                                          ##
##  Refactored by  : Shadow                                                    ##
##  Redesign by    : Shadow                                                    ##
## --------------------------------------------------------------------------- ##
##  Contact        : cata7007@gmail.com                                        ##
##  Project        : TravianZ                                                  ##
##  GitHub         : https://github.com/Shadowss/TravianZ                      ##
## --------------------------------------------------------------------------- ##
##  License        : TravianZ Project                                          ##
##  Copyright      : TravianZ (c) 2010-2025. All rights reserved.              ##
## --------------------------------------------------------------------------- ##
#################################################################################

$checks = tz_install_checks();
$failed = 0;
foreach ($checks as $check) { if (!$check['ok']) $failed++; }
?>
<div class="tz-grid-2">
  <div>
    <div class="tz-card">
      <h3 class="tz-card-title">Before you start</h3>
      <p class="tz-muted tz-lead">The wizard writes <code>GameEngine/config.php</code> and <code>GameEngine/Admin/Mods/constant_format.tpl</code>, imports the database schema and generates the world map. Give write access to the web-server user only &mdash; <strong>never use <code>chmod 777</code></strong> on a public server.</p>
      <div class="tz-code"># Linux example (adjust the user to your web server)
chown -R www-data:www-data GameEngine var
chmod -R 755 GameEngine var
chmod -R 775 GameEngine/Admin/Mods var/log</div>
      <p class="tz-note tz-mt-2">On Windows, grant "Modify" to the IIS/Apache account for those folders.</p>
    </div>

    <div class="tz-card">
      <h3 class="tz-card-title">After installation</h3>
      <p class="tz-muted tz-lead">The final step automatically renames <code>install/</code> to <code>installed_&lt;timestamp&gt;</code>. Delete that folder so nobody can run the installer again.</p>
      <div class="tz-code">rm -R installed_*
chmod -R 755 GameEngine
chmod -R 775 GameEngine/Prevention GameEngine/Notes var/log</div>
      <p class="tz-note tz-mt-2">Protect <code>/Admin</code> with HTTP authentication and enable HTTPS before opening registration.</p>
    </div>
  </div>

  <div>
    <div class="tz-card">
      <h3 class="tz-card-title">System checks</h3>
<?php if ($failed): ?>
      <div class="tz-alert tz-alert-warning tz-mb-3"><?php echo $failed; ?> check(s) need attention before you continue.</div>
<?php else: ?>
      <div class="tz-alert tz-mb-3">Everything looks ready. You can start the setup.</div>
<?php endif; ?>
      <ul class="tz-checks">
<?php foreach ($checks as $check): ?>
        <li class="tz-check<?php echo $check['ok'] ? '' : ' is-bad'; ?>">
          <span class="tz-dot" aria-hidden="true"></span>
          <span><?php echo htmlspecialchars($check['label'], ENT_QUOTES, 'UTF-8'); ?></span>
          <span class="tz-check-detail"><?php echo htmlspecialchars($check['detail'], ENT_QUOTES, 'UTF-8'); ?></span>
        </li>
<?php endforeach; ?>
      </ul>
    </div>

    <div class="tz-card">
      <h3 class="tz-card-title">Disclaimer</h3>
      <ul class="tz-muted tz-disclaimer-list">
        <li>You are fully responsible for any legal consequences of installing or running this game.</li>
        <li>Neither the original team nor the team that customised this script is liable for any damage.</li>
        <li>Review the code yourself before deploying it on a public server.</li>
        <li>You may not remove copyright notices or claim the script as your own.</li>
      </ul>
    </div>
  </div>
</div>

<div class="tz-actions">
  <span class="tz-actions-note">6 short steps &middot; about 3 minutes</span>
  <a class="btn" href="?s=1">Next</a>
</div>
