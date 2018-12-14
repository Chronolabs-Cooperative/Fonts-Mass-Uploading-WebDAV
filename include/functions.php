<?php
/**
 * Chronolabs REST Whois API
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Chronolabs Cooperative http://labs.coop
 * @license         GNU GPL 2 (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * @package         whois
 * @since           1.0.2
 * @author          Simon Roberts <meshy@labs.coop>
 * @version         $Id: functions.php 1000 2013-06-07 01:20:22Z mynamesnot $
 * @subpackage		api
 * @description		Whois API Service REST
 */



// Include Database + Commons
include_once __DIR__ . DIRECTORY_SEPARATOR . 'common.php';


if (!function_exists("checkEmail")) {
    /**
     * checkEmail()
     *
     * @param mixed $email
     * @param mixed $antispam
     * @return bool|mixed
     */
    function checkEmail($email, $antispam = false)
    {
        if (!$email || !preg_match('/^[^@]{1,64}@[^@]{1,255}$/', $email)) {
            return false;
        }
        $email_array      = explode('@', $email);
        $local_array      = explode('.', $email_array[0]);
        $local_arrayCount = count($local_array);
        for ($i = 0; $i < $local_arrayCount; ++$i) {
            if (!preg_match("/^(([A-Za-z0-9!#$%&'*+\/\=?^_`{|}~-][A-Za-z0-9!#$%&'*+\/\=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$/", $local_array[$i])) {
                return false;
            }
        }
        if (!preg_match("/^\[?[0-9\.]+\]?$/", $email_array[1])) {
            $domain_array = explode('.', $email_array[1]);
            if (count($domain_array) < 2) {
                return false; // Not enough parts to domain
            }
            for ($i = 0; $i < count($domain_array); ++$i) {
                if (!preg_match("/^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$/", $domain_array[$i])) {
                    return false;
                }
            }
        }
        if ($antispam) {
            $email = str_replace('@', ' at ', $email);
            $email = str_replace('.', ' dot ', $email);
        }
        
        return $email;
    }
}

if (!function_exists("writeRawFile")) {
    /**
     *
     * @param string $file
     * @param string $data
     */
    function writeRawFile($file = '', $data = '')
    {
        $lineBreak = "\n";
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            $lineBreak = "\r\n";
        }
        if (!is_dir(dirname($file)))
            mkdir(dirname($file), 0777, true);
            if (is_file($file))
                unlink($file);
                $data = str_replace("\n", $lineBreak, $data);
                $ff = fopen($file, 'w');
                fwrite($ff, $data, strlen($data));
                fclose($ff);
    }
}

if (!function_exists("getCompleteFilesListAsArray")) {
	function getCompleteFilesListAsArray($dirname, $result = array())
	{
		foreach(getCompleteDirListAsArray($dirname) as $path)
			foreach(getFileListAsArray($path) as $file)
				$result[$path.DIRECTORY_SEPARATOR.$file] = $path.DIRECTORY_SEPARATOR.$file;
				return $result;
	}

}


if (!function_exists("getCompleteDirListAsArray")) {
	function getCompleteDirListAsArray($dirname, $result = array())
	{
		$result[$dirname] = $dirname;
		foreach(getDirListAsArray($dirname) as $path)
		{
			$result[$dirname . DIRECTORY_SEPARATOR . $path] = $dirname . DIRECTORY_SEPARATOR . $path;
			$result = getCompleteDirListAsArray($dirname . DIRECTORY_SEPARATOR . $path, $result);
		}
		return $result;
	}

}


if (!function_exists("removeEmptyPathFolderList")) {
    function removeEmptyPathFolderList($dirname, $result = array())
    {
        $folders = array_keys(getCompleteDirListAsArray($dirname));
        $result = array();
        sort($folders, SORT_DESC);
        foreach($folders as $path)
        {
            while($path != $dirname) {
                if (rmdir($path))
                    $result[$path] = $path;
                    $path = dirname($path);
            }
        }
        return $result;
    }
    
}

if (!function_exists("getDirListAsArray")) {
	function getDirListAsArray($dirname)
	{
		$ignored = array(
				'cvs' ,
				'_darcs');
		$list = array();
		if (substr($dirname, - 1) != '/') {
			$dirname .= '/';
		}
		if ($handle = opendir($dirname)) {
			while ($file = readdir($handle)) {
				if (substr($file, 0, 1) == '.' || in_array(strtolower($file), $ignored))
					continue;
					if (is_dir($dirname . $file)) {
						$list[$file] = $file;
					}
			}
			closedir($handle);
			asort($list);
			reset($list);
		}

		return $list;
	}
}

if (!function_exists("getFileListAsArray")) {
	function getFileListAsArray($dirname, $prefix = '')
	{
		$filelist = array();
		if (substr($dirname, - 1) == '/') {
			$dirname = substr($dirname, 0, - 1);
		}
		if (is_dir($dirname) && $handle = opendir($dirname)) {
			while (false !== ($file = readdir($handle))) {
				if (! preg_match('/^[\.]{1,2}$/', $file) && is_file($dirname . '/' . $file)) {
					$file = $prefix . $file;
					$filelist[$file] = $file;
				}
			}
			closedir($handle);
			asort($filelist);
			reset($filelist);
		}

		return $filelist;
	}
}

