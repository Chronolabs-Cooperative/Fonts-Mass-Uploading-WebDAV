<?php

    require __DIR__ . DIRECTORY_SEPARATOR . 'mainfile.php';
    
    
    $files = getFileListAsArray(__DIR__);
    shuffle($files);
    shuffle($files);
    shuffle($files);
    shuffle($files);
    $uncompleted = $shfiles = array();
    foreach($files as $file) {
        $parts = explode(".", basename($file));
        if ($parts[0] == 'cron' && $parts[1] == 'stored')
            $uncompleted[$parts[2]] = $file;
        elseif ($parts[0] == 'cron' && $parts[1] == 'staging') {
            $lines = file($file);
            foreach($lines as $lnum => $line) {
                if (strpos($line, 'cron.stored.')) {
                    $parts = explode("cron.stored.", basename($file));
                    $parts = explode(".", $parts[1]);
                    $shfiles[$parts[0]] = $file;
                }
            }
        }
            
    }
    
    if (count($uncompleted) > 0)
    {
        foreach($uncompleted as $fontid => $file)
            if (isset($shfiles[$fontid]) && !empty($shfiles[$fontid]))
                unset($uncompleted[$fontid]);
    }
    
    if (count($uncompleted) > 0)
    {
        $paths = getDirListAsArray(API_FONTS_STAGING);
        foreach($uncompleted as $fontid => $phpfile) {
            if ($font = $GLOBALS["APIDB"]->fetchArray($GLOBALS["APIDB"]->queryF("SELECT * FROM `fonts` WHERE `id` = '$fontid'"))) {
                $found = false;
                foreach($paths as $path) {
                    $files = getFileListAsArray(API_FONTS_STAGING . DS . $path);
                    shuffle($files);
                    shuffle($files);
                    shuffle($files);
                    shuffle($files);
                    if ($found == false)
                    foreach($files as $fille) {
                        if (strpos('  '.basename($fille), $font['filename'])>0 && $found == false) {
                            if (!is_file(__DIR__ . DS . "cron.staging.$fontid.sh"))
                                $sh = array(0 => 'unlink "' . __DIR__ . DS . "cron.staging.$fontid.sh\"", 1 => 'wait ' . mt_rand(2 * mt_rand(20, 60), 6 * mt_rand(20, 60)));
                            else
                                $sh = cleanWhitespaces(file(__DIR__ . DS . "cron.staging.$fontid.sh"));
                            foreach(getFontsListAsArray(API_FONTS_STAGING . DS . $path) as $filee) {
                                $sh[] = sprintf(API_FONTS_STAGER, "Font Type: *." . $filee['type'] . " ~ " . $font['name'] . ' ~ ' . $filee['file'], API_FONTS_STAGING . DS . $path . DS . $file['file'], $file['type'] . DS . $font['alpha'] . DS . $font['beta'] . DS . $font['charley'] . DS . $font['delta'] . DS . $filee['file']);
                            }
                            $sh[] = sprintf(API_FONTS_STAGER, "Font Archive: *.zip ~ " . $font['name'] . " ~ " . $font['filename'] . ".zip", API_FONTS_STAGING . DS . $path . DS . "" . $font['filename'] . ".zip", "zip" . DS . $font['alpha'] . DS . $font['beta'] . DS . $font['charley'] . DS . $font['delta'] . DS . "" . $font['filename'] . ".zip");
                            $sh[] = sprintf(API_FONTS_STAGER, "Font CSS: *.css ~ " . $font['name'] . " ~ " . $font['filename'] . ".css", API_FONTS_STAGING . DS . $path . DS . "" . $font['filename'] . ".css", "css" . DS . $font['alpha'] . DS . $font['beta'] . DS . $font['charley'] . DS . $font['delta'] . DS . "" . $font['filename'] . ".css");
                            $sh[] = sprintf(API_FONTS_STAGER, "Font Naming Card: *.naming.png ~ " . $font['name'] . " ~ " . $font['filename'] . ".naming.png", API_FONTS_STAGING . DS . $path . DS . "" . $font['filename'] . ".naming.png", "naming.png" . DS . $font['alpha'] . DS . $font['beta'] . DS . $font['charley'] . DS . $font['delta'] . DS . "" . $font['filename'] . ".naming.png");
                            $sh[] = sprintf(API_FONTS_STAGER, "Font Preview Card: *.preview.png ~ " . $font['name'] . " ~ " . $font['filename'] . ".preview.png", API_FONTS_STAGING . DS . $path . DS . "" . $font['filename'] . ".preview.png", "preview.png" . DS . $font['alpha'] . DS . $font['beta'] . DS . $font['charley'] . DS . $font['delta'] . DS . "" . $font['filename'] . ".preview.png");
                            foreach(getFileListAsArray(API_FONTS_STAGING . DS . $path . DS . 'glyphs') as $filee) {
                                $sh[] = sprintf(API_FONTS_STAGER, "Font Glyph Card's: *.png ~ " . $font['name'] . " ~ glyphs/".$filee, API_FONTS_STAGING . DS . $path . DS . "glyphs/$filee", "glyphs" . DS . $font['alpha'] . DS . $font['beta'] . DS . $font['charley'] . DS . $font['delta'] . DS . $font['filename'] . DS . $filee);
                            }
                            $sh[] = "rm -Rf \"".API_FONTS_STAGING . DS . $path."\"";
                            $sh[] = "/usr/bin/php -q '".__DIR__ . DS . 'cron.stored.'.$fontid.'.php\'';
                            $sh[] = 'wait ' . mt_rand(2 * mt_rand(20, 60), 6 * mt_rand(20, 60));
                            if (file_put_contents(__DIR__ . DS . "cron.staging.$fontid.sh", implode("\n", $sh)))
                                echo "\nRecreating: cron.staging.$fontid.sh ~ " . $font['name'] . "\n";
                            $found = true;
                        }
                    }
                }
                if ($found == false) {
                    echo "\nFailed to Find remenents of Font: " . $font['name'] . "\n";
                    @include __DIR__ . DS . $phpfile;
                }
            }
        }
    }
    
?>