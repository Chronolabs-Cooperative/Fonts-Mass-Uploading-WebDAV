<?php
   
    require __DIR__ . DIRECTORY_SEPARATOR . 'mainfile.php';

    ini_set('memory_limit', '128M');


        chdir(API_FONTS_JSON);
     echo   shell_exec('svn cleanup');
	echo shell_exec('svn update');

    
    list($count) = $GLOBALS['APIDB']->fetchRow($GLOBALS['APIDB']->queryF("SELECT COUNT(*) FROM `fonts` WHERE `processed` > '0' AND `stored` > '0' AND `tagged` > '0'"));
    if ($count != 0) {

        $result = $GLOBALS['APIDB']->queryF("SELECT `id`, `sourceid`, `key` FROM `fonts` WHERE `processed` > '0' AND `stored` > '0' AND `tagged` > '0'");
        $keys = $sources = array();
        while($font = $GLOBALS['APIDB']->fetchArray($result)) {
            $keys[$font['id']] = $font['key'];
            $sources[$font['sourceid']] = $font['id'];
        }
    }    
     $structure = array();
        
    echo "OTHER: Failed Open Licensing!\n";    
    $sql = "SELECT `a`.`state` as `state`, `a`.`fingerprint` as `fingerprint`, `a`.`sha1` as `sha1`, `a`.`md5` as `md5`, `a`.`bytes` as `bytes`, `a`.`filename` as `filename`, `a`.`found` as `uploaded` FROM `sources` as `a` WHERE `a`.`id` NOT IN (" .implode(", ", array_keys($sources)) . ") AND `State` = 'Locked'";
    $source = array();
    $result = $GLOBALS['APIDB']->queryF($sql);
    while($src = $GLOBALS['APIDB']->fetchArray($result)) {
        $src['uploaded'] = date('Y-m-d H:i:s', $src['uploaded']);
        $source[] = $src;
    }
    file_put_contents($jfile = API_FONTS_JSON . DS . "failed.open.licensing.json", json_encode($source));
    $structure[md5_file($jfile)] = array('bytes' => filesize($jfile), 'records' => count($source), 'path' => '/json', 'filename' => basename($jfile), 'meter' => 'failed', 'type' => 'sources');
    
    echo "OTHER: Duplicate Font Upload!\n";   
    $sql = "SELECT `a`.`state` as `state`, `a`.`fingerprint` as `fingerprint`, `a`.`sha1` as `sha1`, `a`.`md5` as `md5`, `a`.`bytes` as `bytes`, `a`.`filename` as `filename`, `a`.`found` as `uploaded` FROM `sources` as `a` WHERE `a`.`id` NOT IN (" .implode(", ", array_keys($sources)) . ") AND `State` = 'Duplicate'";
    $source = array();
    $result = $GLOBALS['APIDB']->queryF($sql);
    while($src = $GLOBALS['APIDB']->fetchArray($result)) {
        $src['uploaded'] = date('Y-m-d H:i:s', $src['uploaded']);
        $source[] = $src;
    }
    file_put_contents($jfile = API_FONTS_JSON . DS . "duplicate.font.sources.json", json_encode($source));
    $structure[md5_file($jfile)] = array('bytes' => filesize($jfile), 'records' => count($source), 'path' => '/json', 'filename' => basename($jfile), 'meter' => 'duplicate', 'type' => 'sources');
    
    echo "OTHER: Waiting Font Staging!\n";   
    $sql = "SELECT `a`.`state` as `state`, `a`.`fingerprint` as `fingerprint`, `a`.`sha1` as `sha1`, `a`.`md5` as `md5`, `a`.`bytes` as `bytes`, `a`.`filename` as `filename`, `a`.`found` as `uploaded` FROM `sources` as `a` WHERE `a`.`id` NOT IN (" .implode(", ", array_keys($sources)) . ") AND `State` = 'Unique'";
    $source = array();
    $result = $GLOBALS['APIDB']->queryF($sql);
    while($src = $GLOBALS['APIDB']->fetchArray($result)) {
        $src['uploaded'] = date('Y-m-d H:i:s', $src['uploaded']);
        $source[] = $src;
    }
    file_put_contents($jfile = API_FONTS_JSON . DS . "waiting.font.staging.json", json_encode($source));
    $structure[md5_file($jfile)] = array('bytes' => filesize($jfile), 'records' => count($source), 'path' => '/json', 'filename' => basename($jfile), 'meter' => 'waiting', 'type' => 'sources');

    echo "OTHER: Queued Font Staging!\n";   
    $sql = "SELECT `a`.`state` as `state`, `a`.`fingerprint` as `fingerprint`, `a`.`sha1` as `sha1`, `a`.`md5` as `md5`, `a`.`bytes` as `bytes`, `a`.`filename` as `filename`, `a`.`found` as `uploaded` FROM `sources` as `a` WHERE `a`.`id` NOT IN (" .implode(", ", array_keys($sources)) . ") AND `State` = 'Queued'";
    $source = array();
    $result = $GLOBALS['APIDB']->queryF($sql);
    while($src = $GLOBALS['APIDB']->fetchArray($result)) {
        $src['uploaded'] = date('Y-m-d H:i:s', $src['uploaded']);
        $source[] = $src;
    }
    file_put_contents($jfile = API_FONTS_JSON . DS . "queued.font.staging.json", json_encode($source));
    $structure[md5_file($jfile)] = array('bytes' => filesize($jfile), 'records' => count($source), 'path' => '/json', 'filename' => basename($jfile), 'meter' => 'queued', 'type' => 'sources');
    
    $sql = "SELECT `a`.`state` as `state`, `a`.`fingerprint` as `fingerprint`, `a`.`sha1` as `sha1`, `a`.`md5` as `md5`, `a`.`bytes` as `bytes`, `a`.`filename` as `filename`, `a`.`found` as `uploaded`";
    $sql .= " FROM `sources` as `a` WHERE `a`.`id` NOT IN (" .implode(", ", array_keys($sources)) . ") AND `State` = 'Deleted'";
    $source = array();
    $result = $GLOBALS['APIDB']->queryF($sql);
    while($src = $GLOBALS['APIDB']->fetchArray($result)) {
        $src['uploaded'] = date('Y-m-d H:i:s', $src['uploaded']);
        $source[] = $src;
    }
    file_put_contents($jfile = API_FONTS_JSON . DS . "deleted.font.sources.json", json_encode($source));
    $structure[md5_file($jfile)] = array('bytes' => filesize($jfile), 'records' => count($source), 'path' => '/json', 'filename' => basename($jfile), 'meter' => 'deleted', 'type' => 'sources');
    
    echo "OTHER: Completed Font Staging!\n";
    $sql = "SELECT `a`.`id` as `sourceid`, `a`.`state` as `state`, `a`.`fingerprint` as `fingerprint`, `a`.`sha1` as `sha1`, `a`.`md5` as `md5`, `a`.`bytes` as `bytes`, `a`.`filename` as `filename`, `a`.`found` as `uploaded` FROM `sources` as `a` WHERE `a`.`id` IN (" .implode(", ", array_keys($sources)) . ") AND `State` = 'Unique'";
    $source = array();
    $result = $GLOBALS['APIDB']->queryF($sql);
    while($src = $GLOBALS['APIDB']->fetchArray($result)) {
        $sourceid = $src['sourceid'];
        unset($src['sourceid']);
        $src['uploaded'] = date('Y-m-d H:i:s', $src['uploaded']);
        $source[$keys[$sources[$sourceid]]] = $src;
    }
    file_put_contents($jfile = API_FONTS_JSON . DS . "completed.font.staging.json", json_encode($source));
    $structure[md5_file($jfile)] = array('bytes' => filesize($jfile), 'records' => count($source), 'path' => '/json', 'filename' => basename($jfile), 'meter' => 'completed', 'type' => 'sources');
    
    file_put_contents($jfile = API_FONTS_JSON . DS . "others.structures.json", json_encode($structure));
    file_put_contents($jfile = API_FONTS_JSON . DS . "structures.json", json_encode(array_merge(json_decode(file_get_contents(API_FONTS_JSON . DS . "fonts.structures.json"), true), 
                                                                                                json_decode(file_get_contents(API_FONTS_JSON . DS . "glyphs.structures.json"), true),
                                                                                                json_decode(file_get_contents(API_FONTS_JSON . DS . "files.structures.json"), true),
                                                                                                json_decode(file_get_contents(API_FONTS_JSON . DS . "tags.structures.json"), true),
                                                                                                json_decode(file_get_contents(API_FONTS_JSON . DS . "others.structures.json"), true))));
    chdir(API_FONTS_JSON);
    echo shell_exec('svn add * --force');
//    echo shell_exec(sprintf("svn commit -m '%s'", "Other Data JSON Resources: ". date('Y/m/d D H:i:s')));
    
