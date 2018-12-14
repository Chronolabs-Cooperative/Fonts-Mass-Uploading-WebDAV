<?php
   
    require __DIR__ . DIRECTORY_SEPARATOR . 'mainfile.php';

        ini_set('memory_limit', '128M');


        chdir(API_FONTS_JSON);
       echo shell_exec('svn cleanup');
	echo shell_exec('svn update');

    
    list($count) = $GLOBALS['APIDB']->fetchRow($GLOBALS['APIDB']->queryF("SELECT COUNT(*) FROM `fonts` WHERE `processed` > '0' AND `stored` > '0' AND `tagged` > '0'"));
    if ($count != 0) {

        $result = $GLOBALS['APIDB']->queryF("SELECT `id`, `sourceid`, `key` FROM `fonts` WHERE `processed` > '0' AND `stored` > '0' AND `tagged` > '0'");
        $keys = $sources = array();
        while($font = $GLOBALS['APIDB']->fetchArray($result)) {
            $keys[$font['id']] = $font['key'];
            $sources[$font['sourceid']] = $font['id'];
        }
        
        $result = $GLOBALS['APIDB']->queryF("SELECT DISTINCT `alpha` FROM `fonts` WHERE `processed` > '0' AND `stored` > '0' AND `tagged` > '0'");
        $alphas = array();
        while($font = $GLOBALS['APIDB']->fetchArray($result))
            $alphas[] = $font['alpha'];
	sort($alphas);
        $result = $GLOBALS['APIDB']->queryF("SELECT DISTINCT `alpha`, `beta` FROM `fonts` WHERE `processed` > '0' AND `stored` > '0' AND `tagged` > '0'");
        $betas = array();
        while($font = $GLOBALS['APIDB']->fetchArray($result))
            $betas[$font['alpha']][] = $font['beta'];
        $result = $GLOBALS['APIDB']->queryF("SELECT DISTINCT `beta`, `charley` FROM `fonts` WHERE `processed` > '0' AND `stored` > '0' AND `tagged` > '0'");
        $charleys = array();
        while($font = $GLOBALS['APIDB']->fetchArray($result))
            $charleys[$font['beta']][] = $font['charley'];
        
        foreach(array('tags') as $type) {
            if (!is_dir(API_FONTS_JSON . DS . $type))
                mkdir(API_FONTS_JSON . DS . $type, 0777, true);
            foreach($alphas as $alpha) {
                if (!is_dir(API_FONTS_JSON . DS . $type . DS . $alpha))
                    mkdir(API_FONTS_JSON . DS . $type . DS . $alpha, 0777, true);
                foreach($betas[$alpha] as $beta) {
                    if (!is_dir(API_FONTS_JSON . DS . $type . DS . $alpha . DS . $beta))
                        mkdir(API_FONTS_JSON . DS . $type . DS . $alpha . DS . $beta, 0777, true);
                }
            }
        }
        
     $structure = array();
        
        foreach($alphas as $alpha) {
            foreach($betas[$alpha] as $beta) {
                foreach($charleys[$beta] as $charley) {
                    echo "TAGS: Alpha = $alpha; Beta = $beta; Charley = $charley\n";
		    $sql = "SELECT `a`.`key` as `key`, `c`.`tag` as `tag`, `c`.`occured` as `occured` FROM `fonts` as `a` INNER JOIN `tags_links` as `b` ON `a`.`id` = `b`.`fontid` INNER JOIN `tags` as `c` ON `b`.`tagid` = `c`.`id` WHERE `a`.`id` IN (" .implode(", ", array_keys($keys)) . ") AND `a`.`alpha` = '$alpha' AND  `a`.`beta` = '$beta' AND `a`.`charley` = '$charley'";
                    $records = 0;
                    $tags = array();
                    $result = $GLOBALS['APIDB']->queryF($sql);
                    while($tag = $GLOBALS['APIDB']->fetchArray($result)) {
                        $tags[$tag['$key']][$tag['tag']] = $tag;
                        $records++;
                    }
                    file_put_contents($jfile = API_FONTS_JSON . DS . 'tags' . DS . $alpha . DS . $beta . DS . "tags.$charley.json", json_encode($tags));
                    $structure[md5_file($jfile)] = array('bytes' => filesize($jfile), 'records' => $records, 'path' => "/json/tags/$alpha/$beta", 'filename' => basename($jfile), 'meter' => $charley, 'type' => 'tags');
                }
            }
        }
    }
     
    file_put_contents($jfile = API_FONTS_JSON . DS . "tags.structures.json", json_encode($structure));
    file_put_contents($jfile = API_FONTS_JSON . DS . "structures.json", json_encode(array_merge(json_decode(file_get_contents(API_FONTS_JSON . DS . "fonts.structures.json"), true), 
                                                                                                json_decode(file_get_contents(API_FONTS_JSON . DS . "glyphs.structures.json"), true),
                                                                                                json_decode(file_get_contents(API_FONTS_JSON . DS . "files.structures.json"), true),
                                                                                                json_decode(file_get_contents(API_FONTS_JSON . DS . "tags.structures.json"), true),
                                                                                                json_decode(file_get_contents(API_FONTS_JSON . DS . "others.structures.json"), true))));
    chdir(API_FONTS_JSON);
    echo shell_exec('svn add * --force');
/*
        foreach(array('tags') as $type) {
            foreach($alphas as $alpha) {
                foreach($betas[$alpha] as $beta) {
                    if (is_dir(API_FONTS_JSON . DS . $type . DS . $alpha . DS . $beta)) {
                        chdir(API_FONTS_JSON . DS . $type . DS . $alpha . DS . $beta);
                        echo shell_exec(sprintf("svn commit -m '%s'", ucfirst($type) ." Data '/fonts/$alpha/$beta/*.json' ~ JSON Resources: ". date('Y/m/d D H:i:s').''));
                        sleep(mt_rand(7,19));
                     }     
                }
            }
        }

*/
