<?php

//    sleep(mt_rand(10, mt_rand(1, 6) * 20));
    
    use FontLib\Font;
    require_once __DIR__.'/class/FontLib/Autoloader.php';

    require __DIR__ . DIRECTORY_SEPARATOR . 'mainfile.php';
    require __DIR__ . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'xcp.class.php';

    ini_set('memory_limit', '196M');
    
    list($count) = $GLOBALS['APIDB']->fetchRow($GLOBALS['APIDB']->queryF("SELECT COUNT(*) FROM `sources` WHERE `found` > 0 AND `fingered` > '0' and `state` = 'Unique' and `action` = '0'"));
    
    if ($count>0)
    {
        $start = time();
        while(time() < $start + (mt_rand(12,21)*20)) {
            $result = $GLOBALS['APIDB']->queryF("SELECT * FROM `sources` WHERE `found` > 0 AND `fingered`  > '0' and `state` = 'Unique' and `action` = '0' ORDER BY RAND() LIMIT 41");
            while($source = $GLOBALS['APIDB']->fetchArray($result)) {
                mkdir($path = API_FONTS_STAGING . DS . md5_file($source['path'] . DS . $source['filename']), 0777, true);
                copy($source['path'] . DS . $source['filename'], $path . DS . strtolower($source['filename']));
                if (is_file($path . DS . strtolower($source['filename']))) {
                    unlink($source['path'] . DS . $source['filename']);
                    chdir($path);
                    exec(sprintf(DIRECTORY_SEPARATOR . "usr" . DIRECTORY_SEPARATOR . "bin" . DIRECTORY_SEPARATOR . "fontforge -script \"%s\" \"%s\"", __DIR__ . DS . 'include' . DS . 'data' . DS . 'convert-fonts-distribution.pe', $path . DS . strtolower($source['filename'])));
                    $font = Font::load($path . DS . str_replace(".".$source['extension'], ".otf", strtolower($source['filename'])));
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
                        
                        $xcp = new xcp(NULL, mt_rand(0, 255), 12);
                        $barcode = $xcp->calc($source['fingerprint']);
                    
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
                                                
                        file_put_contents($path . DS . $filename . '.css', $csstxt);
                       
                        if ($GLOBALS["APIDB"]->queryF($sql = "INSERT INTO `fonts` (`sourceid`, `key`, `name`, `fullname`, `postscriptname`, `subfamily`, `subfamilyid`, `copyright`, `version`, `filename`, `alpha`, `beta`, `charley`, `delta`, `barcode`) VALUES ('" . $source['id'] . "', '" . md5($source['fingerprint']) . "', '" . $GLOBALS['APIDB']->escape($font->getFontName()) . "', '" . $GLOBALS['APIDB']->escape($font->getFontFullName()) . "', '" . $GLOBALS['APIDB']->escape($font->getFontPostscriptName()) . "', '" . $GLOBALS['APIDB']->escape($font->getFontSubfamily()) . "', '" . $GLOBALS['APIDB']->escape($font->getFontSubfamilyID()) . "', '" . $GLOBALS['APIDB']->escape($font->getFontCopyright()) . "', '" . $GLOBALS['APIDB']->escape($font->getFontVersion()) . "', '" . $filename . "', '" . $alpha . "', '" . $beta . "', '" . $charley .  "', '" . $delta  . "', '" . $barcode . "')"))
                        {
	                        $fontid = $GLOBALS["APIDB"]->getInsertID();
	                       
	                        file_put_contents($path . DS . 'font.json', json_encode(array(  'key'               => md5($source['fingerprint']),
	                                                                                        'name'              => $font->getFontName(),
	                                                                                        'fullname'          => $font->getFontFullname(),
	                                                                                        'postscriptname'    => $font->getFontPostscriptName(),
	                                                                                        'subfamily'         => $font->getFontSubfamily(),
	                                                                                        'subfamilyid'       => $font->getFontSubfamilyID(),
	                                                                                        'copyright'         => $font->getFontCopyright(),
	                                                                                        'version'           => $font->getFontVersion(),
	                                                                                        'barcode'           => $barcode,
	                                                                                        'filename'          => $filename,
	                                                                                        'files'             => getFontsListAsArray($path),
	                                                                                        'glyphs'            => $glyphs
	                                                                    )));
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

	                            $GLOBALS["APIDB"]->queryF("START TRANSACTION");
	                            foreach(getFontsListAsArray($path) as $file) {
	                                #$sh[] = "while [ curl \"" . sprintf(API_FONTS_SVNRAW, $file['type'] . DS . $alpha . DS . $beta . DS . $charley . DS . $delta . DS . $file['file']) . "\" | md5sum != md5sum \"" . $path . DS . $file['file'] . "\" ]";
	                                #$sh[] = "do";
	                                $sh[] = sprintf(API_FONTS_STAGER, "Font Type: *." . $file['type'] . " ~ " . $font->getFontFullname() . ' ~ ' . $file['file'], $path . DS . $file['file'], $file['type'] . DS . $alpha . DS . $beta . DS . $charley . DS . $delta . DS . $file['file']);
	                                #$sh[] = "done";
	                                if (!$GLOBALS["APIDB"]->queryF($sql = "INSERT INTO `files` (`sourceid`, `fontid`, `filename`, `path`, `extension`, `bytes`, `md5`, `sha1`) VALUES ('" . $source['id'] . "', '" . $fontid . "', '" . $GLOBALS['APIDB']->escape($file['file']) . "', '" . $GLOBALS['APIDB']->escape($file['type'] . DS . $alpha . DS . $beta . DS . $charley . DS . $delta) . "', '" . $GLOBALS['APIDB']->escape($file['type']) . "', '" . filesize($path . DS . $file['file']) . "', '" . md5_file($path . DS . $file['file']) . "', '" . sha1_file($path . DS . $file['file']) . "')"))
	                                    die("SQL Failed: $sql;");
	                            }
	                            
	                            #$sh[] = "while [ curl \"" . sprintf(API_FONTS_SVNRAW, "zip" . DS . $alpha . DS . $beta . DS . $charley . DS . $delta . DS . "$filename.zip") . "\" | md5sum != md5sum \"" . $path . DS . "$filename.zip" . "\" ]";
	                            #$sh[] = "do";
	                            $sh[] = sprintf(API_FONTS_STAGER, "Font Archive: *.zip ~ " . $font->getFontFullname() . " ~ $filename.zip", $path . DS . "$filename.zip", "zip" . DS . $alpha . DS . $beta . DS . $charley . DS . $delta . DS . "$filename.zip");
	                            #$sh[] = "done";
	                            if (!$GLOBALS["APIDB"]->queryF($sql = "INSERT INTO `files` (`sourceid`, `fontid`, `filename`, `path`, `extension`, `bytes`, `md5`, `sha1`) VALUES ('" . $source['id'] . "', '" . $fontid . "', '" . $GLOBALS['APIDB']->escape("$filename.zip") . "', '" . $GLOBALS['APIDB']->escape("zip" . DS . $alpha . DS . $beta . DS . $charley . DS . $delta) . "', '" . $GLOBALS['APIDB']->escape("zip") . "', '" . filesize($path . DS . "$filename.zip") . "', '" . md5_file($path . DS . "$filename.zip") . "', '" . sha1_file($path . DS . "$filename.zip") . "')"))
	                                die("SQL Failed: $sql;");
	                        
                                #$sh[] = "while [ curl \"" . sprintf(API_FONTS_SVNRAW, "css" . DS . $alpha . DS . $beta . DS . $charley . DS . $delta . DS . "$filename.css") . "\" | md5sum != md5sum \"" . $path . DS . "$filename.css" . "\" ]";
                                #$sh[] = "do";
	                            $sh[] = sprintf(API_FONTS_STAGER, "Font CSS: *.css ~ " . $font->getFontFullname() . " ~ $filename.css", $path . DS . "$filename.css", "css" . DS . $alpha . DS . $beta . DS . $charley . DS . $delta . DS . "$filename.css");
	                            #$sh[] = "done";
	                            if (!$GLOBALS["APIDB"]->queryF($sql = "INSERT INTO `files` (`sourceid`, `fontid`, `filename`, `path`, `extension`, `bytes`, `md5`, `sha1`) VALUES ('" . $source['id'] . "', '" . $fontid . "', '" . $GLOBALS['APIDB']->escape("$filename.css") . "', '" . $GLOBALS['APIDB']->escape("css" . DS . $alpha . DS . $beta . DS . $charley . DS . $delta) . "', '" . $GLOBALS['APIDB']->escape("css") . "', '" . filesize($path . DS . "$filename.css") . "', '" . md5_file($path . DS . "$filename.css") . "', '" . sha1_file($path . DS . "$filename.css") . "')"))
	                                die("SQL Failed: $sql;");
	                                
                                #$sh[] = "while [ curl \"" . sprintf(API_FONTS_SVNRAW, "naming.png" . DS . $alpha . DS . $beta . DS . $charley . DS . $delta . DS . "$filename.naming.png") . "\" | md5sum != md5sum \"" . $path . DS . "$filename.naming.png" . "\" ]";
                                #$sh[] = "do";
	                            $sh[] = sprintf(API_FONTS_STAGER, "Font Naming Card: *.naming.png ~ " . $font->getFontFullname() . " ~ $filename.naming.png", $path . DS . "$filename.naming.png", "naming.png" . DS . $alpha . DS . $beta . DS . $charley . DS . $delta . DS . "$filename.naming.png");
	                            #$sh[] = "done";
	                            if (!$GLOBALS["APIDB"]->queryF($sql = "INSERT INTO `files` (`sourceid`, `fontid`, `filename`, `path`, `extension`, `bytes`, `md5`, `sha1`) VALUES ('" . $source['id'] . "', '" . $fontid . "', '" . $GLOBALS['APIDB']->escape("$filename.naming.png") . "', '" . $GLOBALS['APIDB']->escape("naming.png" . DS . $alpha . DS . $beta . DS . $charley . DS . $delta) . "', '" . $GLOBALS['APIDB']->escape("naming.png") . "', '" . filesize($path . DS . "$filename.naming.png") . "', '" . md5_file($path . DS . "$filename.naming.png") . "', '" . sha1_file($path . DS . "$filename.naming.png") . "')"))
	                                die("SQL Failed: $sql;");
	                                
	                                #$sh[] = "while [ curl \"" . sprintf(API_FONTS_SVNRAW, "preview.png" . DS . $alpha . DS . $beta . DS . $charley . DS . $delta . DS . "$filename.preview.png") . "\" | md5sum != md5sum \"" . $path . DS . "$filename.preview.png" . "\" ]";
                                #$sh[] = "do";
	                            $sh[] = sprintf(API_FONTS_STAGER, "Font Preview Card: *.preview.png ~ " . $font->getFontFullname() . " ~ $filename.preview.png", $path . DS . "$filename.preview.png", "preview.png" . DS . $alpha . DS . $beta . DS . $charley . DS . $delta . DS . "$filename.preview.png");
	                            #$sh[] = "done";
	                            if (!$GLOBALS["APIDB"]->queryF($sql = "INSERT INTO `files` (`sourceid`, `fontid`, `filename`, `path`, `extension`, `bytes`, `md5`, `sha1`) VALUES ('" . $source['id'] . "', '" . $fontid . "', '" . $GLOBALS['APIDB']->escape("$filename.preview.png") . "', '" . $GLOBALS['APIDB']->escape("preview.png" . DS . $alpha . DS . $beta . DS . $charley . DS . $delta) . "', '" . $GLOBALS['APIDB']->escape("preview.png") . "', '" . filesize($path . DS . "$filename.preview.png") . "', '" . md5_file($path . DS . "$filename.preview.png") . "', '" . sha1_file($path . DS . "$filename.preview.png") . "')"))
	                                die("SQL Failed: $sql;");
	                            
	                            foreach(getFileListAsArray($path . DS . 'glyphs') as $file) {
	                                #$sh[] = "while [ curl \"" . sprintf(API_FONTS_SVNRAW, "glyphs" . DS . $alpha . DS . $beta . DS . $charley . DS . $delta . DS . $filename . DS . $file) . "\" | md5sum != md5sum \"" . $path . DS . "glyphs/$file" . "\" ]";
	                                #$sh[] = "do";
	                                $sh[] = sprintf(API_FONTS_STAGER, "Font Glyph Card's: *.png ~ " . $font->getFontFullname() . " ~ glyphs/$file", $path . DS . "glyphs/$file", "glyphs" . DS . $alpha . DS . $beta . DS . $charley . DS . $delta . DS . $filename . DS . $file);
	                                #$sh[] = "done";
	                                if (!$GLOBALS["APIDB"]->queryF($sql = "INSERT INTO `files` (`sourceid`, `fontid`, `filename`, `path`, `extension`, `bytes`, `md5`, `sha1`) VALUES ('" . $source['id'] . "', '" . $fontid . "', '" . $GLOBALS['APIDB']->escape($file) . "', '" . $GLOBALS['APIDB']->escape('glyphs' . DS . $alpha . DS . $beta . DS . $charley . DS . $delta . DS . $filename) . "', '" . $GLOBALS['APIDB']->escape('png') . "', '" . filesize($path . DS . 'glyphs' . DS . $file) . "', '" . md5_file($path . DS . 'glyphs' . DS . $file) . "', '" . sha1_file($path . DS . 'glyphs' . DS . $file) . "')"))
	                                    die("SQL Failed: $sql;");
	                            }
	                            
	                            $GLOBALS['APIDB']->queryF("UPDATE `glyphs` SET `fontid` = '$fontid' WHERE `sourceid` = '" . $source['id'] . "'");
	                            
	                            $sh[] = "rm -Rf \"$path\"";
				    $sh[] = "/usr/bin/php -q '".__DIR__ . DS . 'cron.stored.'.$fontid.'.php\'';
				    $sh[] = 'wait ' . mt_rand(2 * mt_rand(20, 60), 6 * mt_rand(20, 60));

	                            $GLOBALS["APIDB"]->queryF("COMMIT");
	                            
	                            file_put_contents(__DIR__ . DS . 'cron.stored.'.$fontid.'.php', sprintf(file_get_contents(__DIR__ . DS . 'cron.stored.txt'), $fontid));
	                    
	                            list($files, $storage) = $GLOBALS['APIDB']->fetchRow($GLOBALS['APIDB']->queryF("SELECT COUNT(*), sum(`bytes`) FROM `files` WHERE `fontid` = '$fontid'"));
	                            
	                            if (!$GLOBALS['APIDB']->queryF($sql = "UPDATE `fonts` SET `processed` = UNIX_TIMESTAMP(), `files` = '$files', `storage` = '$storage', `glyphs` = '" . count($glyphs) . "', `archive` = '" . filesize($path . DS . "$filename.zip") . "' WHERE `id` = '$fontid'"))
	                                die("SQL Failed: $sql;");
	                            
	                            file_put_contents(__DIR__ . DS . "cron.staging.$fontid.sh", implode("\n", $sh));
	                            

	                            if (!$GLOBALS['APIDB']->queryF($sql = "UPDATE `sources` SET `action` = UNIX_TIMESTAMP() WHERE `id` = '" . $source['id'] . "'"))
	                                die("SQL Failed: $sql;");
	                        } else {
	                            shell_exec("rm -Rf '$path'");
	                        }
	                    }
	         
			} else {
				echo("\nSQL Failed: $sql;\n");
				shell_exec("rm -Rf '$path'");
				continue;
				continue;
			}
			unset($font);

                }
            }
            sleep(mt_rand(4, 14));
        }
    }
?>
