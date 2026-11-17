<?php
$files = glob('C:/Users/batah/Documents/GitHub/TravianZ/GameEngine/Automation/*.php');
foreach ($files as $file) {
    if (basename($file) == 'index.php') continue;
    $content = file_get_contents($file);
    
    // Replace "global $database;" with "$database = $this->database;"
    $content = str_replace('global $database;', '$database = $this->database;', $content);
    
    $content = preg_replace('/global\s+\$database\s*;/i', '$database = $this->database;', $content);
    
    file_put_contents($file, $content);
}
?>