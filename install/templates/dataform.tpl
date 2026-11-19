<?php

#################################################################################
##              -= YOU MAY NOT REMOVE OR CHANGE THIS NOTICE =-                 ##
## --------------------------------------------------------------------------- ##
##  Filename       : dataform.tpl                                              ##
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

@include_once __DIR__ . '/../../GameEngine/config.php';
if (isset($_GET['c']) && $_GET['c'] == 1) {
    echo '<div class="tz-alert tz-alert-error"><strong>Error importing database.</strong> Check the configuration and database credentials.</div>';
}
if (isset($_GET['err']) && $_GET['err'] == 1) {
    $safePrefix = htmlspecialchars(defined('TB_PREFIX') ? (string)TB_PREFIX : 'tz_', ENT_QUOTES, 'UTF-8');
    $safeDb = htmlspecialchars(defined('SQL_DB') ? (string)SQL_DB : '', ENT_QUOTES, 'UTF-8');
    echo '<div class="tz-alert tz-alert-error">Existing structure found! Please remove tables with prefix <b>' . $safePrefix . '</b> from database <b>' . $safeDb . '</b>.</div>';
}
?>
<form action="process.php" method="post" id="dataform">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['tz_csrf'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
  <input type="hidden" name="substruc" value="1">
  <div class="card">
    <h3 class="tz-card-title">Create database structure</h3>
    <div class="tz-alert tz-alert-warning">This imports the schema and can take a while. Do not close the page until the next step loads.</div>
    <div class="tz-actions">
      <span class="tz-actions-note">Step 3 of 6 &middot; uses the credentials from the previous step</span>
      <button class="btn" id="Submit" type="submit" onclick="return proceed()" data-processing="Importing...">Create database</button>
    </div>
  </div>
</form>