if (!function_exists("cleanWhitespaces")) {
	/**
	 *
	 * @param array $array
	 */
	function cleanWhitespaces($array = array())
	{
		foreach($array as $key => $value)
		{
			if (is_array($value))
				$array[$key] = cleanWhitespaces($value);
				else {
					$array[$key] = trim(str_replace(array("\n", "\r", "\t"), "", $value));
				}
		}
		return $array;
	}
}


if (!function_exists("MakePHPFont")) {
    /**
     * Function for making PHP font for TCPDF and similar applications
     *
     * @param string $fontfile path to font file (TTF, OTF or PFB).
     * @param string $fmfile font metrics file (UFM or AFM).
     * @param boolean $embedded Set to false to not embed the font, true otherwise (default).
     * @param string $enc Name of the encoding table to use. Omit this parameter for TrueType Unicode, OpenType Unicode and symbolic fonts like Symbol or ZapfDingBats.
     * @param array $patch Optional modification of the encoding
     */
    function MakePHPFont($fontfile, $fmfile, $path = "/tmp/", $embedded=true, $enc='cp1252', $patch=array()) {
        //Generate a font definition file
        ini_set('auto_detect_line_endings', '1');
        if (!file_exists($fontfile)) {
            die('Error: file not found: '.$fontfile);
        }
        if (!file_exists($fmfile)) {
            die('Error: file not found: '.$fmfile);
        }
        $cidtogidmap = '';
        $map = array();
        $diff = '';
        $dw = 0; // default width
        $ffext = strtolower(substr($fontfile, -3));
        $fmext = strtolower(substr($fmfile, -3));
        if ($fmext == 'afm') {
            if (($ffext == 'ttf') OR ($ffext == 'otf')) {
                $type = 'TrueType';
            } elseif ($ffext == 'pfb') {
                $type = 'Type1';
            } else {
                die('Error: unrecognized font file extension: '.$ffext);
            }
            if ($enc) {
                $map = ReadMap($enc);
                foreach ($patch as $cc => $gn) {
                    $map[$cc] = $gn;
                }
            }
            $fm = ReadAFM($fmfile, $map);
            if (isset($widths['.notdef'])) {
                $dw = $widths['.notdef'];
            }
            if ($enc) {
                $diff = MakeFontEncoding($map);
            }
            $fd = MakeFontDescriptor($fm, empty($map));
        } elseif ($fmext == 'ufm') {
            $enc = '';
            if (($ffext == 'ttf') OR ($ffext == 'otf')) {
                $type = 'TrueTypeUnicode';
            } else {
                die('Error: not a TrueType font: '.$ffext);
            }
            $fm = ReadUFM($fmfile, $cidtogidmap);
            $dw = $fm['MissingWidth'];
            $fd = MakeFontDescriptor($fm, false);
        }
        //Start generation
        $s = '<?php'."\n";
        $s .= '$type=\''.$type."';\n";
        $s .= '$name=\''.$fm['FontName']."';\n";
        $s .= '$desc='.$fd.";\n";
        if (!isset($fm['UnderlinePosition'])) {
            $fm['UnderlinePosition'] = -100;
        }
        if (!isset($fm['UnderlineThickness'])) {
            $fm['UnderlineThickness'] = 50;
        }
        $s .= '$up='.$fm['UnderlinePosition'].";\n";
        $s .= '$ut='.$fm['UnderlineThickness'].";\n";
        if ($dw <= 0) {
            if (isset($fm['Widths'][32]) AND ($fm['Widths'][32] > 0)) {
                // assign default space width
                $dw = $fm['Widths'][32];
            } else {
                $dw = 600;
            }
        }
        $s .= '$dw='.$dw.";\n";
        $w = MakeWidthArray($fm);
        $s .= '$cw='.$w.";\n";
        $s .= '$enc=\''.$enc."';\n";
        $s .= '$diff=\''.$diff."';\n";
        $basename = substr(basename($fmfile), 0, -4);
        if ($embedded) {
            //Embedded font
            $f = fopen($fontfile,'rb');
            if (!$f) {
                die('Error: Unable to open '.$fontfile);
            }
            $file = fread($f, filesize($fontfile));
            fclose($f);
            if ($type == 'Type1') {
                //Find first two sections and discard third one
                $header = (ord($file{0}) == 128);
                if ($header) {
                    //Strip first binary header
                    $file = substr($file, 6);
                }
                $pos = strpos($file, 'eexec');
                if (!$pos) {
                    die('Error: font file does not seem to be valid Type1');
                }
                $size1 = $pos + 6;
                if ($header AND (ord($file{$size1}) == 128)) {
                    //Strip second binary header
                    $file = substr($file, 0, $size1).substr($file, $size1+6);
                }
                $pos = strpos($file, '00000000');
                if (!$pos) {
                    die('Error: font file does not seem to be valid Type1');
                }
                $size2 = $pos - $size1;
                $file = substr($file, 0, ($size1 + $size2));
            }
            $basename = strtolower($basename);
            $cmp = $path . DIRECTORY_SEPARATOR . $basename.'.z';
            SaveToFile($cmp, gzcompress($file, 9), 'b');
            $s .= '$file=\'' . API_URL . '/v2/font/' . $_GET['clause'] . '/z.api\';'."\n\n";
            $s .= '/* To Execute on a local path download the *.php and the *.z; place in the same folder you can download the resource from:'."\n";
            $s .= '   *.z: ' . API_URL . '/v2/font/' . $_GET['clause'] . '/z.api'."\n";
            $s .= '   *.php: ' . API_URL . '/v2/font/' . $_GET['clause'] . '/php.api'."\n";
            $s .= '   Uncomment line for local file access for embedded *.z font and comment out line above! */'."\n\n";
            $s .= '//$file=__DIR__ . DIRECTORY_SEPARATOR . \''.$basename.'.z\';'."\n";
            if($type == 'Type1') {
                $s .= '$size1='.$size1.";\n";
                $s .= '$size2='.$size2.";\n";
            } else {
                $s.='$originalsize='.filesize($fontfile).";\n";
            }
        } else {
            //Not embedded font
            $s .= '$file='."'';\n";
        }
        $s .= "?>";
        SaveToFile($path . DIRECTORY_SEPARATOR . $basename.'.php',$s);
        //print "Font definition file generated (".$basename.".php)\n";
    }
}

