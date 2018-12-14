<?php
    
    sleep(mt_rand(10, mt_rand(14, 27) * 60));

    require __DIR__ . DIRECTORY_SEPARATOR . 'mainfile.php';
    
    list($count) = $GLOBALS['APIDB']->fetchRow($GLOBALS['APIDB']->queryF("SELECT COUNT(*) FROM `fonts` WHERE `processed` > '0' AND `stored` > '0' AND `tagged` = '0'"));
    if ($count != 0) {
        $result = $GLOBALS['APIDB']->queryF("SELECT * FROM `fonts` WHERE `processed` > '0' AND `stored` > '0' AND `tagged` = '0' ORDER BY RAND() LIMIT 200");
        while($font = $GLOBALS['APIDB']->fetchArray($result)) {
            $tags = 0;
            $tagstr = ucwords(str_replace(array("_", "-", "=", "+", "~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "{", "}", "[", "]", "\\", "|", "\"", "'", ":", ";", "<", ">", ",", "?", "/"), " ", $font['name'] . " " . $font['subfamily']));
            while(strpos($tagstr, "  "))
                $tagstr = str_replace("  ", " ", $tagstr);
            foreach(explode(" ", $tagstr) as $tag)
            {
                $tags++;
                list($count) = $GLOBALS['APIDB']->fetchRow($GLOBALS['APIDB']->queryF("SELECT COUNT(*) FROM `tags` WHERE `tag` LIKE '$tag'"));
                if ($count == 0) {
                    if (!$GLOBALS['APIDB']->queryF($sql = "INSERT INTO `tags` (`tag`) VALUES('$tag')"))
                        die("SQL Failed: $sql;");
                    $tagid = $GLOBALS['APIDB']->getInsertID();
                } else {
                    list($tagid) = $GLOBALS['APIDB']->fetchRow($GLOBALS['APIDB']->queryF("SELECT `id` FROM `tags` WHERE `tag` LIKE '$tag'"));
                    if (!$GLOBALS['APIDB']->queryF($sql = "UPDATE `tags` SET `occured` = `occured` + 1 WHERE `id` = '$tagid'"))
                        die("SQL Failed: $sql;");
                }
                if (!$GLOBALS['APIDB']->queryF($sql = "INSERT INTO `tags_links` (`tagid`, `fontid`, `sourceid`) VALUES('$tagid', '" . $font['id'] . "', '" . $font['sourceid'] . "')"))
                    die("SQL Failed: $sql;");
            }
            
            $extensions = array();
            $resultb = $GLOBALS['APIDB']->queryF("SELECT DISTINCT `extension` FROM `files` WHERE `fontid` = '" . $font['id'] . "' ORDER BY `extension` ASC");
            while($file = $GLOBALS['APIDB']->fetchArray($resultb))
                $extensions[] = $file['extension'];

            $email = '';
            $emailstr = str_replace(array("\n", "\t", "\r", "_", "-", "=", "+", "~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "{", "}", "[", "]", "\\", "|", "\"", "'", ":", ";", "<", ">", ",", "?", "/", "."), " ", $font['copyright']);

            while(strpos($emailstr, "  "))
                $emailstr = str_replace("  ", " ", $emailstr);
            foreach(explode(" ", $emailstr) as $testemail)
                if (checkEmail($testemail)) {
                    $email = $testemail;
                    continue;
                }
            
            if (!$GLOBALS['APIDB']->queryF($sql = "UPDATE `fonts` SET `tags` = '$tags', `email` = '$email', `extensions` = '" . implode("|", $extensions) . "', `tagged` = UNIX_TIMESTAMP() WHERE `id` = '" . $font['id'] . "'"))
                die("SQL Failed: $sql;");
            
        }
    }
    
?>