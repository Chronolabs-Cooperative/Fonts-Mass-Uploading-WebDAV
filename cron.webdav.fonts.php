<?php

    require __DIR__ . DIRECTORY_SEPARATOR . 'mainfile.php';
    
    mkdir($path = API_FONTS_SOURCES . DS . date('Y-m-d H:i:s'), 0777, true);
    
    foreach(getCompleteFontsListAsArray(API_FONTS_WEBDAV) as $type => $md5s)
    {
        foreach($md5s as $md5 => $file) {
            if (filemtime($file) < time() - (mt_rand(2, 4)*mt_rand(30,60))) {
                mkdir($path . DS . $md5, 0777, true);
                copy($file, $path . DS . $md5 . DS . basename($file));
		echo "\nCopied File: " . basename($file);
                unlink($file);
                if (dirname($file)!=API_FONTS_WEBDAV)
                    rmdir(dirname($file));
            }
        }
    }
    
    rmdir($path);
    
?>
