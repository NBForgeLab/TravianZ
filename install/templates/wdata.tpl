<?php

#################################################################################
##              -= YOU MAY NOT REMOVE OR CHANGE THIS NOTICE =-                 ##
## --------------------------------------------------------------------------- ##
##  Filename       : wdata.tpl                                                 ##
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
if (isset($_GET['c']) && $_GET['c'] == '1') {
    echo '<div class="tz-alert tz-alert-error">Error creating wdata. Check configuration or file permissions.</div>';
}
if (isset($_GET['err']) && $_GET['err'] == '1') {
    echo '<div class="tz-alert tz-alert-error">Existing World Data found! Empty tables <i>' . TB_PREFIX . 'odata, ' . TB_PREFIX . 'units, ' . TB_PREFIX . 'vdata, ' . TB_PREFIX . 'wdata</i>.</div>';
}
$autoStartCroppers = isset($_GET['startCroppers']) && $_GET['startCroppers'] === '1';
?>
<form action="process.php" method="post" id="dataform">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['tz_csrf'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
  <input type="hidden" name="subwdata" value="1" />
  <div class="card">
    <h3 class="tz-card-title">Create world data</h3>
    <p class="tz-note tz-mb-3">Generates the map, oases and cropper villages. This can take a few minutes &mdash; keep this tab open.</p>

    <div id="submitWrap" class="<?php echo $autoStartCroppers ? 'is-hidden' : ''; ?>">
      <div class="tz-actions">
        <span class="tz-actions-note">Step 4 of 6 &middot; progress is shown below</span>
        <button class="btn" id="Submit" type="submit" onclick="return proceed()" data-processing="Generating...">Create world</button>
      </div>
    </div>

    <div id="progressBox" class="<?php echo $autoStartCroppers ? '' : 'is-hidden'; ?>">
      <div class="tz-status-text">Building croppers&hellip;</div>
      <div class="tz-progress-track tz-progress-wdata"><div id="pbar"></div></div>
      <div id="pinfo" class="tz-note tz-mt-2">Starting&hellip;</div>
      <pre id="plog" class="tz-code tz-log"></pre>
      <div id="autoNext" class="tz-countdown">Proceeding to next step in <b id="cd">3</b>&hellip;</div>
    </div>
  </div>
</form>
<script>
(function () {
  var NEXT_URL = 'index.php?s=4'; var COUNTDOWN_SECS = 3; var finished = false;
  function startCountdown(){
    var box=document.getElementById('autoNext'),cd=document.getElementById('cd'),left=COUNTDOWN_SECS;
    box.classList.remove('is-hidden');box.classList.add('is-visible');cd.textContent=left;
    var t=setInterval(function(){left--;cd.textContent=left;if(left<=0){clearInterval(t);window.location.href=NEXT_URL}},1000)
  }
  function startCroppersBuild(){
    var box=document.getElementById('progressBox'),pbar=document.getElementById('pbar'),pinfo=document.getElementById('pinfo'),plog=document.getElementById('plog'),submitWrap=document.getElementById('submitWrap');
    if(submitWrap){submitWrap.classList.add('is-hidden');}
    box.classList.remove('is-hidden');
    if(!('EventSource'in window)){plog.textContent+="Browser does not support Server-Sent Events\n";return}
    var MAX=3,retries=0,es=new EventSource('ajax_croppers.php');
    es.onopen=function(){if(!finished&&retries>0){plog.textContent+="Reconnected\n"}};
    es.onmessage=function(e){
      if(!e.data||e.data.charCodeAt(0)!==123)return;
      try{
        var d=JSON.parse(e.data),pct=d.pct|0,done=d.done|0,total=d.total|0;
        if(finished)return;
        retries=0;
        pbar.style.width=pct+'%';
        pinfo.textContent=done+' / '+total+' ('+pct+'%)';
        if(d.msg){plog.textContent+=d.msg+"\n";plog.scrollTop=plog.scrollHeight}
        if(pct>=100){finished=true;plog.textContent+="Completed!\n";es.close();startCountdown()}
        if(d.error){finished=true;plog.textContent+="Error: "+(d.msg||"unknown")+"\n";es.close();startCountdown()}
      }catch(err){}
    };
    es.onerror=function(){
      if(finished)return;
      retries++;
      plog.textContent+="Warning: connection hiccup ("+retries+"/"+MAX+")\n";
      if(retries>=MAX){finished=true;plog.textContent+="Too many failures\n";es.close();startCountdown()}
    }
  }
  document.addEventListener('DOMContentLoaded',function(){<?php if($autoStartCroppers){echo 'startCroppersBuild();';} ?>});
})();
</script>
