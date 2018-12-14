<?php

//    sleep(mt_rand(6*60, mt_rand(7, 13) * 60));
    
    require __DIR__ . DIRECTORY_SEPARATOR . 'mainfile.php';
    
    ini_set('memory_limit', '64M');
    list($count) = $GLOBALS['APIDB']->fetchRow($GLOBALS['APIDB']->queryF("SELECT COUNT(*) FROM `sources` WHERE (`state` = 'Queued' AND `found` > 0 AND `fingered` = '0') OR (`fingered` > '0' AND `state` IN ('Queued', 'Locked'))"));
    
    if ($count>0)
    {
        $start = time();
        while(time() < $start + (4*60)) {
            $result = $GLOBALS['APIDB']->queryF("SELECT * FROM `sources` WHERE (`state` = 'Queued' AND `found` > 0 AND `fingered` = '0') OR (`fingered` > '0' AND `state` IN ('Queued', 'Locked')) ORDER BY RAND() LIMIT 241");
            while($source = $GLOBALS['APIDB']->fetchArray($result)) {
                if (is_file($source['path'] . DS . $source['filename'])) {
                    mkdir($path = API_VAR_PATH . DS . md5_file($source['path'] . DS . $source['filename']), 0777, true);
                    copy($source['path'] . DS . $source['filename'], $path . DS . $source['filename']);
		    exec(sprintf(DIRECTORY_SEPARATOR . "usr" . DIRECTORY_SEPARATOR . "bin" . DIRECTORY_SEPARATOR . "fontforge -script \"%s\" \"%s\"", __DIR__ . DS . 'include' . DS . 'data' . DS . 'convert-fonts-eot.pe', $path . DS . $source['filename']));
                    exec(sprintf(DIRECTORY_SEPARATOR . "usr" . DIRECTORY_SEPARATOR . "bin" . DIRECTORY_SEPARATOR . "fontforge -script \"%s\" \"%s\"", __DIR__ . DS . 'include' . DS . 'data' . DS . 'convert-fonts-ufo.pe', $path . DS . str_replace(array('.'.$source['extension'], strtoupper('.'.$source['extension'])), '.eot', $source['filename'])));
		    if (is_dir($path . DS . str_replace(array(strtolower("." . $source['extension']), strtoupper("." . $source['extension'])), '.ufo', $source['filename']) . DS . 'glyphs')) {
                        $glyphsfingerprint = '';
                        
                        $glyphs = array();
                        $fileglyphs = getFileListAsArray($path . DS . str_replace(array(strtolower("." . $source['extension']), strtoupper("." . $source['extension'])), '.ufo', $source['filename']) . DS . 'glyphs');
                        sort($fileglyphs);
                        foreach($fileglyphs as $glyph)
                        {
                            $glyphs[$glyph] = getGlyphArrayFromXML(xml2array(file_get_contents($path . DS . str_replace(array(strtolower("." . $source['extension']), strtoupper("." . $source['extension'])), '.ufo', $source['filename']) . DS . 'glyphs' . DS . $glyph)));
                        }
                        $fingering = array();
                        foreach($glyphs as $key => $values)
                            $fingering[] = $values['fingerprint'];
                        
                        sort($fingering);
                        list($count) = $GLOBALS['APIDB']->fetchRow($GLOBALS['APIDB']->queryF("SELECT COUNT(*) FROM `sources` WHERE `fingerprint` LIKE '" . sha1(implode('', $fingering)) ."' AND `sha1` LIKE '" . sha1_file($source['path']. DS . $source['filename']) ."' AND `state` NOT IN ('Locked', 'Duplicate')"));
                        if ($count == 0)
                        {
                            $GLOBALS['APIDB']->query('UPDATE `sources` SET `sha1` = "' . sha1_file($source['path']. DS . $source['filename']) . '", `md5` = "' . md5_file($source['path']. DS . $source['filename']) . '", `fingerprint` = "' . sha1(implode('', $fingering)) . '", `state` = "Unique", `fingered` = UNIX_TIMESTAMP() WHERE `id` = "' . $source['id']. '"');
                            $GLOBALS['APIDB']->query('START TRANSACTION');
                            foreach($glyphs as $key => $glyph)
                            {
                                if (!$GLOBALS['APIDB']->query($sql = 'INSERT INTO `glyphs` (`sourceid`, `name`, `unicode`) VALUES ("' . $source['id']. '", "' . $glyph['name'] . '", "' . $glyph['unicode'] . '")'))
                                    die("SQL Failed: $sql;");
                            }
                            $GLOBALS['APIDB']->query('COMMIT');
                            echo "\nFingered Unique Font: " . $source['filename'] . "\n\n";
                        } else {
                            $GLOBALS['APIDB']->query('UPDATE `sources` SET `fingerprint` = "' . sha1(implode('', $fingering)) . '", `sha1` = "' . sha1_file($source['path']. DS . $source['filename']) . '", `md5` = "' . md5_file($source['path']. DS . $source['filename']) . '", `state` = "Duplicate", `fingered` = UNIX_TIMESTAMP() WHERE `id` = "' . $source['id']. '"');
                            echo "\nFingered Duplicate Font: " . $source['filename'] . "\n\n";
                            unlink($source['path'] . DS . $source['filename']);
                        }
                        
                    } else {
                        $GLOBALS['APIDB']->query('UPDATE `sources` SET `state` = "Locked", `sha1` = "' . sha1_file($source['path']. DS . $source['filename']) . '", `md5` = "' . md5_file($source['path']. DS . $source['filename']) . '", `fingered` = UNIX_TIMESTAMP() WHERE `id` = "' . $source['id']. '"');
                        echo "\nLocked Font: " . $source['filename'] . "\n\n";
            			if ($read == APICache::read(sha1($source['path'] . DS . $source['filename'])))
            				if ($read['amount'] + 1 > 3) {
            		            unlink($source['path'] . DS . $source['filename']);
            					$GLOBALS['APIDB']->query('UPDATE `sources` SET `state` = "Deleted", `fingered` = UNIX_TIMESTAMP() WHERE `id` = "' . $source['id']. '"');
            					APICache::delete(sha1($source['path'] . DS . $source['filename']));
            				}
            			if (!empty($read)) {
            				$read['amount'] += 1;
            				$read['time'] = microtime(true);
            			} else {
            				$read = array('time' => microtime(true), 'amount' => 1);
            			}
    			        APICache::write(sha1($source['path'] . DS . $source['filename']), $read, 3600 * 24 * 7 * 4 * 3);
                    }
                }
            }
            sleep(mt_rand(4, 15));
        }
    }
    
    
?>