if (!function_exists("ReadMap")) {
    /**
     * Read the specified encoding map.
     * @param string $enc map name (see /enc/ folder for valid names).
     */
    function ReadMap($enc) {
        //Read a map file
        $file = __DIR__.'/data/enc/'.strtolower($enc).'.map';
        $a = file($file);
        if (empty($a)) {
            die('Error: encoding not found: '.$enc);
        }
        $cc2gn = array();
        foreach ($a as $l) {
            if ($l{0} == '!') {
                $e = preg_split('/[ \\t]+/',rtrim($l));
                $cc = hexdec(substr($e[0],1));
                $gn = $e[2];
                $cc2gn[$cc] = $gn;
            }
        }
        for($i = 0; $i <= 255; $i++) {
            if(!isset($cc2gn[$i])) {
                $cc2gn[$i] = '.notdef';
            }
        }
        return $cc2gn;
    }
}

if (!function_exists("ReadUFM")) {
    /**
     * Read UFM file
     *
     * @param $file string
     * @param $cidtogidmap array
     */
    function ReadUFM($file, &$cidtogidmap) {
        //Prepare empty CIDToGIDMap
        $cidtogidmap = str_pad('', (256 * 256 * 2), "\x00");
        //Read a font metric file
        $a = file($file);
        if (empty($a)) {
            die('File not found');
        }
        $widths = array();
        $fm = array();
        foreach($a as $l) {
            $e = explode(' ',chop($l));
            if(count($e) < 2) {
                continue;
            }
            $code = $e[0];
            $param = $e[1];
            if($code == 'U') {
                // U 827 ; WX 0 ; N squaresubnosp ; G 675 ;
                //Character metrics
                $cc = (int)$e[1];
                if ($cc != -1) {
                    $gn = $e[7];
                    $w = $e[4];
                    $glyph = $e[10];
                    $widths[$cc] = $w;
                    if($cc == ord('X')) {
                        $fm['CapXHeight'] = $e[13];
                    }
                    // Set GID
                    if (($cc >= 0) AND ($cc < 0xFFFF) AND $glyph) {
                        $cidtogidmap{($cc * 2)} = chr($glyph >> 8);
                        $cidtogidmap{(($cc * 2) + 1)} = chr($glyph & 0xFF);
                    }
                }
                if(($gn == '.notdef') AND (!isset($fm['MissingWidth']))) {
                    $fm['MissingWidth'] = $w;
                }
            } elseif($code == 'FontName') {
                $fm['FontName'] = $param;
            } elseif($code == 'Weight') {
                $fm['Weight'] = $param;
            } elseif($code == 'ItalicAngle') {
                $fm['ItalicAngle'] = (double)$param;
            } elseif($code == 'Ascender') {
                $fm['Ascender'] = (int)$param;
            } elseif($code == 'Descender') {
                $fm['Descender'] = (int)$param;
            } elseif($code == 'UnderlineThickness') {
                $fm['UnderlineThickness'] = (int)$param;
            } elseif($code == 'UnderlinePosition') {
                $fm['UnderlinePosition'] = (int)$param;
            } elseif($code == 'IsFixedPitch') {
                $fm['IsFixedPitch'] = ($param == 'true');
            } elseif($code == 'FontBBox') {
                $fm['FontBBox'] = array($e[1], $e[2], $e[3], $e[4]);
            } elseif($code == 'CapHeight') {
                $fm['CapHeight'] = (int)$param;
            } elseif($code == 'StdVW') {
                $fm['StdVW'] = (int)$param;
            }
        }
        if(!isset($fm['MissingWidth'])) {
            $fm['MissingWidth'] = 600;
        }
        if(!isset($fm['FontName'])) {
            die('FontName not found');
        }
        $fm['Widths'] = $widths;
        return $fm;
    }
}

