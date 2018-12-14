<?php

    require __DIR__ . DIRECTORY_SEPARATOR . 'mainfile.php';
    
    mkdir($path = API_FONTS_SOURCES . DS . date('Y-m-d H:i:s'), 0777, true);
    
    foreach(getCompletePacksListAsArray(API_FONTS_WEBDAV) as $type => $md5s)
    {
        foreach($md5s as $md5 => $file) {
            if (filemtime($file) < time() - (mt_rand(2, 4)*mt_rand(30,60))) {
                mkdir($path . DS . $type . DS . $md5, 0777, true);
		        chdir($path . DS . $type . DS . $md5);
                $parts = explode(".", $file);
                $packtype = strtolower($parts[count($parts)-1]);
                $cmds = getExtractionShellExec();
                copy($file, $path . DS . $type . DS . $md5 . DS . basename($file));
                if (is_file($path . DS . $type . DS . $md5 . DS . basename($file))) {
                    unlink($file);
                    echo shell_exec((substr($cmds[$packtype],0,1)!="#"?DIRECTORY_SEPARATOR . "usr" . DIRECTORY_SEPARATOR . "bin" . DIRECTORY_SEPARATOR:'') . str_replace('%path', '.', str_replace('%pack', $path . DS . $type . DS . $md5 . DS . basename($file), (substr($cmds[$packtype],0,1)!="#"?$cmds[$packtype]:substr($cmds[$packtype],1)))));
                    unlink($path . DS . $type . DS . $md5 . DS . basename($file));
                    while(count(getCompletePacksListAsArray($path . DS . $type . DS . $md5))) {
                        foreach(getCompletePacksListAsArray($path . DS . $type . DS . $md5) as $types => $md5se)
                        {
                            foreach($md5se as $md5e => $filee) {
                                $parts = explode(".", $filee);
                                $packtype = $parts[count($parts)-1];
                                $cmds = getExtractionShellExec();
                                copy($filee, $path . DS . $type . DS . $md5 . DS . basename($filee));
                                echo shell_exec((substr($cmds[$packtype],0,1)!="#"?DIRECTORY_SEPARATOR . "usr" . DIRECTORY_SEPARATOR . "bin" . DIRECTORY_SEPARATOR:'') . str_replace('%path', '.', str_replace('%pack', $path . DS . $type . DS . $md5 . DS . basename($filee), (substr($cmds[$packtype],0,1)!="#"?$cmds[$packtype]:substr($cmds[$packtype],1)))));
                                unlink($filee);
                                unlink($path . DS . $type . DS . $md5 . DS . basename($filee));
                            }
                        }
                    }
                }
                if (dirname($file)!=API_FONTS_WEBDAV)
                    rmdir(dirname($file));
            }
        }
    }
    
    rmdir($path);
    
?>
