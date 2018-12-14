<?php
/**
 * API constants file
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       (c) 2000-2016 API Project (www.api.org)
 * @license             GNU GPL 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 */

// API URL's for Functions
define('API_FONTS_SOURCES','/mnt/fonts/fonts-storage');
define('API_FONTS_STAGING','/mnt/fonts/fonts-staging');
define('API_FONTS_JSON', '/fonts/fonts-json');
define('API_FONTS_WEBDAV', '/mnt/fonts/dav.fonts4web.org.uk');
define('API_FONTS_STAGER', 'svn import -m "%s" --force "%s" "https://svn.code.sf.net/p/chronolabs-cooperative/fonts/%s"');
define('API_FONTS_SVNRAW', 'https://svn.code.sf.net/p/chronolabs-cooperative/fonts/%s');
define('API_FONTS_SVNADD', 'svn add * --force');
define('API_FONTS_COMMIT', 'svn commit -m "%s"');