if (!function_exists("ReadAFM")) {
    /**
     * Read AFM file
     *
     * @param $file string
     * @param $map array
     */
    function ReadAFM($file,&$map) {
        //Read a font metric file
        $a = file($file);
        if(empty($a)) {
            die('File not found');
        }
        $widths = array();
        $fm = array();
        $fix = array(
            'Edot'=>'Edotaccent',
            'edot'=>'edotaccent',
            'Idot'=>'Idotaccent',
            'Zdot'=>'Zdotaccent',
            'zdot'=>'zdotaccent',
            'Odblacute' => 'Ohungarumlaut',
            'odblacute' => 'ohungarumlaut',
            'Udblacute'=>'Uhungarumlaut',
            'udblacute'=>'uhungarumlaut',
            'Gcedilla'=>'Gcommaaccent'
            ,'gcedilla'=>'gcommaaccent',
            'Kcedilla'=>'Kcommaaccent',
            'kcedilla'=>'kcommaaccent',
            'Lcedilla'=>'Lcommaaccent',
            'lcedilla'=>'lcommaaccent',
            'Ncedilla'=>'Ncommaaccent',
            'ncedilla'=>'ncommaaccent',
            'Rcedilla'=>'Rcommaaccent',
            'rcedilla'=>'rcommaaccent',
            'Scedilla'=>'Scommaaccent',
            'scedilla'=>'scommaaccent',
            'Tcedilla'=>'Tcommaaccent',
            'tcedilla'=>'tcommaaccent',
            'Dslash'=>'Dcroat',
            'dslash'=>'dcroat',
            'Dmacron'=>'Dcroat',
            'dmacron'=>'dcroat',
            'combininggraveaccent'=>'gravecomb',
            'combininghookabove'=>'hookabovecomb',
            'combiningtildeaccent'=>'tildecomb',
            'combiningacuteaccent'=>'acutecomb',
            'combiningdotbelow'=>'dotbelowcomb',
            'dongsign'=>'dong'
        );
        foreach($a as $l) {
            $e = explode(' ', rtrim($l));
            if (count($e) < 2) {
                continue;
            }
            $code = $e[0];
            $param = $e[1];
            if ($code == 'C') {
                //Character metrics
                $cc = (int)$e[1];
                $w = $e[4];
                $gn = $e[7];
                if (substr($gn, -4) == '20AC') {
                    $gn = 'Euro';
                }
                if (isset($fix[$gn])) {
                    //Fix incorrect glyph name
                    foreach ($map as $c => $n) {
                        if ($n == $fix[$gn]) {
                            $map[$c] = $gn;
                        }
                    }
                }
                if (empty($map)) {
                    //Symbolic font: use built-in encoding
                    $widths[$cc] = $w;
                } else {
                    $widths[$gn] = $w;
                    if($gn == 'X') {
                        $fm['CapXHeight'] = $e[13];
                    }
                }
                if($gn == '.notdef') {
                    $fm['MissingWidth'] = $w;
                }
            } elseif($code == 'FontName') {
                $fm['FontName'] = $param;
            } elseif($code == 'Weight') {
                $fm['Weight'] = $param;
            } elseif($code == 'ItalicAngle') {
                $fm['ItalicAngle'] = (double)$param;
            } elseif($code == 'Ascender') {
                $fm['Ascender'] = (int)$param;
            } elseif($code == 'Descender') {
                $fm['Descender'] = (int)$param;
            } elseif($code == 'UnderlineThickness') {
                $fm['UnderlineThickness'] = (int)$param;
            } elseif($code == 'UnderlinePosition') {
                $fm['UnderlinePosition'] = (int)$param;
            } elseif($code == 'IsFixedPitch') {
                $fm['IsFixedPitch'] = ($param == 'true');
            } elseif($code == 'FontBBox') {
                $fm['FontBBox'] = array($e[1], $e[2], $e[3], $e[4]);
            } elseif($code == 'CapHeight') {
                $fm['CapHeight'] = (int)$param;
            } elseif($code == 'StdVW') {
                $fm['StdVW'] = (int)$param;
            }
        }
        if (!isset($fm['FontName'])) {
            die('FontName not found');
        }
        if (!empty($map)) {
            if (!isset($widths['.notdef'])) {
                $widths['.notdef'] = 600;
            }
            if (!isset($widths['Delta']) AND isset($widths['increment'])) {
                $widths['Delta'] = $widths['increment'];
            }
            //Order widths according to map
            for ($i = 0; $i <= 255; $i++) {
                if (!isset($widths[$map[$i]])) {
                    //print "Warning: character ".$map[$i]." is missing\n";
                    $widths[$i] = $widths['.notdef'];
                } else {
                    $widths[$i] = $widths[$map[$i]];
                }
            }
        }
        $fm['Widths'] = $widths;
        return $fm;
    }
}

