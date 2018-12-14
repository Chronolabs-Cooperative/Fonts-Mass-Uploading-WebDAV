<?php
   
    require __DIR__ . DIRECTORY_SEPARATOR . 'mainfile.php';
    require __DIR__ . DS . 'class' . DS . 'apilists.php';

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
        
    chdir(API_FONTS_JSON);
    echo shell_exec('svn add * --force');
        foreach(array('fonts', 'files', 'glyphs', 'tags') as $type) {
            foreach($alphas as $alpha) {
                foreach($betas[$alpha] as $beta) {
                    if (is_dir(API_FONTS_JSON . DS . $type . DS . $alpha . DS . $beta) && strlen($alpha) == 1 && strlen($beta) == 2) {
                        chdir(API_FONTS_JSON . DS . $type . DS . $alpha . DS . $beta);
			echo "\n\n\nCOMMIT: " .  ucfirst($type) ." Data '/$type/$alpha/$beta/*.json' ~ JSON Resources: ". date('Y/m/d D H:i:s');
			$out = array();
                        exec(sprintf("svn commit -m '%s'", ucfirst($type) ." Data '/$type/$alpha/$beta/*.json' ~ JSON Resources: ". date('Y/m/d D H:i:s')), $out);
			if (count($out) > 1) {
				echo "\n\n\t" . import("\n\t", $out);
				if (strpos(import("\n\t", $out), "is not known to exist in the repository")>0) {
					$out = array();
					foreach(APILists::getFileListAsArray(API_FONTS_JSON . DS . $type . DS . $alpha . DS . $beta) as $jsonfile) {
					    exec(sprintf(API_FONTS_STAGER, ucfirst($type) ." Data '/$type/$alpha/$beta/$jsonfile' ~ JSON Resources: ". date('Y/m/d D H:i:s'), API_FONTS_JSON . DS . $type . DS . $alpha . DS . $beta . DS . $jsonfile, "json" . DS . $type . DS . $alpha . DS . $beta . DS . $jsonfile), $out);
    					echo "\n\n\t" . import("\n\t", $out);
					}
				}
                                $wait = mt_rand(7,19);
                                echo "\n\nwaiting for $wait seconds for next commital!";
                                sleep($wait);
			}
		 	chdir(API_FONTS_JSON);
    			echo shell_exec('svn cleanup');
		   }
                }
            }

        }

}
chdir(API_FONTS_JSON);
echo shell_exec(sprintf("svn commit -m '%s'", "Root Commit of Data JSON Resources: ". date('Y/m/d D H:i:s')));

