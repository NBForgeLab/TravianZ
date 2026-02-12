<?php

//////////////     edited by Pyrrhic of A.G.B.   /////////////////////////


include("Templates/Plus/pmenu.tpl");


	echo " <br /><div align=center><h2>Get <font color=#71D000>P</font><font color=#FF6F0F>l</font><font  color=#71D000>u</font><font color=#FF6F0F>s</font></h2>";
	echo " <br /> To enter you login then select the plus site. <br />You will be redirected to the plus site.";
	echo " <br /> <i>The plus system will be monitored. Account name, site plus on ip, <br /> and points given are logged,<br /> if caught cheating account will be banned.</i> <br /> <br /> </div>";

if(!$_POST['plus']){
echo <<<EOT
<form method="POST">
<table border="0" width="300" align="center">
<tr><td><b>$session->username</b></td></tr>
<tr><td align=center><b>Select Reward: </b>
		<select name="reward">
			<option value="error" selected>Select reward...</option>
			<option value="p_plus">VIP Account</option>
			<option value="p_b1">Lumber</option>
			<option value="p_b2">Clay</option>
			<option value="p_b3">Iron</option>
			<option value="p_b4">Crop </option>";
		</select><br/><br/>
  <input type="submit" name="plus" value="Get Now"></td></tr>
</table>
</form>
<br /> <br /> 

EOT;
}else{

    $account = preg_replace("/[^a-zA-Z0-9_\\-]/", "", $_POST['username']);
    $reward = preg_replace("/[^a-zA-Z0-9_\\-]/", "", $_POST['reward']);
    $valid=TRUE;


    if($reward == ""){
        echo "<b>ERROR:</b><br />";
        echo "Please select a reward.";
        echo "<br /><br /><input type=\"button\" value=\"Back\" data-go-back=\"1\">";
        $valid=FALSE;
    }

    if(!$valid) break;
    $valid=TRUE; 
/////////////////////////////////////////////////////////
    $plusTime = 604800; // 7 days
    $time = time();
    $giveplus = ($time + $plusTime);
    $rowsAcc = $database->query_return("SELECT Count(*) as Total FROM ".TB_PREFIX."users WHERE id = ".(int)$session->uid);
    if(((int)($rowsAcc[0]['Total'] ?? 0)) <= 0){
        echo "<b>ERROR:</b><br />";
        echo "The account name you entered does not exist.";
        echo "<br /><br /><input type=\"button\" value=\"Back\" data-go-back=\"1\">";
        $valid=FALSE;
    }
    if(!$valid) break;
    $valid=TRUE;

    $rowsPlus = $database->query_return("SELECT * FROM ".TB_PREFIX."users WHERE id = ".(int)$session->uid." LIMIT 1");
    $pluss = isset($rowsPlus[0]) ? $rowsPlus[0] : [];

    switch($reward){
      case 'p_plus':
        $key='plus';
        $gldz='10';
        $url='URL=./plus.php?id=3';
        break;
      case 'p_b1':
        $key ='b1';
        $gldz='5';
        $url='URL=./plus.php?id=3';
        break;
      case 'p_b2':
        $key ='b2';
        $gldz='5';
        $url='URL=./plus.php?id=3';
        break;
      case 'p_b3':
        $key ='b3';
        $gldz='5';
        $url='URL=./plus.php?id=3';
        break;
      case 'p_b4':
        $key ='b4';
        $gldz='5';
        $url='URL=./plus.php?id=3';
        break;

      default:
echo' Please select the option you wish to activate or extend.<br>';
        $valid=FALSE;
        break;
    }
    if(!$valid) break;
    $valid=TRUE;
    


    if(!empty($pluss)){ 
        if($time > (int)$pluss[$key] ){
            $database->query("UPDATE ".TB_PREFIX."users SET `{$key}`= `{$key}` + (".$time." + ".$plusTime."),  `gold` =  `gold` - {$gldz}   WHERE id = ".(int)$session->uid);
            echo "<META HTTP-EQUIV=Refresh CONTENT=\"2; {$url}\" ><br /><br /><div align=center><font color=green size=4><b> Your Status has been updated!</b></font></div>";
       }else
        if($time < (int)$pluss[$key]){
            $database->query("UPDATE ".TB_PREFIX."users SET `{$key}`= `{$key}` + ".$plusTime.",  `gold` =  `gold` - {$gldz}  WHERE id = ".(int)$session->uid);
            echo "<META HTTP-EQUIV=Refresh CONTENT=\"2; {$url}\" ><br /><br /><div align=center><font color=green size=4><b> Your Status has been updated!</b></font></div>";
       
       }
    }
else{
        $database->query("INSERT INTO ".TB_PREFIX."users (`username`,`{$key}`, `gold`) VALUES ('".$session->username."', (".$time." + ".$plusTime."), `gold` - {$gldz})");
        echo "<META HTTP-EQUIV=Refresh CONTENT=\"3; {$url})\" ><br /><br /><div align=center><font color=green size=4><b> Your Status has been updated!</b></font></div>";
	 }   
}


?>
</div>
