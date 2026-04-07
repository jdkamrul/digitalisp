-- Standard FreeRADIUS MariaDB/MySQL Schema Reference
-- Use this schema to set up your external FreeRADIUS database.

CREATE DATABASE IF NOT EXISTS radius;
USE radius;

-- 1. Authentication Table (radcheck)
CREATE TABLE IF NOT EXISTS radcheck (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    username varchar(64) NOT NULL DEFAULT '',
    attribute varchar(64) NOT NULL DEFAULT '',
    op char(2) NOT NULL DEFAULT '==',
    value varchar(253) NOT NULL DEFAULT '',
    PRIMARY KEY (id),
    KEY username (username(32))
) ENGINE=InnoDB;

-- 2. Authorization Table (radreply)
CREATE TABLE IF NOT EXISTS radreply (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    username varchar(64) NOT NULL DEFAULT '',
    attribute varchar(64) NOT NULL DEFAULT '',
    op char(2) NOT NULL DEFAULT '=',
    value varchar(253) NOT NULL DEFAULT '',
    PRIMARY KEY (id),
    KEY username (username(32))
) ENGINE=InnoDB;

-- 3. Group Attributes (radgroupcheck)
-- Used for defining profiles (e.g. speed limits)
CREATE TABLE IF NOT EXISTS radgroupcheck (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    groupname varchar(64) NOT NULL DEFAULT '',
    attribute varchar(64) NOT NULL DEFAULT '',
    op char(2) NOT NULL DEFAULT '==',
    value varchar(253) NOT NULL DEFAULT '',
    PRIMARY KEY (id),
    KEY groupname (groupname(32))
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS radgroupreply (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    groupname varchar(64) NOT NULL DEFAULT '',
    attribute varchar(64) NOT NULL DEFAULT '',
    op char(2) NOT NULL DEFAULT '=',
    value varchar(253) NOT NULL DEFAULT '',
    PRIMARY KEY (id),
    KEY groupname (groupname(32))
) ENGINE=InnoDB;

-- 4. User to Group Mapping (radusergroup)
CREATE TABLE IF NOT EXISTS radusergroup (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    username varchar(64) NOT NULL DEFAULT '',
    groupname varchar(64) NOT NULL DEFAULT '',
    priority int(11) NOT NULL DEFAULT '1',
    PRIMARY KEY (id),
    KEY username (username(32))
) ENGINE=InnoDB;

-- 5. Accounting Table (radacct)
CREATE TABLE IF NOT EXISTS radacct (
    radacctid bigint(21) NOT NULL AUTO_INCREMENT,
    acctsessionid varchar(64) NOT NULL DEFAULT '',
    acctuniqueid varchar(32) NOT NULL DEFAULT '',
    username varchar(64) NOT NULL DEFAULT '',
    groupname varchar(64) NOT NULL DEFAULT '',
    realm varchar(64) DEFAULT '',
    nasipaddress varchar(15) NOT NULL DEFAULT '',
    nasportid varchar(32) DEFAULT NULL,
    nasporttype varchar(32) DEFAULT NULL,
    acctstarttime datetime DEFAULT NULL,
    acctupdatetime datetime DEFAULT NULL,
    acctstoptime datetime DEFAULT NULL,
    acctinterval int(12) DEFAULT NULL,
    acctsessiontime int(12) unsigned DEFAULT NULL,
    acctauthentic varchar(32) DEFAULT NULL,
    connectinfo_start varchar(50) DEFAULT NULL,
    connectinfo_stop varchar(50) DEFAULT NULL,
    acctinputoctets bigint(20) DEFAULT NULL,
    acctoutputoctets bigint(20) DEFAULT NULL,
    calledstationid varchar(50) DEFAULT NULL,
    callingstationid varchar(50) DEFAULT NULL,
    acctterminatecause varchar(32) DEFAULT NULL,
    servicetype varchar(32) DEFAULT NULL,
    framedprotocol varchar(32) DEFAULT NULL,
    framedipaddress varchar(15) DEFAULT NULL,
    PRIMARY KEY (radacctid),
    UNIQUE KEY acctuniqueid (acctuniqueid),
    KEY username (username),
    KEY framedipaddress (framedipaddress),
    KEY acctsessionid (acctsessionid),
    KEY acctsessiontime (acctsessiontime),
    KEY acctstarttime (acctstarttime),
    KEY acctinterval (acctinterval),
    KEY acctstoptime (acctstoptime),
    KEY nasipaddress (nasipaddress)
) ENGINE=InnoDB;
