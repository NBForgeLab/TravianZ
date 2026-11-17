<?php

#################################################################################
##              -= YOU MAY NOT REMOVE OR CHANGE THIS NOTICE =-                 ##
## --------------------------------------------------------------------------- ##
##  Filename       : accounts.tpl                                              ##
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

if (isset($_GET['err']) && $_GET['err'] == 1) {
    echo '<div class="tz-alert tz-alert-error">At least the Multihunter and Support passwords are required.</div>';
}
if (isset($_GET['err']) && $_GET['err'] == 2) {
    echo '<div class="tz-alert tz-alert-error">Natars is reserved. Choose a different admin username.</div>';
}
if (isset($_GET['err']) && $_GET['err'] == 3) {
    echo '<div class="tz-alert tz-alert-error">Invalid security token (CSRF). Please refresh and try again.</div>';
}
?>
<form action="include/accounts.php" method="post" id="dataform">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['tz_csrf'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
  <div class="grid-2">
    <div class="card">
      <h3 class="tz-card-title">Multihunter account</h3>
      <div class="tz-stack">
        <div><label for="mhpw_name">Name</label><input class="input" type="text" id="mhpw_name" value="Multihunter" disabled></div>
        <div>
          <label for="mhpw">Password</label>
          <div class="tz-password-wrap">
            <input class="input" type="password" name="mhpw" id="mhpw" required autocomplete="new-password">
            <button type="button" class="tz-pw-toggle" aria-label="Show password" title="Show password" aria-pressed="false">
              <svg class="tz-pw-icon tz-pw-show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg class="tz-pw-icon tz-pw-hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
            </button>
          </div>
        </div>
      </div>
    </div>
    <div class="card">
      <h3 class="tz-card-title">Support account</h3>
      <div class="tz-stack">
        <div><label for="spw_name">Name</label><input class="input" type="text" id="spw_name" value="Support" disabled></div>
        <div>
          <label for="spw">Password</label>
          <div class="tz-password-wrap">
            <input class="input" type="password" name="spw" id="spw" required autocomplete="new-password">
            <button type="button" class="tz-pw-toggle" aria-label="Show password" title="Show password" aria-pressed="false">
              <svg class="tz-pw-icon tz-pw-show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg class="tz-pw-icon tz-pw-hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <h3 class="tz-card-title">Admin account</h3>
    <div class="grid-2">
      <div><label for="aname">Admin name</label><input class="input" name="aname" id="aname"></div>
      <div><label for="aemail">Admin email</label><input class="input" name="aemail" id="aemail" type="email"></div>
      <div>
        <label for="apass">Admin password</label>
        <div class="tz-password-wrap">
          <input class="input" name="apass" id="apass" type="password" autocomplete="new-password">
          <button type="button" class="tz-pw-toggle" aria-label="Show password" title="Show password" aria-pressed="false">
            <svg class="tz-pw-icon tz-pw-show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            <svg class="tz-pw-icon tz-pw-hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
          </button>
        </div>
      </div>
      <div><label for="atribe">Tribe</label>
        <select class="input" name="atribe" id="atribe">
          <option value="1" selected>Romans</option>
          <option value="2">Teutons</option>
          <option value="3">Gauls</option>
          <option value="6">Huns</option>
          <option value="7">Egyptians</option>
          <option value="8">Spartans</option>
          <option value="9">Vikings</option>
        </select>
      </div>
      <div><label for="admin_rank">Show in stats</label>
        <select class="input" name="admin_rank" id="admin_rank">
          <option value="true">true</option>
          <option value="false" selected>false</option>
        </select>
      </div>
      <div><label for="admin_support_msgs">Include Support Msgs</label>
        <select class="input" name="admin_support_msgs" id="admin_support_msgs">
          <option value="true" selected>true</option>
          <option value="false">false</option>
        </select>
      </div>
      <div><label for="admin_raidable">Allow Raidable</label>
        <select class="input" name="admin_raidable" id="admin_raidable">
          <option value="true" selected>true</option>
          <option value="false">false</option>
        </select>
      </div>
    </div>
    <p class="tz-note tz-mt-3">Leave the admin fields empty to skip admin creation.</p>
    <div class="tz-actions">
      <span class="tz-actions-note">Step 5 of 6 &middot; choose strong, unique passwords</span>
      <button class="btn" type="submit">Create accounts</button>
    </div>
  </div>
</form>
