
-- Suppression de l'ancienne primary key des associations de keyword.
ALTER TABLE `Keyword_Association` DROP `id`;
ALTER TABLE `Keyword_Association` DROP INDEX `associationUniqueness`;
ALTER TABLE `Techno_Meaning` CHANGE `refKeyword` `keyword` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `Techno_Family_Member` CHANGE `refKeyword` `keyword` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