if (!function_exists("MakeFontDescriptor")) {
    /**
     * Makes font description header
     *
     * @param $fm array
     * @param $symbolic boolean
     */
    function MakeFontDescriptor($fm, $symbolic=false) {
        //Ascent
        $asc = (isset($fm['Ascender']) ? $fm['Ascender'] : 1000);
        $fd = "array('Ascent'=>".$asc;
        //Descent
        $desc = (isset($fm['Descender']) ? $fm['Descender'] : -200);
        $fd .= ",'Descent'=>".$desc;
        //CapHeight
        if (isset($fm['CapHeight'])) {
            $ch = $fm['CapHeight'];
        } elseif (isset($fm['CapXHeight'])) {
            $ch = $fm['CapXHeight'];
        } else {
            $ch = $asc;
        }
        $fd .= ",'CapHeight'=>".$ch;
        //Flags
        $flags = 0;
        if (isset($fm['IsFixedPitch']) AND $fm['IsFixedPitch']) {
            $flags += 1<<0;
        }
        if ($symbolic) {
            $flags += 1<<2;
        } else {
            $flags += 1<<5;
        }
        if (isset($fm['ItalicAngle']) AND ($fm['ItalicAngle'] != 0)) {
            $flags += 1<<6;
        }
        $fd .= ",'Flags'=>".$flags;
        //FontBBox
        if (isset($fm['FontBBox'])) {
            $fbb = $fm['FontBBox'];
        } else {
            $fbb = array(0, ($desc - 100), 1000, ($asc + 100));
        }
        $fd .= ",'FontBBox'=>'[".$fbb[0].' '.$fbb[1].' '.$fbb[2].' '.$fbb[3]."]'";
        //ItalicAngle
        $ia = (isset($fm['ItalicAngle']) ? $fm['ItalicAngle'] : 0);
        $fd .= ",'ItalicAngle'=>".$ia;
        //StemV
        if (isset($fm['StdVW'])) {
            $stemv = $fm['StdVW'];
        } elseif (isset($fm['Weight']) && preg_match('(bold|black)', $fm['Weight'])) {
            $stemv = 120;
        } else {
            $stemv = 70;
        }
        $fd .= ",'StemV'=>".$stemv;
        //MissingWidth
        if(isset($fm['MissingWidth'])) {
            $fd .= ",'MissingWidth'=>".$fm['MissingWidth'];
        }
        $fd .= ')';
        return $fd;
    }
}

if (!function_exists("MakeWidthArray")) {
    /**
     * Makes Widths Array for Font
     *
     * @param array $fm
     */
    function MakeWidthArray($fm) {
        //Make character width array
        $s = 'array(';
        $cw = $fm['Widths'];
        $els = array();
        $c = 0;
        foreach ($cw as $i => $w) {
            if (is_numeric($i)) {
                $els[] = (((($c++)%10) == 0) ? "\n" : '').$i.'=>'.$w;
            }
        }
        $s .= implode(',', $els);
        $s .= ')';
        return $s;
    }
}

if (!function_exists("MakeFontEncoding")) {
    /**
     * Makes a Font Encoding Mapping References
     *
     * @param array $map
     */
    function MakeFontEncoding($map) {
        //Build differences from reference encoding
        $ref = ReadMap('cp1252');
        $s = '';
        $last = 0;
        for ($i = 32; $i <= 255; $i++) {
            if ($map[$i] != $ref[$i]) {
                if ($i != $last+1) {
                    $s .= $i.' ';
                }
                $last = $i;
                $s .= '/'.$map[$i].' ';
            }
        }
        return rtrim($s);
    }
}

if (!function_exists("SaveToFile")) {
    /**
     * Writes a file to the filebase
     *
     * @param string $file
     * @param string $s
     * @param string $mode
     */
    function SaveToFile($file, $s, $mode='t') {
        $f = fopen($file, 'w'.$mode);
        if(!$f) {
            die('Can\'t write to file '.$file);
        }
        fwrite($f, $s, strlen($s));
        fclose($f);
    }
}

if (!function_exists("ReadShort")) {
    /**
     * Read's Short Data from File Via Unpack
     *
     * @param string $f
     */
    function ReadShort($f) {
        $a = unpack('n1n', fread($f, 2));
        return $a['n'];
    }
}

if (!function_exists("ReadLong")) {
    /**
     * Reads Long Data from File
     *
     * @param string $f
     */
    function ReadLong($f) {
        $a = unpack('N1N', fread($f, 4));
        return $a['N'];
    }
}

if (!function_exists("putRawFile")) {
    /**
     * Saves a Raw File to the Filebase
     *
     * @param string $file
     * @param string $data
     *
     * @return boolean
     */
    function putRawFile($file = '', $data = '')
    {
        $lineBreak = "\n";
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            $lineBreak = "\r\n";
        }
        if (!is_dir(dirname($file)))
            if (strpos(' '.$file, FONTS_CACHE))
                mkdirSecure(dirname($file), 0777, true);
                else
                    mkdir(dirname($file), 0777, true);
                    elseif (strpos(' '.$file, FONTS_CACHE) && !file_exists(FONTS_CACHE . DIRECTORY_SEPARATOR . '.htaccess'))
                    SaveToFile(FONTS_CACHE . DIRECTORY_SEPARATOR . '.htaccess', "<Files ~ \"^.*$\">\n\tdeny from all\n</Files>");
                    if (is_file($file))
                        unlink($file);
                        return SaveToFile($file, $data);
    }
}


