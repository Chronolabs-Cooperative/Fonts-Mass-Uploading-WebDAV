<?php

    require __DIR__ . DIRECTORY_SEPARATOR . 'mainfile.php';
	ini_set('memory_limit', '315M');
    require_once __DIR__ . DS . 'include' . DS . 'functions.php';
    $folders = getCompleteDirListAsArray(API_FONTS_SOURCES);
    shuffle($folders);
    shuffle($folders);
    shuffle($folders);
    shuffle($folders);
    foreach($folders as $folder)
    {
        foreach(getFontsListAsArray($folder) as $file => $values) {
            list($count) = $GLOBALS['APIDB']->fetchRow($GLOBALS['APIDB']->queryF("SELECT COUNT(*) FROM `sources` WHERE `filename` LIKE '$file' AND `path` LIKE '$folder'"));
            if ($count == 0)
            {
                if (!$GLOBALS['APIDB']->queryF($sql = "INSERT INTO `sources` (`filename`, `path`, `extension`, `bytes`, `found`) VALUES('" . $GLOBALS['APIDB']->escape($file) . "', '" . $GLOBALS['APIDB']->escape($folder) . "', '" . $GLOBALS['APIDB']->escape($values['type']) . "', '" . filesize($folder.DS.$file) . "', UNIX_TIMESTAMP())"))
                    die("SQL Failed: $sql;");
                else
                    echo "\nFont File Added: $file";
            } else {
		list($id, $state) = $GLOBALS['APIDB']->fetchRow($GLOBALS['APIDB']->queryF("SELECT `id`, `state`  FROM `sources` WHERE `filename` LIKE '$file' AND `path` LIKE '$folder'"));
		if ($state == 'Locked') {
			$GLOBALS['APIDB']->queryF("UPDATE `sources` SET `state` = 'Queued', `fingered` = 0 WHERE `id = '$id'");
	                echo "\nFont File Requeued: $file";
		} else 
			echo "\nFont File Skipped: $file";
	   }
        }
    }
    
?>
