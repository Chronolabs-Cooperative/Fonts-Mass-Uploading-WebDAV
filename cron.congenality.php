<?php

//    sleep(mt_rand(10, mt_rand(1, 6) * 20));
    
    use FontLib\Font;
    require_once __DIR__.'/class/FontLib/Autoloader.php';

    require __DIR__ . DIRECTORY_SEPARATOR . 'mainfile.php';
    require __DIR__ . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'xcp.class.php';

    ini_set('memory_limit', '196M');
    
    $fixing = array('woff','eot','ttf','otf','pt3','gsf','ptb','gai','t42','pfa','pf3','std');
    
    $result = $GLOBALS['APIDB']->queryF("SELECT `id` FROM `fonts` WHERE `stored` > 0 AND `tagged` > '0' and `passed` = '0' ORDER BY RAND() LIMIT 13");
    
    while($fontidz = $GLOBALS['APIDB']->fetchArray($result))
    {
        $fontid = $fontidz['id'];
        $missing = array();
        $found = array();
        $html = array();
        $results = $GLOBALS['APIDB']->queryF("SELECT * FROM `files` WHERE `fontid` = " . $fontid);
        while($file = $GLOBALS['APIDB']->fetchArray($result))
        {
            if (!isset($html[$file['path']]))
                $html[$file['path']] = file_get_contents(sprintf(API_FONTS_SVNRAW, $file['path']));
            if (!strpos($html[$file['path']], $file['filename']))
            {
                $missing[$file['extension']][] = $file['path'].'/'.$file['filename'];
            } else {
                $found[$file['extension']][] = $file['path'].'/'.$file['filename'];
            }
        }
        if (count($missing) > 0 && count($found) > 0) {
            mkdir($path = API_FONTS_STAGING . DS . md5_file(time() . DS . $fontid), 0777, true);
            foreach($fixing as $ext)
            {
                if (isset($found[$ext]))
                    foreach($found[$ext] as $fontfile) {
                        $extension = $ext;
                        $paths = dirname($fontfile);
                        file_put_contents($filefont = $path . DS . ($filename = basename($fontfile)), file_get_contents(sprintf(API_FONTS_SVNRAW, $fontfile)));
                        continue;
                        continue;
                    }
            }
            if (is_file($filefont)) {
                chdir($path);
                exec(sprintf(DIRECTORY_SEPARATOR . "usr" . DIRECTORY_SEPARATOR . "bin" . DIRECTORY_SEPARATOR . "fontforge -script \"%s\" \"%s\"", __DIR__ . DS . 'include' . DS . 'data' . DS . 'convert-fonts-distribution.pe', $filefont));
                $font = Font::load($path . DS . str_replace(".".$extension, ".otf", strtolower(basename($filefont))));
                if (is_object($font) && !empty($font)) {
                    $filename = strtolower(getOnlyAlpha(htmlspecialchars($font->getFontFullName() . " " . $font->getFontSubfamily()), '-'));
                    list($count) = $GLOBALS['APIDB']->fetchRow($GLOBALS['APIDB']->queryF("SELECT COUNT(*) FROM `fonts` WHERE `filename` LIKE '$filename'"));
                    if ($count>0)
                        $filename .= " (" . $count . ")";
                    
                    $css = array();
                    foreach(getFontsListAsArray($path) as $file) {
                        rename($path . DS . $file['file'], $path . DS . $filename . "." . $file['type']);
                        $css[$file['type']] = $filename . "." . $file['type'];
                    }
                        
                    require_once __DIR__ . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'WideImage' . DIRECTORY_SEPARATOR . 'WideImage.php';
                    if (strlen($font->getFontFullName())<=9)
                    {
                        $img = WideImage::load(__DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'font-title-small.png');
                    } elseif (strlen($font->getFontFullName())<=12)
                    {
                        $img = WideImage::load(__DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'font-title-medium.png');
                    }elseif (strlen($font->getFontFullName())<=21)
                    {
                        $img = WideImage::load(__DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'font-title-large.png');
                    } else
                    {
                        $img = WideImage::load(__DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'font-title-extra.png');
                    }
                    $height = $img->getHeight();
                    $point = $height * (32/99);
                    $canvas = $img->getCanvas();
                    $canvas->useFont($path . DS . $filename . ".ttf", $point, $img->allocateColor(0, 0, 0));
                    $canvas->writeText('center', 'center', $font->getFontFullName());
                    $img->saveToFile($path . DS . $filename.".naming.png");
                    unset($img);
                    
                    $img = WideImage::load(__DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'font-preview.png');
                    if ($state == 'jpg')
                    {
                        $bg = $img->allocateColor(255, 255, 255);
                        $img->fill(0, 0, $bg);
                    }
                    $height = $img->getHeight();
                    $lsize = 66;
                    $ssize = 14;
                    $step = mt_rand(8,11);
                    $canvas = $img->getCanvas();
                    $i=0;
                    while($i<$height)
                    {
                        $canvas->useFont($path . DS . $filename . ".ttf", $point = $ssize + ($lsize - (($lsize  * ($i/$height)))), $img->allocateColor(0, 0, 0));
                        $canvas->writeText(19, $i, getFontPreviewText());
                        $i=$i+$point + $step;
                    }
                    $img->saveToFile($path . DS . $filename.".preview.png");
                    unset($img);
                    
                    $glyphsbytes = 0;
                    $glyphs = array();
                    mkdir($path . DS . 'glyphs', 0777, true);
                    $resultb = $GLOBALS['APIDB']->queryF("SELECT * FROM `glyphs` WHERE `sourceid` = '" . $source['id'] . "' AND `unicode` NOT LIKE ''");
                    while($glyph = $GLOBALS['APIDB']->fetchArray($resultb)) 
                    {    
                        $glyphs[] = array('name' => $glyph['name'], 'unicode' => $glyph['unicode']);
                        $img = WideImage::load(__DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'font-glyph.png');
                        if ($state == 'jpg')
                        {
                            $bg = $img->allocateColor(255, 255, 255);
                            $img->fill(0, 0, $bg);
                        }
                        $height = $img->getHeight();
                        $canvas = $img->getCanvas();
                        $canvas->useFont($path . DS . $filename . ".ttf", $height-37, $img->allocateColor(0, 0, 0));
                        $canvas->writeText("center", "center", "&#".hexdec($glyph['unicode']).";");
                        $canvas->useFont(__DIR__ . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'titles.ttf', 10, $img->allocateColor(0, 0, 0));
                        $canvas->writeText('center', 'top + 3', '&amp;#'.hexdec($glyph['unicode']).';');
                        $img->saveToFile($path . DS . 'glyphs' . DS . $glyph['unicode'] . ".png");
                        unset($img);
                        $glyphsbytes += filesize($path . DS . 'glyphs' . DS . $glyph['unicode'] . ".png");
                    }
                    
                    $pp = strtolower(str_replace(array(" ", "-", "_", "(", ")", "*", "+", "=", "'", '"'), "", htmlspecialchars($font->getFontFullname())));
                    $parts = explode(" ", strtolower(str_replace(array("-", "_", "(", ")", "*", "+", "=", "'", '"'), "", htmlspecialchars($font->getFontFullname()))));
                    $alpha = substr($pp, 0, 1);
                    $beta = substr($pp, 0, 2);
                    $charley = substr($pp, 0, 3);
                    $delta = $parts[0];
                    
                    $csstxt = "@font-face {\n";
                    $csstxt .= "\tfont-family:\t\t\t'".getOnlyAlpha(htmlspecialchars($font->getFontFullName() . " " . $font->getFontSubfamily()), '-')."';\n";
                    $csstxt .= "\tsrc:\t\t\turl('./".$css['eot']."') format('eot');\n";
                    $csstxt .= "\tsrc:\t\t\tlocal('||'),\n";
                    $csskeys = array_keys($css);
                    foreach($css as $type => $fontfile)
                        if ($type!='eot' && $type != $csskeys[count($csskeys)-1])
                            $csstxt .= "\t\t\t\turl('./".$fontfile."') format('".$type."'),\n";
                        elseif ($type!='eot' && $type == $csskeys[count($csskeys)-1])
                            $csstxt .= "\t\t\t\turl('./".$fontfile."') format('".$type."');\n";
                    $csstxt .= "}\n\n";
                    $csstxt .= "@font-face {\n";
                    $csstxt .= "\tfont-family:\t\t\t'".getOnlyAlpha(htmlspecialchars(strtolower($font->getFontFullName() . " " . $font->getFontSubfamily())), '-')."';\n";
                    $csstxt .= "\tsrc:\t\t\turl('./".$css['eot']."') format('eot');\n";
                    $csstxt .= "\tsrc:\t\t\tlocal('||'),\n";
                    $csskeys = array_keys($css);
                    foreach($css as $type => $fontfile)
                        if ($type!='eot' && $type != $csskeys[count($csskeys)-1])
                            $csstxt .= "\t\t\t\turl('./".$fontfile."') format('".$type."'),\n";
                        elseif ($type!='eot' && $type == $csskeys[count($csskeys)-1])
                            $csstxt .= "\t\t\t\turl('./".$fontfile."') format('".$type."');\n";
                    $csstxt .= "}\n\n";
                    $csstxt .= "@font-face {\n";
                    $csstxt .= "\tfont-family:\t\t\t'" . $barcode . "';\n";
                    $csstxt .= "\tsrc:\t\t\turl('./".$css['eot']."') format('eot');\n";
                    $csstxt .= "\tsrc:\t\t\tlocal('||'),\n";
                    $csskeys = array_keys($css);
                    foreach($css as $type => $fontfile)
                        if ($type!='eot' && $type != $csskeys[count($csskeys)-1])
                            $csstxt .= "\t\t\t\turl('./".$fontfile."') format('".$type."'),\n";
                        elseif ($type!='eot' && $type == $csskeys[count($csskeys)-1])
                            $csstxt .= "\t\t\t\turl('./".$fontfile."') format('".$type."');\n";
                    $csstxt .= "}\n\n";
                    file_put_contents($path . DS . 'style.css', $csstxt);
                                        
                    if (strpos($paths, $delta)) {
                                    
                        $csstxt = "@font-face {\n";
                        $csstxt .= "\tfont-family:\t\t\t'".getOnlyAlpha(htmlspecialchars($font->getFontFullName() . " " . $font->getFontSubfamily()), '-')."';\n";
                        $csstxt .= "\tsrc:\t\t\turl('".sprintf(API_FONTS_SVNRAW, 'eot' . DS . $alpha . DS . $beta . DS . $charley . DS . $delta . DS . $css['eot']) ."') format('eot');\n";
                        $csstxt .= "\tsrc:\t\t\tlocal('||'),\n";
                        $csskeys = array_keys($css);
                        foreach($css as $type => $fontfile)
                            if ($type!='eot' && $type != $csskeys[count($csskeys)-1])
                                $csstxt .= "\t\t\t\turl('".sprintf(API_FONTS_SVNRAW, $type . DS . $alpha . DS . $beta . DS . $charley . DS . $delta . DS . $fontfile)."') format('".$type."'),\n";
                        elseif ($type!='eot' && $type == $csskeys[count($csskeys)-1])
                                $csstxt .= "\t\t\t\turl('".sprintf(API_FONTS_SVNRAW, $type . DS . $alpha . DS . $beta . DS . $charley . DS . $delta . DS . $fontfile)."') format('".$type."');\n";
                        $csstxt .= "}\n\n";
                        $csstxt .= "@font-face {\n";
                        $csstxt .= "\tfont-family:\t\t\t'".getOnlyAlpha(htmlspecialchars(strtolower($font->getFontFullName() . " " . $font->getFontSubfamily())), '-')."';\n";
                        $csstxt .= "\tsrc:\t\t\turl('".sprintf(API_FONTS_SVNRAW, 'eot' . DS . $alpha . DS . $beta . DS . $charley . DS . $delta . DS . $css['eot']) ."') format('eot');\n";
                        $csstxt .= "\tsrc:\t\t\tlocal('||'),\n";
                        $csskeys = array_keys($css);
                        foreach($css as $type => $fontfile)
                            if ($type!='eot' && $type != $csskeys[count($csskeys)-1])
                                $csstxt .= "\t\t\t\turl('".sprintf(API_FONTS_SVNRAW, $type . DS . $alpha . DS . $beta . DS . $charley . DS . $delta . DS . $fontfile)."') format('".$type."'),\n";
                            elseif ($type!='eot' && $type == $csskeys[count($csskeys)-1])
                                $csstxt .= "\t\t\t\turl('".sprintf(API_FONTS_SVNRAW, $type . DS . $alpha . DS . $beta . DS . $charley . DS . $delta . DS . $fontfile)."') format('".$type."');\n";
                            $csstxt .= "}\n\n";
                
                        $csstxt .= "@font-face {\n";
                        $csstxt .= "\tfont-family:\t\t\t'" . $barcode . "';\n";
                        $csstxt .= "\tsrc:\t\t\turl('".sprintf(API_FONTS_SVNRAW, 'eot' . DS . $alpha . DS . $beta . DS . $charley . DS . $delta . DS . $css['eot']) ."') format('eot');\n";
                        $csstxt .= "\tsrc:\t\t\tlocal('||'),\n";
                        $csskeys = array_keys($css);
                        foreach($css as $type => $fontfile)
                            if ($type!='eot' && $type != $csskeys[count($csskeys)-1])
                                $csstxt .= "\t\t\t\turl('".sprintf(API_FONTS_SVNRAW, $type . DS . $alpha . DS . $beta . DS . $charley . DS . $delta . DS . $fontfile)."') format('".$type."'),\n";
                            elseif ($type!='eot' && $type == $csskeys[count($csskeys)-1])
                                $csstxt .= "\t\t\t\turl('".sprintf(API_FONTS_SVNRAW, $type . DS . $alpha . DS . $beta . DS . $charley . DS . $delta . DS . $fontfile)."') format('".$type."');\n";
                        $csstxt .= "}\n\n";
                    } else {
                
                        
                        $csstxt = "@font-face {\n";
                        $csstxt .= "\tfont-family:\t\t\t'".getOnlyAlpha(htmlspecialchars($font->getFontFullName() . " " . $font->getFontSubfamily()), '-')."';\n";
                        $csstxt .= "\tsrc:\t\t\turl('".sprintf(API_FONTS_SVNRAW, 'eot' . DS . $alpha . DS . $beta . DS . $charley . DS . $css['eot']) ."') format('eot');\n";
                        $csstxt .= "\tsrc:\t\t\tlocal('||'),\n";
                        $csskeys = array_keys($css);
                        foreach($css as $type => $fontfile)
                            if ($type!='eot' && $type != $csskeys[count($csskeys)-1])
                                $csstxt .= "\t\t\t\turl('".sprintf(API_FONTS_SVNRAW, $type . DS . $alpha . DS . $beta . DS . $charley . DS . $fontfile)."') format('".$type."'),\n";
                            elseif ($type!='eot' && $type == $csskeys[count($csskeys)-1])
                                $csstxt .= "\t\t\t\turl('".sprintf(API_FONTS_SVNRAW, $type . DS . $alpha . DS . $beta . DS . $charley . DS . $fontfile)."') format('".$type."');\n";
                            $csstxt .= "}\n\n";
                            $csstxt .= "@font-face {\n";
                            $csstxt .= "\tfont-family:\t\t\t'".getOnlyAlpha(htmlspecialchars(strtolower($font->getFontFullName() . " " . $font->getFontSubfamily())), '-')."';\n";
                            $csstxt .= "\tsrc:\t\t\turl('".sprintf(API_FONTS_SVNRAW, 'eot' . DS . $alpha . DS . $beta . DS . $charley . DS .$css['eot']) ."') format('eot');\n";
                            $csstxt .= "\tsrc:\t\t\tlocal('||'),\n";
                            $csskeys = array_keys($css);
                            foreach($css as $type => $fontfile)
                                if ($type!='eot' && $type != $csskeys[count($csskeys)-1])
                                    $csstxt .= "\t\t\t\turl('".sprintf(API_FONTS_SVNRAW, $type . DS . $alpha . DS . $beta . DS . $charley . DS . $fontfile)."') format('".$type."'),\n";
                                elseif ($type!='eot' && $type == $csskeys[count($csskeys)-1])
                                    $csstxt .= "\t\t\t\turl('".sprintf(API_FONTS_SVNRAW, $type . DS . $alpha . DS . $beta . DS . $charley . DS . $fontfile)."') format('".$type."');\n";
                            $csstxt .= "}\n\n";
                            
                            $csstxt .= "@font-face {\n";
                            $csstxt .= "\tfont-family:\t\t\t'" . $barcode . "';\n";
                            $csstxt .= "\tsrc:\t\t\turl('".sprintf(API_FONTS_SVNRAW, 'eot' . DS . $alpha . DS . $beta . DS . $charley . DS . $css['eot']) ."') format('eot');\n";
                            $csstxt .= "\tsrc:\t\t\tlocal('||'),\n";
                            $csskeys = array_keys($css);
                            foreach($css as $type => $fontfile)
                                if ($type!='eot' && $type != $csskeys[count($csskeys)-1])
                                    $csstxt .= "\t\t\t\turl('".sprintf(API_FONTS_SVNRAW, $type . DS . $alpha . DS . $beta . DS . $charley .  DS . $fontfile)."') format('".$type."'),\n";
                                elseif ($type!='eot' && $type == $csskeys[count($csskeys)-1])
                                    $csstxt .= "\t\t\t\turl('".sprintf(API_FONTS_SVNRAW, $type . DS . $alpha . DS . $beta . DS . $charley .  DS . $fontfile)."') format('".$type."');\n";
                            $csstxt .= "}\n\n";
                        }
                        file_put_contents($path . DS . $filename . '.css', $csstxt);
                       
                        file_put_contents("$path/ACADEMIC", file_get_contents('https://sourceforge.net/p/chronolabs-cooperative/fonts/HEAD/tree/ACADEMIC?format=raw'));
	                    file_put_contents("$path/LICENCE", file_get_contents('https://sourceforge.net/p/chronolabs-cooperative/fonts/HEAD/tree/ubuntu-font-licence-1.0.txt?format=raw'));
	                    file_put_contents("$path/LICENCE-GPL3", file_get_contents('https://sourceforge.net/p/chronolabs-cooperative/fonts/HEAD/tree/LICENSE?format=raw'));
	                    echo shell_exec("cd \"$path\"");
	                    echo shell_exec("/usr/bin/zip -u -D -r -9 '$path/$filename.zip' .");
	                    
	                    if (is_file($path . DS . "$filename.zip")) {
	                    
        			         if (!is_file(__DIR__ . DS . "cron.staging.$fontid.sh"))
        			    	       $sh = array(0 => 'unlink "' . __DIR__ . DS . "cron.staging.$fontid.sh\"", 1 => 'wait ' . mt_rand(2 * mt_rand(20, 60), 6 * mt_rand(20, 60)));
	                         else 
	                               $sh = cleanWhitespaces(file(__DIR__ . DS . "cron.staging.$fontid.sh"));

                           $sh[] = "echo \"\\nStaging Font: $fontid ~ " . $font->getFontFullname() . "\"";

                           foreach($missing as $ext => $files) {
                               foreach($files as $filz) {
                                   if ($ext!='png')
                                        if (file_exists($path . DS . basename($filz)))
                                            $sh[] = sprintf(API_FONTS_STAGER, "Font Type: *." . $ext . " ~ " . $font->getFontFullname() . ' ~ ' . basename($filz), $path . DS . basename($filz), $filz);
                                   else 
                                       if (file_exists($path . DS . 'glyphs' . DS . basename($filz)))
                                           $sh[] = sprintf(API_FONTS_STAGER, "Font Type: *." . $ext . " ~ " . $font->getFontFullname() . ' ~ ' . basename($filz), $path . DS . 'glyphs' . DS . basename($filz), $filz);
                            }
                            
                            $sh[] = "rm -Rf \"$path\"";
			                $sh[] = 'wait ' . mt_rand(2 * mt_rand(20, 60), 6 * mt_rand(20, 60));
                            file_put_contents(__DIR__ . DS . "cron.congenality.$fontid.sh", implode("\n", $sh));
                        }
                    }
                }
            }
            sleep(mt_rand(4, 14));
        } else {
            $GLOBALS['APIDB']->queryF("UPDATE `fonts` SET `passed` = UNIX_TIMESTAMP() WHERE `id` = $fontid");
        }
    }
?>
