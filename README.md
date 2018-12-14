## Chronolabs Cooperative presents

# Fonts On Mass Uploading with Network Drive (WebDAV)

#### Demo: https://dav.fonts4web.org.uk

### Author: Dr. Simon Antony Roberts <simon@ordinance.space>

This is an example of how the SVN is utilised with WebDAV to do mass uploading and cache storage on SVN; it utilised and design to run on ubuntu/debian services and will allow you to mass upload to webdav and then file and cache on subversion (svn)!

# Setting Up the environment in Ubuntu/Debian

There is a couple of extensions you will require for this API to run you need to execute the following at your terminal bash shell to have the modules installed before installation on debian/ubuntu

    $ sudo apt-get install rar* p7zip-full unace unrar* zip unzip sharutils sharutils uudeview mpack arj cabextract file-roller fontforge tasksel nano bzip2 -y
    

# Setting up WebDAV with Apache 2.x

The following script goes in your /etc/apache2/sites-available path it can be called after your hostname of the webdav in this example it is: **dav.fonts4web.org.uk.conf** file; the actual area of the WebDAV that is where the files are written to is as follows **/mnt/fonts/dav.fonts4web.org.uk** this is also the constant in **include/constants.php** of **API_FONTS_WEBDAV**.

    # This is for Standard Web Access for HTTP://
    <VirtualHost *:80>
        ServerName dav.fonts4web.org.uk
        ServerAdmin wishcraft@users.sourceforge.net
        DocumentRoot /mnt/fonts/dav.fonts4web.org.uk
        ErrorLog /var/log/apache2/dav.fonts4web.org.uk-error.log
        CustomLog /var/log/apache2/dav.fonts4web.org.uk-access.log common
        <Directory /mnt/fonts/dav.fonts4web.org.uk>
            # You Change here if you want to have username/password with htpasswd part of the
            # apache utilities and specify and change this section to support http username/password
            DAV On
            Options Indexes FollowSymLinks MultiViews
            AllowOverride All
            Require all granted
        </Directory>
    </VirtualHost>
    
    # This is for SSL Access for HTTPS://
    <VirtualHost *:443>
        ServerName dav.fonts4web.org.uk
        ServerAdmin wishcraft@users.sourceforge.net
        DocumentRoot /mnt/fonts/dav.fonts4web.org.uk
        ErrorLog /var/log/apache2/dav.fonts4web.org.uk-error.log
        CustomLog /var/log/apache2/dav.fonts4web.org.uk-access.log common
        SSLEngine on
        SSLCertificateFile /fonts/fonts4web.org.uk.crt
        SSLCertificateKeyFile /fonts/fonts4web.org.uk.key.crt
        SSLCertificateChainFile /fonts/fonts4web.org.uk.ca.crt
        <Directory /mnt/fonts/dav.fonts4web.org.uk>
        		  # You Change here if you want to have username/password with htpasswd part of the
        		  # apache utilities and specify and change this section to support http username/password
                DAV On
                Options Indexes FollowSymLinks MultiViews
                AllowOverride All
                Require all granted
        </Directory>
    </VirtualHost>  

This is the file settings and user permission to set the WebDAV file path as

    $ sudo chown -Rf www-data:www-data /mnt/fonts/dav.fonts4web.org.uk
    $ sudo chmod -Rf 0777 /mnt/fonts/dav.fonts4web.org.uk
    
To Turn on the apache module for dav with apache run the following:

    $ sudo a2enmod dav
    $ sudo a2enmod dav_fs
    $ sudo service apache2 restart

To Enable the WebDav for resolution with apache run the following:

    $ sudo a2ensite dav.fonts4web.org.uk
    $ sudo service apache2 reload

# Setting Up the MySQL Database

Setting up the MySQL Database is relatively easy there is a configuration file for the settings for the PHP in **include/dbconfig.php** as well as other details like an sql create tables \*.sql script found **include/data/sql/mysqli.sql**; once you have configured and created the database you may want to assign a username to it for query limits rather than run it on the 'root' username...