if (!function_exists("xml2array")) {
    /**
     * Function to convert XML to Array in PHP
     *
     * @param unknown $contents
     * @param number $get_attributes
     * @param string $priority
     */
    function xml2array($contents, $get_attributes=1, $priority = 'tag') {
        if(!$contents) return array();
        
        if(!function_exists('xml_parser_create')) {
            return array();
        }
        
        //Get the XML parser of PHP - PHP must have this module for the parser to work
        $parser = xml_parser_create('');
        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); # http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, trim($contents), $xml_values);
        xml_parser_free($parser);
        
        if(!$xml_values) return;//Hmm...
        
        //Initializations
        $xml_array = array();
        $parents = array();
        $opened_tags = array();
        $arr = array();
        
        $current = &$xml_array; //Refference
        
        //Go through the tags.
        $repeated_tag_index = array();//Multiple tags with same name will be turned into an array
        foreach($xml_values as $data) {
            unset($attributes,$value);//Remove existing values, or there will be trouble
            
            //This command will extract these variables into the foreach scope
            // tag(string), type(string), level(int), attributes(array).
            extract($data);//We could use the array by itself, but this cooler.
            
            $result = array();
            $attributes_data = array();
            
            if(isset($value)) {
                if($priority == 'tag') $result = $value;
                else $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode
            }
            
            //Set the attributes too.
            if(isset($attributes) and $get_attributes) {
                foreach($attributes as $attr => $val) {
                    if($priority == 'tag') $attributes_data[$attr] = $val;
                    else $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
                }
            }
            
            //See tag status and do the needed.
            if($type == "open") {//The starting of the tag '<tag>'
                $parent[$level-1] = &$current;
                if(!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
                    $current[$tag] = $result;
                    if($attributes_data) $current[$tag. '_attr'] = $attributes_data;
                    $repeated_tag_index[$tag.'_'.$level] = 1;
                    
                    $current = &$current[$tag];
                    
                } else { //There was another element with the same tag name
                    
                    if(isset($current[$tag][0])) {//If there is a 0th element it is already an array
                        $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
                        $repeated_tag_index[$tag.'_'.$level]++;
                    } else {//This section will make the value an array if multiple tags with the same name appear together
                        $current[$tag] = array($current[$tag],$result);//This will combine the existing item and the new item together to make an array
                        $repeated_tag_index[$tag.'_'.$level] = 2;
                        
                        if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
                            $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                            unset($current[$tag.'_attr']);
                        }
                        
                    }
                    $last_item_index = $repeated_tag_index[$tag.'_'.$level]-1;
                    $current = &$current[$tag][$last_item_index];
                }
                
            } elseif($type == "complete") { //Tags that ends in 1 line '<tag />'
                //See if the key is already taken.
                if(!isset($current[$tag])) { //New Key
                    $current[$tag] = $result;
                    $repeated_tag_index[$tag.'_'.$level] = 1;
                    if($priority == 'tag' and $attributes_data) $current[$tag. '_attr'] = $attributes_data;
                    
                } else { //If taken, put all things inside a list(array)
                    if(isset($current[$tag][0]) and is_array($current[$tag])) {//If it is already an array...
                        
                        // ...push the new element into that array.
                        $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
                        
                        if($priority == 'tag' and $get_attributes and $attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
                        }
                        $repeated_tag_index[$tag.'_'.$level]++;
                        
                    } else { //If it is not an array...
                        $current[$tag] = array($current[$tag],$result); //...Make it an array using using the existing value and the new value
                        $repeated_tag_index[$tag.'_'.$level] = 1;
                        if($priority == 'tag' and $get_attributes) {
                            if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
                                
                                $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                                unset($current[$tag.'_attr']);
                            }
                            
                            if($attributes_data) {
                                $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
                            }
                        }
                        $repeated_tag_index[$tag.'_'.$level]++; //0 and 1 index is already taken
                    }
                }
                
            } elseif($type == 'close') { //End of tag '</tag>'
                $current = &$parent[$level-1];
            }
        }
        
        return($xml_array);
    }
}

if (!function_exists("getGlyphArrayFromXML")) {
    /**
     * Gets Glyph Array from XML Array for *.glif files
     *
     * @param array $glyph
     *
     * @return array
     */
    function getGlyphArrayFromXML( $glyph = array() ) {
        $ret = array();
        $ret['width'] = $glyph['glyph']['advance_attr']['width'];
        $ret['unicode'] = $glyph['glyph']['unicode_attr']['hex'];
        $ret['name'] = $glyph['glyph_attr']['name'];
        $ret['format'] = $glyph['glyph_attr']['format'];
        $ret['contours'] = array();
        foreach($glyph['glyph']['outline']['contour'] as $index => $contour)
            foreach($contour['point'] as $weight => $values)
            {
                if (is_string($weight) && !is_numeric($weight))
                {
                    $weight = (integer)str_replace('_attr', '', $weight);
                    $ret['contours'][$index][$weight] = array("x"=>$values['x'], "y"=>$values['y'], 'type'=>(!isset($values['type'])?'-----':$values['type']), 'smooth'=>(!isset($values['smooth'])?'-----':$values['smooth']));
                }
            }
        $ret['fingerprint'] = sha1(json_encode($ret['contours']));
        return $ret;
    }
}

if (!function_exists('sef'))
{
    /**
     * Safe encoded paths elements
     *
     * @param unknown $datab
     * @param string $char
     *
     * @return string
     */
    function sef($value = '', $stripe ='-')
    {
        return(strtolower(getOnlyAlpha($result, $stripe)));
    }
}


