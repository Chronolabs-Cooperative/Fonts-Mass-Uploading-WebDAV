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





