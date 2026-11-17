<?php
$files = glob('C:/Users/batah/Documents/GitHub/TravianZ/GameEngine/Automation/*.php');
foreach ($files as $file) {
    $content = file_get_contents($file);
    // Replace: global $database;
    // With: $database = $this->database;
    // And handle methods
    
    // This is simple replacement, it might not cover all cases.
    $newContent = str_replace('global $database;', '$database = $this->database;', $content);
    
    // Also need to handle cases where it is global $database, $another_global;
    $newContent = preg_replace('/global\s+\$database,\s*([^;]+);/', '$database = $this->database; global $1;', $newContent);
    $newContent = preg_replace('/global\s+([^,]+),\s*\$database;/', 'global $1; $database = $this->database;', $newContent);
    
    file_put_contents($file, $newContent);
    echo "Refactored $file\n";
}
?>