if (!function_exists('getOnlyAlpha'))
{
    /**
     * Safe encoded paths elements
     *
     * @param unknown $datab
     * @param string $char
     *
     * @return string
     */
    function getOnlyAlpha($value = '', $stripe ='-')
    {
        $value = str_replace('&', 'and', $value);
        $value = str_replace(array("'", '"', "`"), 'tick', $value);
        $replacement_chars = array();
        $accepted = array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","m","o","p","q",
            "r","s","t","u","v","w","x","y","z","0","9","8","7","6","5","4","3","2","1");
        for($i=0;$i<256;$i++){
            if (!in_array(strtolower(chr($i)),$accepted))
                $replacement_chars[] = chr($i);
        }
        $result = trim(str_replace($replacement_chars, $stripe, ($value)));
        while(strpos($result, $stripe.$stripe, 0))
            $result = (str_replace($stripe.$stripe, $stripe, $result));
            while(substr($result, 0, strlen($stripe)) == $stripe)
                $result = substr($result, strlen($stripe), strlen($result) - strlen($stripe));
                while(substr($result, strlen($result) - strlen($stripe), strlen($stripe)) == $stripe)
                    $result = substr($result, 0, strlen($result) - strlen($stripe));
                    return($result);
    }
}


if (!function_exists("getFontPreviewText")) {
    /**
     * gets random preview text for font preview
     *
     * @return string
     */
    function getFontPreviewText()
    {
        static $text = '';
        if (empty($text))
        {
            $texts = cleanWhitespaces(file(__DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'preview-texts.diz'));
            shuffle($texts); shuffle($texts); shuffle($texts); shuffle($texts);
            if (count($_SESSION['previewtxt'])>0 && count($_SESSION['previewtxt']) < count($texts))
            {
                foreach($texts as $key => $txt)
                    if (in_array($txt, $_SESSION['previewtxt']))
                        unset($texts[$key]);
            } elseif(count($_SESSION['previewtxt'])==0 && count($_SESSION['previewtxt']) == count($texts)) {
                $_SESSION['previewtxt'] = array();
            }
            $attempts = 0;
            while(empty($text) && !in_array($text, $_SESSION['previewtxt']) || $attempts < 10)
            {
                $attempts++;
                $text = $texts[mt_rand(0, count($texts)-1)];
            }
            $_SESSION['previewtxt'][] = $text;
        }
        return $text;
    }
}

if (!function_exists("deleteFilesNotListedByArray")) {
    /**
     * deletes all files and folders contained within the path passed which do not match the array for file skipping
     *
     * @param string $dirname
     * @param array $skipped
     *
     * @return array
     */
    function deleteFilesNotListedByArray($dirname, $skipped = array())
    {
        $deleted = array();
        foreach(array_reverse(getCompleteFilesListAsArray($dirname)) as $file)
        {
            $found = false;
            foreach($skipped as $skip)
                if (strtolower(substr($file, strlen($file)-strlen($skip)))==strtolower($skip))
                    $found = true;
                    if ($found == false)
                    {
                        if (unlink($file))
                        {
                            $deleted[str_replace($dirname, "", dirname($file))][] = basename($file);
                            if (dirname($file)!=API_FONTS_WEBDAV)
                                rmdir(dirname($file));
                        }
                    }
        }
        return $deleted;
    }
    
}

if (!function_exists("getCompleteFilesListAsArray")) {
    /**
     * Get a complete file listing for a folder and sub-folder
     *
     * @param string $dirname
     * @param string $remove
     *
     * @return array
     */
    function getCompleteFilesListAsArray($dirname, $remove = '')
    {
        foreach(getCompleteDirListAsArray($dirname) as $path)
            foreach(getFileListAsArray($path) as $file)
                $result[str_replace($remove, '', $path.DIRECTORY_SEPARATOR.$file)] = str_replace($remove, '', $path.DIRECTORY_SEPARATOR.$file);
                return $result;
    }
    
}


if (!function_exists("getCompleteDirListAsArray")) {
    /**
     * Get a complete folder/directory listing for a folder and sub-folder
     *
     * @param string $dirname
     * @param array $result
     *
     * @return array
     */
    function getCompleteDirListAsArray($dirname, $result = array())
    {
        $result[$dirname] = $dirname;
        foreach(getDirListAsArray($dirname) as $path)
        {
            $result[$dirname . DIRECTORY_SEPARATOR . $path] = $dirname . DIRECTORY_SEPARATOR . $path;
            $result = getCompleteDirListAsArray($dirname . DIRECTORY_SEPARATOR . $path, $result);
        }
        return $result;
    }
    
}

if (!function_exists("getCompletePacksListAsArray")) {
    /**
     * Get a complete all packed archive supported for a folder and sub-folder
     *
     * @param string $dirname
     * @param array $result
     *
     * @return array
     */
    function getCompletePacksListAsArray($dirname, $result = array())
    {
        foreach(getCompleteDirListAsArray($dirname) as $path)
        {
            foreach(getPacksListAsArray($path) as $file=>$values)
                $result[$values['type']][md5_file( $path . DIRECTORY_SEPARATOR . $values['file'])] =  $path . DIRECTORY_SEPARATOR . $values['file'];
        }
        return $result;
    }
}

if (!function_exists("getCompleteFontsListAsArray")) {
    /**
     * Get a complete all font files supported for a folder and sub-folder
     *
     * @param string $dirname
     * @param array $result
     *
     * @return array
     */
    function getCompleteFontsListAsArray($dirname, $result = array())
    {
        foreach(getCompleteDirListAsArray($dirname) as $path)
        {
            foreach(getFontsListAsArray($path) as $file=>$values)
                $result[$values['type']][md5_file($path . DIRECTORY_SEPARATOR . $values['file'])] = $path . DIRECTORY_SEPARATOR . $values['file'];
        }
        return $result;
    }
}

