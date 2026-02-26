-- Add columns to oxattribute
ALTER TABLE `oxattribute` ADD COLUMN `OXTYPE` VARCHAR(32) NOT NULL DEFAULT 'TEXT' COMMENT 'Attribute Type (TEXT, SELECT, BOOL, DATE, COLOR, IMAGE)';
ALTER TABLE `oxattribute` ADD COLUMN `OXMULTILANG` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Is attribute multilingual?';

-- Create table for attribute values
CREATE TABLE `oxattributevalues` (
    `OXID` CHAR(32) NOT NULL COMMENT 'Unique ID',
    `OXOBJID` CHAR(32) NOT NULL COMMENT 'Group ID for translations',
    `OXATTRID` CHAR(32) NOT NULL COMMENT 'Foreign key to oxattribute',
    `OXLANG` INT NOT NULL DEFAULT 0 COMMENT 'Language ID',
    `OXVALUE` VARCHAR(255) NOT NULL COMMENT 'Value',
    `OXSORT` INT NOT NULL DEFAULT 0 COMMENT 'Sorting order',
    `OXCOLOR` VARCHAR(32) NOT NULL DEFAULT '' COMMENT 'Hex color code',
    `OXIMAGE` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Image path',
    PRIMARY KEY (`OXID`),
    INDEX `OXATTRID` (`OXATTRID`),
    INDEX `OXOBJID` (`OXOBJID`),
    INDEX `OXLANG` (`OXLANG`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Structured Attribute Values';

-- Extension for product assignment
ALTER TABLE `oxobject2attribute` ADD COLUMN `OXATTRVALUEID` VARCHAR(255) DEFAULT NULL COMMENT 'Link to oxattributevalues.OXID(s)';
ALTER TABLE `oxobject2attribute` ADD INDEX `OXATTRVALUEID` (`OXATTRVALUEID`);
