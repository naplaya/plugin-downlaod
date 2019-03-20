<?php
$error = 0;
$f = file_put_contents("plugin_to_install.zip", fopen($_POST['install'], 'r'), LOCK_EX);

if($f === false){
    $error = -1;//file couldn't be downloaded
}

$zip = new ZipArchive;
$res = $zip->open('plugin_to_install.zip');
if ($res === TRUE) {
    //try to extract zip to plugins folder
    if($zip->extractTo(HTML_PATH_PLUGINS)){
        //check each folder in root of zip if it contains plugins.php
        for($i = 0; $i < $zip->numFiles; $i++) {   
            $path = HTML_PATH_PLUGINS.'/'.$zip->getNameIndex($i).'plugin.php';
            if(file_exists($path)){
                //get plugin.php to activate it
                $content_plugin_file = file_get_contents ($path);
                
                //regex to find class name
                $re = '/(class) ([a-zA-Z-_])*( extends Plugin)/m';
                preg_match($re, $content_plugin_file, $matches);
                
                // Class name of the plugin
                $className = explode(" ", $matches[0]); 
                
                //activate found class
                if(!activatePlugin(trim($className[1]))) $error = -3;
            } 
        }
    } else {
        $error = -4;//zip couldn't be extracted
    }
    $zip->close();
} else {
    $error = -2;//zip couldn't be opened
}

switch($error){
    case -1:
        die("Couldn't download file (-1)");
        break;
    case -2:
        die("Couldn't open zip archive (-2)");
        break;
    case -3:
        die("Couldn't activate Plugin, please do it manualy (-3)");
        break;
    case -4:
        die("Couldn't extract plugin to ".HTML_PATH_PLUGINS.' (-4)');
        break;
    default:
        die("An unexpected error happend (-9)");
        break;
}