if (!function_exists("getDirListAsArray")) {
    /**
     * Get a folder listing for a single path no recursive
     *
     * @param string $dirname
     *
     * @return array
     */
    function getDirListAsArray($dirname)
    {
        $ignored = array(
            'cvs' ,
            '_darcs', '.git', '.svn');
        $list = array();
        if (substr($dirname, - 1) != '/') {
            $dirname .= '/';
        }
        if ($handle = opendir($dirname)) {
            while ($file = readdir($handle)) {
                if (substr($file, 0, 1) == '.' || in_array(strtolower($file), $ignored))
                    continue;
                    if (is_dir($dirname . $file)) {
                        $list[$file] = $file;
                    }
            }
            closedir($handle);
            asort($list);
            reset($list);
        }
        return $list;
    }
}

if (!function_exists("getFileListAsArray")) {
    /**
     * Get a file listing for a single path no recursive
     *
     * @param string $dirname
     * @param string $prefix
     *
     * @return array
     */
    function getFileListAsArray($dirname, $prefix = '')
    {
        $filelist = array();
        if (substr($dirname, - 1) == '/') {
            $dirname = substr($dirname, 0, - 1);
        }
        if (is_dir($dirname) && $handle = opendir($dirname)) {
            while (false !== ($file = readdir($handle))) {
                if (! preg_match('/^[\.]{1,2}$/', $file) && is_file($dirname . '/' . $file)) {
                    $file = $prefix . $file;
                    $filelist[$file] = $file;
                }
            }
            closedir($handle);
            asort($filelist);
            reset($filelist);
        }
        return $filelist;
    }
}

if (!function_exists("get7zListAsArray")) {
    /**
     * Get a zip file listing for a single path no recursive
     *
     * @param string $dirname
     * @param string $prefix
     *
     * @return array
     */
    function get7zListAsArray($dirname, $prefix = '')
    {
        $filelist = array();
        if ($handle = opendir($dirname)) {
            while (false !== ($file = readdir($handle))) {
                if (preg_match('/(\.7z)$/i', $file)) {
                    $file = $prefix . $file;
                    $filelist[$file] = $file;
                }
            }
            closedir($handle);
            asort($filelist);
            reset($filelist);
        }
        return $filelist;
    }
}


if (!function_exists("getPacksListAsArray")) {
    /**
     * Get a compressed archives file listing for a single path no recursive
     *
     * @param string $dirname
     * @param string $prefix
     *
     * @return array
     */
    function getPacksListAsArray($dirname, $prefix = '')
    {
        $packs = cleanWhitespaces(file(__DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'packs-converted.diz'));
        $filelist = array();
        if ($handle = opendir($dirname)) {
            while (false !== ($file = readdir($handle))) {
                foreach($packs as $pack)
                    if (substr(strtolower($file), strlen($file)-strlen(".".$pack)) == strtolower(".".$pack)) {
                        $file = $prefix . $file;
                        $filelist[$file] = array('file'=>$file, 'type'=>$pack);
                    }
            }
            closedir($handle);
        }
        return $filelist;
    }
}

if (!function_exists("getExtractionShellExec")) {
    /**
     * Get a bash shell execution command for extracting archives
     *
     * @return array
     */
    function getExtractionShellExec()
    {
        $ret = array();
        foreach(cleanWhitespaces(file(__DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'packs-extracting.diz')) as $values)
        {
            $parts = explode("||", $values);
            $ret[$parts[0]] = $parts[1];
        }
        return $ret;
    }
}

if (!function_exists("getFontsListAsArray")) {
    /**
     * Get a font files listing for a single path no recursive
     *
     * @param string $dirname
     * @param string $prefix
     *
     * @return array
     */
    function getFontsListAsArray($dirname, $prefix = '')
    {
        $formats = cleanWhitespaces(file(__DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'font-converted.diz'));
        $filelist = array();
        
        if ($handle = opendir($dirname)) {
            while (false !== ($file = readdir($handle))) {
                foreach($formats as $format)
                    if (substr(strtolower($file), strlen($file)-strlen(".".$format)) == strtolower(".".$format)) {
                        $file = $prefix . $file;
                        $filelist[$file] = array('file'=>$file, 'type'=>$format);
                    }
            }
            closedir($handle);
        }
        return $filelist;
    }
}

if (!function_exists("getStampingShellExec")) {
    /**
     * Get a bash shell execution command for stamping archives
     *
     * @return array
     */
    function getStampingShellExec()
    {
        $ret = array();
        foreach(cleanWhitespaces(file(__DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'packs-stamping.diz')) as $values)
        {
            $parts = explode("||", $values);
            $ret[$parts[0]] = $parts[1];
        }
        return $ret;
    }
}

if (!function_exists("getArchivingShellExec")) {
    /**
     * Get a bash shell execution command for creating archives
     *
     * @return array
     */
    function getArchivingShellExec()
    {
        $ret = array();
        foreach(cleanWhitespaces(file(__DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'packs-archiving.diz')) as $values)
        {
            $parts = explode("||", $values);
            $ret[$parts[0]] = $parts[1];
        }
        return $ret;
    }
}

