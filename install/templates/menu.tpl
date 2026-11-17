<?php

#################################################################################
##              -= YOU MAY NOT REMOVE OR CHANGE THIS NOTICE =-                 ##
## --------------------------------------------------------------------------- ##
##  Filename       : menu.tpl                                                  ##
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

$cur = $tzStep;
// Only the first two steps are safe to revisit: later steps run destructive
// database operations when submitted again.
if (!function_exists('tz_step_is_revisitable')) {
    function tz_step_is_revisitable($index, $cur) {
        return $index < $cur && $index <= 1 && $cur < 5;
    }
}
?>
<ol class="tz-steps-list">
<?php foreach ($tzSteps as $i => $name):
    $state = $i < $cur ? 'is-done' : ($i == $cur ? 'is-current' : '');
    $linkable = tz_step_is_revisitable($i, $cur);
?>
  <li class="tz-step <?php echo $state; ?>"<?php echo $i == $cur ? ' aria-current="step"' : ''; ?>>
<?php if ($linkable): ?>
    <a class="tz-step-inner" href="?s=<?php echo $i; ?>">
<?php else: ?>
    <span class="tz-step-inner">
<?php endif; ?>
      <span class="tz-step-num"><?php echo $i < $cur ? tz_install_icon('check') : ($i + 1); ?></span>
      <span class="tz-step-label"><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></span>
<?php if ($linkable): ?>
    </a>
<?php else: ?>
    </span>
<?php endif; ?>
  </li>
<?php endforeach; ?>
</ol>
