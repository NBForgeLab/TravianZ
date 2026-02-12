<?php

include("../Village.php");
if(isset($_POST['wwname']) && !empty($_POST['wwname']) && $village->natar){
    $database->submitWWname($village->wid,$_POST['wwname']);
    header("Location: ../../build.php?id=99&n");
}else{
    header("Location: ../../dorf2.php");
}


?>