## Configuring PHP for MySQL Database: include/dbconfig.php

The following constants are for configuring the database set them as you need and the database should work fine

### API_DB_TYPE

This is the Database Type the default setting is: **mysql**

### API_DB_CHARSET

This is the Database Character Set typal the default setting is: **utf8**

### API_DB_PREFIX

This is the table name prefix it is not used in this code example.

### API_DB_HOST

This is the database mysql server network hostname or IP Address the default is: **localhost**

### API_DB_USER

This is the database username to log on and authenticate with the database the default is: **root**

### API_DB_PASS

This is the database username password for logon to the mysql service with the database, there is no default setting!

### API_DB_NAME

This is the database itself name for the connection, you will specify this when creating the database, there is no default setting!

### API_DB_PCONNECT

This is to specify if a persistent connection is used, the default is 1 = Yes or you can specify also 0 = No!

## Creating the MySQL Database: include/data/sql/mysqli.sql

You can run the **mysqli.sql** file in phpmyadmin or mysql workbench to create the database alternatively the SQL below will be executable from the mysql shell command to create the database, remember you have to create the database first before populating the fields.

    CREATE TABLE `files` (
     `id` mediumint(128) NOT NULL AUTO_INCREMENT,
     `fontid` mediumint(64) NOT NULL DEFAULT '0',
     `sourceid` mediumint(64) NOT NULL DEFAULT '0',
     `filename` varchar(255) NOT NULL DEFAULT '',
     `path` varchar(255) NOT NULL DEFAULT '',
     `extension` varchar(22) NOT NULL DEFAULT '',
     `bytes` int(12) NOT NULL DEFAULT '0',
     `sha1` varchar(40) NOT NULL DEFAULT '',
     `md5` varchar(32) NOT NULL DEFAULT '',
     PRIMARY KEY (`id`),
     KEY `SEARCH` (`fontid`,`sourceid`,`extension`,`sha1`,`md5`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
    
    CREATE TABLE `fonts` (
     `id` mediumint(64) NOT NULL AUTO_INCREMENT,
     `sourceid` mediumint(64) NOT NULL,
     `key` varchar(32) NOT NULL DEFAULT '',
     `name` varchar(255) NOT NULL DEFAULT '',
     `fullname` varchar(255) NOT NULL DEFAULT '',
     `postscriptname` varchar(255) NOT NULL DEFAULT '',
     `subfamily` varchar(255) NOT NULL DEFAULT '',
     `subfamilyid` varchar(255) NOT NULL DEFAULT '',
     `copyright` longtext,
     `email` varchar(198) NOT NULL DEFAULT '',
     `version` varchar(255) NOT NULL DEFAULT '',
     `filename` varchar(128) NOT NULL DEFAULT '',
     `archive` int(12) NOT NULL DEFAULT '0',
     `storage` int(12) NOT NULL DEFAULT '0',
     `files` int(12) NOT NULL DEFAULT '0',
     `tags` int(12) NOT NULL DEFAULT '0',
     `glyphs` int(12) NOT NULL DEFAULT '0',
     `alpha` varchar(1) NOT NULL DEFAULT '',
     `beta` varchar(2) NOT NULL DEFAULT '',
     `charley` varchar(3) NOT NULL DEFAULT '',
     `delta` varchar(64) NOT NULL DEFAULT '',
     `extensions` varchar(255) NOT NULL DEFAULT '',
     `barcode` varchar(40) NOT NULL DEFAULT '',
     `processed` int(12) NOT NULL DEFAULT '0',
     `stored` int(12) NOT NULL DEFAULT '0',
     `tagged` int(12) NOT NULL DEFAULT '0',
     `sent2api` int(12) NOT NULL DEFAULT '0',
     PRIMARY KEY (`id`),
     KEY `SEARCH` (`name`,`key`,`storage`,`alpha`,`beta`,`charley`,`delta`,`extensions`,`barcode`,`processed`,`stored`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
    
    CREATE TABLE `glyphs` (
     `id` mediumint(196) NOT NULL AUTO_INCREMENT,
     `fontid` mediumint(64) NOT NULL DEFAULT '0',
     `sourceid` mediumint(64) NOT NULL DEFAULT '0',
     `name` varchar(45) NOT NULL DEFAULT '',
     `unicode` varchar(10) NOT NULL DEFAULT '',
     PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
    
    CREATE TABLE `sources` (
     `id` mediumint(64) NOT NULL AUTO_INCREMENT,
     `state` enum('Queued','Unique','Duplicate','Locked','Deleted') NOT NULL DEFAULT 'Queued',
     `fingerprint` varchar(40) NOT NULL DEFAULT '',
     `sha1` varchar(40) NOT NULL DEFAULT '',
     `md5` varchar(40) NOT NULL DEFAULT '',
     `extension` varchar(10) NOT NULL DEFAULT '',
     `path` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
     `filename` varchar(255) NOT NULL DEFAULT '',
     `bytes` int(12) NOT NULL DEFAULT '0',
     `found` int(12) NOT NULL DEFAULT '0',
     `fingered` int(12) NOT NULL DEFAULT '0',
     `action` int(12) NOT NULL DEFAULT '0',
     PRIMARY KEY (`id`),
     KEY `SEARCH` (`found`,`action`,`state`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
    
    CREATE TABLE `tags` (
     `id` mediumint(32) NOT NULL AUTO_INCREMENT,
     `tag` varchar(64) NOT NULL DEFAULT '',
     `occured` int(12) NOT NULL DEFAULT '1',
     PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
    
    CREATE TABLE `tags_links` (
     `id` mediumint(128) NOT NULL AUTO_INCREMENT,
     `tagid` mediumint(32) NOT NULL DEFAULT '0',
     `fontid` mediumint(64) NOT NULL DEFAULT '0',
     `sourceid` mediumint(64) NOT NULL DEFAULT '0',
     PRIMARY KEY (`id`),
     KEY `SEARCH` (`tagid`,`fontid`,`sourceid`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

# Setting Runtime states and File Folder Paths

There is a **include/constants.php** file that contain the runtime variables for the cronjobs/sheduled task that needs to be set this is a series of defined constants that are used about the PHP library, you will need to set these explicitly and normally once the process begins, you will not be able to change these settings, without having to move files around and position resources. There is also **mainfile.php** which also contains some start up root constants you will also have to define to get the PHP library executing!

## Settings constants: include/constants.php

This file contains both file path as well as URL path and settings for commital to the SVN, which uses an import method normally, you can in the SVN call specify username and passwords and so on I will refer you to your shell bash executing **$ svn --help** for further description on how to go about this; if your using http(s):// rather than svn:// or ssh+svn:// which I would for the later recommend you set up RSA Keys for the SSH Authentication with **no login**

### API_FONTS_SOURCES

This is a folder path that is used when the webdav is well contains fonts, then all fonts when they are unpacked from archives as well as placed on the webdav are copied into this folder path until selected for staging to the SVN.

### API_FONTS_STAGING

This is a folder path that is used when staging a font onto the SVN, when the font is converted for the SVN it is left here until the batch *.sh script fires to commit to the SVN.

### API_FONTS_JSON

This is the folder path which the \*.json resource font description files are stored, when you make the SVN you have to make a immediately in the root of it a **/json** path which with SVN you checkout into this path by running the following say the path is **/fonts/fonts-json** then you would run the following command into this path at your shell bash terminal which that command is for example with sourceforge.net as follows with the **/json** subpath specified to checkout as root ie. **$ svn checkout https://svn.code.sf.net/p/chronolabs-cooperative/fonts/json/ /fonts/fonts-json**.

### API_FONTS_WEBDAV

This is the path of the WebDAV as per the apache2 configuration for the WebDAV filebase!

### API_FONTS_STAGER

This is the terminal shell command to import a file into the SVN using the **svn import...** function, this has 3 string variables specified by '%s' which subsitute in order the commital message, the root file path and the path on the SVN it is to be writen to.

### API_FONTS_SVNRAW

This is the network URL/URI path for raw access to files, this is used in generating CSS as well as other information and accessing files; you specify the full root path to the SVN file raw access then end with a '%s' which is subsituted for the path and filenames.

### API_FONTS_SVNADD

SVN Command for Adding Files to the Respository the default setting is **svn add * --force**.

### API_FONTS_COMMIT

SVN Command for Commiting Files to the Respository the default setting is **svn commit -m "%s"**.

## Main Runtime constants: mainfile.php

This is the boot **mainfile.php** constants, you will need to change these to support the operation of this library...

### API_ROOT_PATH

This is the root path of this library and the php files with it.

### API_PATH

This is the cache data folder path for file cache and runtime generated data files.

### API_VAR_PATH

This is the temporary folder path the default setting is **/tmp** would be different for things other than ubuntu/debian.

### API_URL

This is the URL for the WebDAV and resource!

### API_COOKIE_DOMAIN

This is the domain path for the cookie system, remember specifying a period in front will include subdomains endlessly!

# Cron Jobs - Scheduled Tasks

There is a couple of cron jobs that need to run on the system in order for the system to run completely within versioning specifications to get to the cron scheduler in ubuntu/debian run the following

    $ sudo crontab -e
    
once in the cron scheduler put these lines in making sure the paths resolution is correct as well as any load balancing you have to do, this is assuming **API_ROOT_PATH = /fonts/fonts-dav**.

    */47 */4 * * * /usr/bin/php -q /fonts/fonts-dav/cron.sources.php >/dev/null 2>&1
    */17 * * * * /usr/bin/php -q /fonts/fonts-dav/cron.fingered.php >/dev/null 2>&1
    */13 * * * * /usr/bin/php -q /fonts/fonts-dav/cron.staging.php >/dev/null 2>&1
    */13 */6 * * * /usr/bin/php -q /fonts/fonts-dav/cron.uncompleted.php >/dev/null 2>&1
    */14 * * * * sh /fonts/fonts-dav/cron.staging.*.sh >/dev/null 2>&1
    */25 * * * * sh /fonts/fonts-dav/cron.staging.*.sh >/dev/null 2>&1
    */36 * * * * sh /fonts/fonts-dav/cron.staging.*.sh >/dev/null 2>&1
    */11 * * * * /usr/bin/php -q /fonts/fonts-dav/cron.webdav.fonts.php >/dev/null 2>&1
    */11 * * * * /usr/bin/php -q /fonts/fonts-dav/cron.webdav.packs.php >/dev/null 2>&1
    */41 * * * * /usr/bin/php -q /fonts/fonts-dav/cron.tagging.php >/dev/null 2>&1
    */11 */1 * * * /usr/bin/php -q /fonts/fonts-dav/cron.json.commit.php >/dev/null 2>&1
    */11 */2 * * * /usr/bin/php -q /fonts/fonts-dav/cron.json.fonts.php >/dev/null 2>&1
    */22 */2 * * * /usr/bin/php -q /fonts/fonts-dav/cron.json.files.php >/dev/null 2>&1
    */33 */2 * * * /usr/bin/php -q /fonts/fonts-dav/cron.json.tags.php >/dev/null 2>&1
    */11 */3 * * * /usr/bin/php -q /fonts/fonts-dav/cron.json.glyphs.php >/dev/null 2>&1
    */44 */2 * * * /usr/bin/php -q /fonts/fonts-dav/cron.json.others.php >/dev/null 2>&1

# Configuring SVN on Ubuntu/Debian: .subversion

You will find a configuration file around for SVN (subversio) in the following places **/root/.subversion** or **/home/username/.subversion**; this is the configuration file for SVN and you will be able to turn on remember password etc on it so you are able to resource and use http(s):// or svn:// without having to hard code your username and password which you can also do if you like which you will find out how to on the command: **$ svn --help**!