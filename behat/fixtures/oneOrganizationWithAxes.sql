-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le: Jeu 27 Juin 2013 à 10:59
-- Version du serveur: 5.5.24-log
-- Version de PHP: 5.4.3

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT=0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `inventory`
--

-- --------------------------------------------------------

--
-- Structure de la table `acl_filter`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `acl_filter`;
CREATE TABLE IF NOT EXISTS `acl_filter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idUser` int(11) NOT NULL,
  `action` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `entityName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `entityIdentifier` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniqueACLFilterEntry` (`idUser`,`action`,`entityName`,`entityIdentifier`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=22 ;

--
-- Contenu de la table `acl_filter`
--

INSERT INTO `acl_filter` (`id`, `idUser`, `action`, `entityName`, `entityIdentifier`) VALUES
(19, 3, 'Orga_Action_Cell::256', 'Orga_Model_Cell', 1),
(20, 3, 'Orga_Action_Cell::512', 'Orga_Model_Cell', 1),
(16, 3, 'User_Model_Action_Default::1', 'Orga_Model_Cell', 1),
(13, 3, 'User_Model_Action_Default::1', 'Orga_Model_Organization', 1),
(8, 3, 'User_Model_Action_Default::1', 'User_Model_User', 3),
(18, 3, 'User_Model_Action_Default::128', 'Orga_Model_Cell', 1),
(10, 3, 'User_Model_Action_Default::16', 'User_Model_Role', 1),
(21, 3, 'User_Model_Action_Default::2', 'User_Model_User', 3),
(17, 3, 'User_Model_Action_Default::4', 'Orga_Model_Cell', 1),
(14, 3, 'User_Model_Action_Default::4', 'Orga_Model_Organization', 1),
(11, 3, 'User_Model_Action_Default::4', 'User_Model_Role', 1),
(9, 3, 'User_Model_Action_Default::4', 'User_Model_User', 3),
(15, 3, 'User_Model_Action_Default::8', 'Orga_Model_Organization', 1),
(12, 3, 'User_Model_Action_Default::8', 'User_Model_Role', 1);

-- --------------------------------------------------------

--
-- Structure de la table `af_action`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_action`;
CREATE TABLE IF NOT EXISTS `af_action` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idTargetComponent` int(11) NOT NULL,
  `idCondition` int(11) DEFAULT NULL,
  `type_action` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_E42D79D956BE68EA` (`idTargetComponent`),
  KEY `IDX_E42D79D9413CA315` (`idCondition`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `af_action_setalgovalue`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_action_setalgovalue`;
CREATE TABLE IF NOT EXISTS `af_action_setalgovalue` (
  `id` int(11) NOT NULL,
  `idAlgo` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_B35712EB675FA209` (`idAlgo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_action_setoptionstate`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_action_setoptionstate`;
CREATE TABLE IF NOT EXISTS `af_action_setoptionstate` (
  `id` int(11) NOT NULL,
  `state` int(11) NOT NULL,
  `idOption` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_15CE5C0B3997A82A` (`idOption`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_action_setstate`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_action_setstate`;
CREATE TABLE IF NOT EXISTS `af_action_setstate` (
  `id` int(11) NOT NULL,
  `state` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_action_setvalue`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_action_setvalue`;
CREATE TABLE IF NOT EXISTS `af_action_setvalue` (
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_action_setvalue_checkbox`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_action_setvalue_checkbox`;
CREATE TABLE IF NOT EXISTS `af_action_setvalue_checkbox` (
  `id` int(11) NOT NULL,
  `checked` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_action_setvalue_numeric`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_action_setvalue_numeric`;
CREATE TABLE IF NOT EXISTS `af_action_setvalue_numeric` (
  `id` int(11) NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_action_setvalue_select_multi`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_action_setvalue_select_multi`;
CREATE TABLE IF NOT EXISTS `af_action_setvalue_select_multi` (
  `id` int(11) NOT NULL,
  `options_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_CF36C7973ADB05F1` (`options_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_action_setvalue_select_single`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_action_setvalue_select_single`;
CREATE TABLE IF NOT EXISTS `af_action_setvalue_select_single` (
  `id` int(11) NOT NULL,
  `idOption` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_D4AC72F53997A82A` (`idOption`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_af`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_af`;
CREATE TABLE IF NOT EXISTS `af_af` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ref` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `documentation` longtext COLLATE utf8_unicode_ci,
  `position` int(11) NOT NULL,
  `idRootGroup` int(11) DEFAULT NULL,
  `idAlgoSet` int(11) NOT NULL,
  `idMainAlgo` int(11) NOT NULL,
  `idCategory` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_CF37269146F3EA3` (`ref`),
  UNIQUE KEY `UNIQ_CF37269B66DCADA` (`idAlgoSet`),
  UNIQUE KEY `UNIQ_CF37269CC831C02` (`idMainAlgo`),
  UNIQUE KEY `UNIQ_CF372694A1389BB` (`idRootGroup`),
  KEY `IDX_CF3726955EF339A` (`idCategory`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `af_category`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_category`;
CREATE TABLE IF NOT EXISTS `af_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  `idParentCategory` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_D8162A7D2526073F` (`idParentCategory`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `af_component`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_component`;
CREATE TABLE IF NOT EXISTS `af_component` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ref` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `help` longtext COLLATE utf8_unicode_ci,
  `visible` tinyint(1) NOT NULL,
  `position` int(11) NOT NULL,
  `idAF` int(11) DEFAULT NULL,
  `idGroup` int(11) DEFAULT NULL,
  `type_component` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ref` (`idAF`,`ref`),
  KEY `IDX_8BF704C35E699E88` (`idAF`),
  KEY `IDX_8BF704C37A0407D8` (`idGroup`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `af_component_checkbox`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_component_checkbox`;
CREATE TABLE IF NOT EXISTS `af_component_checkbox` (
  `id` int(11) NOT NULL,
  `defaultValue` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_component_field`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_component_field`;
CREATE TABLE IF NOT EXISTS `af_component_field` (
  `id` int(11) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_component_group`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_component_group`;
CREATE TABLE IF NOT EXISTS `af_component_group` (
  `id` int(11) NOT NULL,
  `foldaway` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_component_numeric`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_component_numeric`;
CREATE TABLE IF NOT EXISTS `af_component_numeric` (
  `id` int(11) NOT NULL,
  `unit` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `withUncertainty` tinyint(1) NOT NULL,
  `defaultValue` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `defaultValueReminder` tinyint(1) NOT NULL,
  `required` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_component_select`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_component_select`;
CREATE TABLE IF NOT EXISTS `af_component_select` (
  `id` int(11) NOT NULL,
  `required` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_component_select_multi`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_component_select_multi`;
CREATE TABLE IF NOT EXISTS `af_component_select_multi` (
  `id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_component_select_multi_defaultvalues`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_component_select_multi_defaultvalues`;
CREATE TABLE IF NOT EXISTS `af_component_select_multi_defaultvalues` (
  `idSelectMulti` int(11) NOT NULL,
  `idSelectOption` int(11) NOT NULL,
  PRIMARY KEY (`idSelectMulti`,`idSelectOption`),
  KEY `IDX_67AB7A01B117EB8E` (`idSelectMulti`),
  KEY `IDX_67AB7A012098DCB0` (`idSelectOption`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_component_select_option`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_component_select_option`;
CREATE TABLE IF NOT EXISTS `af_component_select_option` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ref` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `visible` tinyint(1) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `position` int(11) NOT NULL,
  `idSelect` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ref` (`idSelect`,`ref`),
  KEY `IDX_A7D370AE28E3425A` (`idSelect`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `af_component_select_single`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_component_select_single`;
CREATE TABLE IF NOT EXISTS `af_component_select_single` (
  `id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `idDefaultValue` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_37F257079AFC98DC` (`idDefaultValue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_component_subaf`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_component_subaf`;
CREATE TABLE IF NOT EXISTS `af_component_subaf` (
  `id` int(11) NOT NULL,
  `foldaway` int(11) NOT NULL,
  `idCalledAF` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_C6EB6B3E532BBE7A` (`idCalledAF`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_component_subaf_notrepeated`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_component_subaf_notrepeated`;
CREATE TABLE IF NOT EXISTS `af_component_subaf_notrepeated` (
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_component_subaf_repeated`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_component_subaf_repeated`;
CREATE TABLE IF NOT EXISTS `af_component_subaf_repeated` (
  `id` int(11) NOT NULL,
  `minInputNumber` int(11) NOT NULL,
  `withFreeLabel` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_condition`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_condition`;
CREATE TABLE IF NOT EXISTS `af_condition` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ref` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `idAF` int(11) NOT NULL,
  `type_condition` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ref` (`idAF`,`ref`),
  KEY `IDX_7FDF2DD75E699E88` (`idAF`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `af_condition_elementary`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_condition_elementary`;
CREATE TABLE IF NOT EXISTS `af_condition_elementary` (
  `id` int(11) NOT NULL,
  `relation` int(11) NOT NULL,
  `idField` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_F5C5CED94C310645` (`idField`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_condition_elementary_checkbox`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_condition_elementary_checkbox`;
CREATE TABLE IF NOT EXISTS `af_condition_elementary_checkbox` (
  `id` int(11) NOT NULL,
  `value` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_condition_elementary_numeric`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_condition_elementary_numeric`;
CREATE TABLE IF NOT EXISTS `af_condition_elementary_numeric` (
  `id` int(11) NOT NULL,
  `value` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_condition_elementary_select_multi`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_condition_elementary_select_multi`;
CREATE TABLE IF NOT EXISTS `af_condition_elementary_select_multi` (
  `id` int(11) NOT NULL,
  `idOption` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_5E5590643997A82A` (`idOption`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_condition_elementary_select_single`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_condition_elementary_select_single`;
CREATE TABLE IF NOT EXISTS `af_condition_elementary_select_single` (
  `id` int(11) NOT NULL,
  `idOption` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_F089B2043997A82A` (`idOption`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_condition_expression`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_condition_expression`;
CREATE TABLE IF NOT EXISTS `af_condition_expression` (
  `id` int(11) NOT NULL,
  `expression` longtext COLLATE utf8_unicode_ci NOT NULL,
  `idTECExpression` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_D62FDD252C508E17` (`idTECExpression`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_input`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_input`;
CREATE TABLE IF NOT EXISTS `af_input` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hidden` tinyint(1) NOT NULL,
  `disabled` tinyint(1) NOT NULL,
  `refComponent` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `idInputSet` int(11) NOT NULL,
  `inputType` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_85DD0DB5DDF99681` (`idInputSet`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `af_inputset`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_inputset`;
CREATE TABLE IF NOT EXISTS `af_inputset` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `refAF` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `completion` int(11) DEFAULT NULL,
  `inputSetType` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `af_inputset_primary`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_inputset_primary`;
CREATE TABLE IF NOT EXISTS `af_inputset_primary` (
  `id` int(11) NOT NULL,
  `finished` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_inputset_sub`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_inputset_sub`;
CREATE TABLE IF NOT EXISTS `af_inputset_sub` (
  `id` int(11) NOT NULL,
  `freeLabel` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_input_checkbox`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_input_checkbox`;
CREATE TABLE IF NOT EXISTS `af_input_checkbox` (
  `id` int(11) NOT NULL,
  `value` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_input_group`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_input_group`;
CREATE TABLE IF NOT EXISTS `af_input_group` (
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_input_numeric`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_input_numeric`;
CREATE TABLE IF NOT EXISTS `af_input_numeric` (
  `id` int(11) NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_input_select_multi`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_input_select_multi`;
CREATE TABLE IF NOT EXISTS `af_input_select_multi` (
  `id` int(11) NOT NULL,
  `value` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_input_select_single`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_input_select_single`;
CREATE TABLE IF NOT EXISTS `af_input_select_single` (
  `id` int(11) NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_input_subaf`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_input_subaf`;
CREATE TABLE IF NOT EXISTS `af_input_subaf` (
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_input_subaf_notrepeated`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_input_subaf_notrepeated`;
CREATE TABLE IF NOT EXISTS `af_input_subaf_notrepeated` (
  `id` int(11) NOT NULL,
  `idSub` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_882F985794A9F5B` (`idSub`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_input_subaf_repeated`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_input_subaf_repeated`;
CREATE TABLE IF NOT EXISTS `af_input_subaf_repeated` (
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_input_subaf_repeated_value`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_input_subaf_repeated_value`;
CREATE TABLE IF NOT EXISTS `af_input_subaf_repeated_value` (
  `idInputSubAF` int(11) NOT NULL,
  `idSub` int(11) NOT NULL,
  PRIMARY KEY (`idInputSubAF`,`idSub`),
  KEY `IDX_3E6E256E444242C1` (`idInputSubAF`),
  KEY `IDX_3E6E256E794A9F5B` (`idSub`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_output_element`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_output_element`;
CREATE TABLE IF NOT EXISTS `af_output_element` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `refContext` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `refIndicator` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `idOutputSet` int(11) NOT NULL,
  `idInputSet` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_FFD106E79AFBF5DA` (`idOutputSet`),
  KEY `IDX_FFD106E7DDF99681` (`idInputSet`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `af_output_element_indexes`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_output_element_indexes`;
CREATE TABLE IF NOT EXISTS `af_output_element_indexes` (
  `idOutputElement` int(11) NOT NULL,
  `idIndex` int(11) NOT NULL,
  PRIMARY KEY (`idOutputElement`,`idIndex`),
  KEY `IDX_18A421F769F7E6E1` (`idOutputElement`),
  KEY `IDX_18A421F797B7241C` (`idIndex`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `af_output_index`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_output_index`;
CREATE TABLE IF NOT EXISTS `af_output_index` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `refAxis` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `refMember` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `af_output_outputset`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_output_outputset`;
CREATE TABLE IF NOT EXISTS `af_output_outputset` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idInputSet` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_1C1A514ADDF99681` (`idInputSet`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `af_output_total`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `af_output_total`;
CREATE TABLE IF NOT EXISTS `af_output_total` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `refIndicator` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `idOutputSet` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_355225DF9AFBF5DA` (`idOutputSet`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `algo_algo`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `algo_algo`;
CREATE TABLE IF NOT EXISTS `algo_algo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ref` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `idSet` int(11) NOT NULL,
  `type_algo` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ref` (`idSet`,`ref`),
  KEY `IDX_3080AD55C75C385B` (`idSet`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `algo_condition`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `algo_condition`;
CREATE TABLE IF NOT EXISTS `algo_condition` (
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `algo_condition_elementary`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `algo_condition_elementary`;
CREATE TABLE IF NOT EXISTS `algo_condition_elementary` (
  `id` int(11) NOT NULL,
  `relation` int(11) DEFAULT NULL,
  `inputRef` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `algo_condition_elementary_boolean`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `algo_condition_elementary_boolean`;
CREATE TABLE IF NOT EXISTS `algo_condition_elementary_boolean` (
  `id` int(11) NOT NULL,
  `value` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `algo_condition_elementary_numeric`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `algo_condition_elementary_numeric`;
CREATE TABLE IF NOT EXISTS `algo_condition_elementary_numeric` (
  `id` int(11) NOT NULL,
  `value` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `algo_condition_elementary_select`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `algo_condition_elementary_select`;
CREATE TABLE IF NOT EXISTS `algo_condition_elementary_select` (
  `id` int(11) NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `algo_condition_elementary_select_multi`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `algo_condition_elementary_select_multi`;
CREATE TABLE IF NOT EXISTS `algo_condition_elementary_select_multi` (
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `algo_condition_elementary_select_single`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `algo_condition_elementary_select_single`;
CREATE TABLE IF NOT EXISTS `algo_condition_elementary_select_single` (
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `algo_condition_expression`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `algo_condition_expression`;
CREATE TABLE IF NOT EXISTS `algo_condition_expression` (
  `id` int(11) NOT NULL,
  `expression` longtext COLLATE utf8_unicode_ci NOT NULL,
  `idExpression` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_F93CF4D65AC448EB` (`idExpression`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `algo_index`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `algo_index`;
CREATE TABLE IF NOT EXISTS `algo_index` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `refClassifAxis` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `idAlgoNumeric` int(11) DEFAULT NULL,
  `type_index` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_F53DE6CC55E2C741` (`idAlgoNumeric`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `algo_index_algo`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `algo_index_algo`;
CREATE TABLE IF NOT EXISTS `algo_index_algo` (
  `id` int(11) NOT NULL,
  `idAlgo` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_733C5BB7675FA209` (`idAlgo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `algo_index_fixed`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `algo_index_fixed`;
CREATE TABLE IF NOT EXISTS `algo_index_fixed` (
  `id` int(11) NOT NULL,
  `refClassifMember` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `algo_numeric`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `algo_numeric`;
CREATE TABLE IF NOT EXISTS `algo_numeric` (
  `id` int(11) NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `refContext` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `refIndicator` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `algo_numeric_constant`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `algo_numeric_constant`;
CREATE TABLE IF NOT EXISTS `algo_numeric_constant` (
  `id` int(11) NOT NULL,
  `unitValue` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `algo_numeric_expression`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `algo_numeric_expression`;
CREATE TABLE IF NOT EXISTS `algo_numeric_expression` (
  `id` int(11) NOT NULL,
  `unit` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `expression` longtext COLLATE utf8_unicode_ci NOT NULL,
  `idExpression` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_3FA885465AC448EB` (`idExpression`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `algo_numeric_input`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `algo_numeric_input`;
CREATE TABLE IF NOT EXISTS `algo_numeric_input` (
  `id` int(11) NOT NULL,
  `inputRef` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `unit` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `algo_numeric_parameter`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `algo_numeric_parameter`;
CREATE TABLE IF NOT EXISTS `algo_numeric_parameter` (
  `id` int(11) NOT NULL,
  `familyRef` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `algo_parametercoordinate`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `algo_parametercoordinate`;
CREATE TABLE IF NOT EXISTS `algo_parametercoordinate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `refDimensionMeaning` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `idAlgo` int(11) NOT NULL,
  `type_parameter` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_6EE2BBED675FA209` (`idAlgo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `algo_parametercoordinate_algo`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `algo_parametercoordinate_algo`;
CREATE TABLE IF NOT EXISTS `algo_parametercoordinate_algo` (
  `id` int(11) NOT NULL,
  `idAlgoKeyword` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_A29AABA048D02A31` (`idAlgoKeyword`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `algo_parametercoordinate_fixed`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `algo_parametercoordinate_fixed`;
CREATE TABLE IF NOT EXISTS `algo_parametercoordinate_fixed` (
  `id` int(11) NOT NULL,
  `refMemberKeyword` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `algo_selection`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `algo_selection`;
CREATE TABLE IF NOT EXISTS `algo_selection` (
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `algo_selection_main`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `algo_selection_main`;
CREATE TABLE IF NOT EXISTS `algo_selection_main` (
  `id` int(11) NOT NULL,
  `expression` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `idExpression` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_52564FAA5AC448EB` (`idExpression`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `algo_selection_textkey`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `algo_selection_textkey`;
CREATE TABLE IF NOT EXISTS `algo_selection_textkey` (
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `algo_selection_textkey_expression`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `algo_selection_textkey_expression`;
CREATE TABLE IF NOT EXISTS `algo_selection_textkey_expression` (
  `id` int(11) NOT NULL,
  `expression` longtext COLLATE utf8_unicode_ci NOT NULL,
  `idExpression` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_D167CFEC5AC448EB` (`idExpression`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `algo_selection_textkey_input`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `algo_selection_textkey_input`;
CREATE TABLE IF NOT EXISTS `algo_selection_textkey_input` (
  `id` int(11) NOT NULL,
  `inputRef` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `algo_set`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `algo_set`;
CREATE TABLE IF NOT EXISTS `algo_set` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `classif_axis`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `classif_axis`;
CREATE TABLE IF NOT EXISTS `classif_axis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ref` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  `idDirectNarrower` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_CD8BEA0E146F3EA3` (`ref`),
  KEY `IDX_CD8BEA0E2F89D6F2` (`idDirectNarrower`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `classif_context`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `classif_context`;
CREATE TABLE IF NOT EXISTS `classif_context` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ref` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_730D3DA8146F3EA3` (`ref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `classif_contextindicator`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `classif_contextindicator`;
CREATE TABLE IF NOT EXISTS `classif_contextindicator` (
  `idContext` int(11) NOT NULL,
  `idIndicator` int(11) NOT NULL,
  PRIMARY KEY (`idContext`,`idIndicator`),
  KEY `IDX_FE1067E7F2E4EE8C` (`idContext`),
  KEY `IDX_FE1067E72DDEB6E5` (`idIndicator`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `classif_contextindicator_axes`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `classif_contextindicator_axes`;
CREATE TABLE IF NOT EXISTS `classif_contextindicator_axes` (
  `idContext` int(11) NOT NULL,
  `idIndicator` int(11) NOT NULL,
  `idAxis` int(11) NOT NULL,
  PRIMARY KEY (`idContext`,`idIndicator`,`idAxis`),
  KEY `IDX_7EB1ADE5F2E4EE8C2DDEB6E5` (`idContext`,`idIndicator`),
  KEY `IDX_7EB1ADE5F6F2D864` (`idAxis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `classif_indicator`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `classif_indicator`;
CREATE TABLE IF NOT EXISTS `classif_indicator` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ref` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  `refUnit` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `refRatioUnit` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_C3EC55C4146F3EA3` (`ref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `classif_member`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `classif_member`;
CREATE TABLE IF NOT EXISTS `classif_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ref` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  `idAxis` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `memberRefUniquenessInAxis` (`ref`,`idAxis`),
  KEY `IDX_4430BB36F6F2D864` (`idAxis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `classif_member_association`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `classif_member_association`;
CREATE TABLE IF NOT EXISTS `classif_member_association` (
  `idParent` int(11) NOT NULL,
  `idChild` int(11) NOT NULL,
  PRIMARY KEY (`idParent`,`idChild`),
  KEY `IDX_4997A6F55E9FC8D5` (`idParent`),
  KEY `IDX_4997A6F535771734` (`idChild`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `doc_bibliography`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `doc_bibliography`;
CREATE TABLE IF NOT EXISTS `doc_bibliography` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `doc_bibliography_referenceddocuments`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `doc_bibliography_referenceddocuments`;
CREATE TABLE IF NOT EXISTS `doc_bibliography_referenceddocuments` (
  `idBibliography` int(11) NOT NULL,
  `idDocument` int(11) NOT NULL,
  PRIMARY KEY (`idBibliography`,`idDocument`),
  KEY `IDX_79D8433ED2D14599` (`idBibliography`),
  KEY `IDX_79D8433E8BCAA02D` (`idDocument`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `doc_document`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `doc_document`;
CREATE TABLE IF NOT EXISTS `doc_document` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `filePath` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `creationDate` datetime NOT NULL,
  `idLibrary` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_589A2E83B139F34E` (`idLibrary`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `doc_library`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `doc_library`;
CREATE TABLE IF NOT EXISTS `doc_library` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `dw_axis`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `dw_axis`;
CREATE TABLE IF NOT EXISTS `dw_axis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ref` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  `idCube` int(11) NOT NULL,
  `idDirectNarrower` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `axisRefUniquenessInCube` (`ref`,`idCube`),
  KEY `IDX_829918994303EF26` (`idCube`),
  KEY `IDX_829918992F89D6F2` (`idDirectNarrower`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `dw_cube`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `dw_cube`;
CREATE TABLE IF NOT EXISTS `dw_cube` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `dw_filter`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `dw_filter`;
CREATE TABLE IF NOT EXISTS `dw_filter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idReport` int(11) DEFAULT NULL,
  `idAxis` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_26A3DEACA73EDF1E` (`idReport`),
  KEY `IDX_26A3DEACF6F2D864` (`idAxis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `dw_filter_member`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `dw_filter_member`;
CREATE TABLE IF NOT EXISTS `dw_filter_member` (
  `idFilter` int(11) NOT NULL,
  `idMember` int(11) NOT NULL,
  PRIMARY KEY (`idFilter`,`idMember`),
  KEY `IDX_949AC92C1CD5F787` (`idFilter`),
  KEY `IDX_949AC92C13F552E2` (`idMember`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `dw_indicator`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `dw_indicator`;
CREATE TABLE IF NOT EXISTS `dw_indicator` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ref` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  `refUnit` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `refRatioUnit` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `idCube` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `indicatorRefUniquenessInCube` (`ref`,`idCube`),
  KEY `IDX_E4E6543C4303EF26` (`idCube`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `dw_member`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `dw_member`;
CREATE TABLE IF NOT EXISTS `dw_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ref` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  `idAxis` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_29837BC9F6F2D864` (`idAxis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `dw_member_association`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `dw_member_association`;
CREATE TABLE IF NOT EXISTS `dw_member_association` (
  `idParent` int(11) NOT NULL,
  `idChild` int(11) NOT NULL,
  PRIMARY KEY (`idParent`,`idChild`),
  KEY `IDX_16CAAD295E9FC8D5` (`idParent`),
  KEY `IDX_16CAAD2935771734` (`idChild`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `dw_report`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `dw_report`;
CREATE TABLE IF NOT EXISTS `dw_report` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `chartType` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sortType` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `withUncertainty` tinyint(1) NOT NULL,
  `idCube` int(11) NOT NULL,
  `idNumerator` int(11) DEFAULT NULL,
  `idNumeratorAxis1` int(11) DEFAULT NULL,
  `idNumeratorAxis2` int(11) DEFAULT NULL,
  `idDenominator` int(11) DEFAULT NULL,
  `idDenominatorAxis1` int(11) DEFAULT NULL,
  `idDenominatorAxis2` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_9D48F6354303EF26` (`idCube`),
  KEY `IDX_9D48F635D7B4CDCB` (`idNumerator`),
  KEY `IDX_9D48F63593C3E32` (`idNumeratorAxis1`),
  KEY `IDX_9D48F63590356F88` (`idNumeratorAxis2`),
  KEY `IDX_9D48F635EE70132` (`idDenominator`),
  KEY `IDX_9D48F6354E83B31F` (`idDenominatorAxis1`),
  KEY `IDX_9D48F635D78AE2A5` (`idDenominatorAxis2`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `dw_result`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `dw_result`;
CREATE TABLE IF NOT EXISTS `dw_result` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `idCube` int(11) DEFAULT NULL,
  `idIndicator` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_4A0D40A24303EF26` (`idCube`),
  KEY `IDX_4A0D40A22DDEB6E5` (`idIndicator`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `dw_result_member`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `dw_result_member`;
CREATE TABLE IF NOT EXISTS `dw_result_member` (
  `idResult` int(11) NOT NULL,
  `idMember` int(11) NOT NULL,
  PRIMARY KEY (`idResult`,`idMember`),
  KEY `IDX_5D2EAC0E707B6989` (`idResult`),
  KEY `IDX_5D2EAC0E13F552E2` (`idMember`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ext_log_entries`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `ext_log_entries`;
CREATE TABLE IF NOT EXISTS `ext_log_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `logged_at` datetime NOT NULL,
  `object_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `object_class` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `version` int(11) NOT NULL,
  `data` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  `username` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `log_class_lookup_idx` (`object_class`),
  KEY `log_date_lookup_idx` (`logged_at`),
  KEY `log_user_lookup_idx` (`username`),
  KEY `log_version_lookup_idx` (`object_id`,`object_class`,`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `ext_translations`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `ext_translations`;
CREATE TABLE IF NOT EXISTS `ext_translations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `locale` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `object_class` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `field` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `foreign_key` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lookup_unique_idx` (`locale`,`object_class`,`field`,`foreign_key`),
  KEY `translations_lookup_idx` (`locale`,`object_class`,`foreign_key`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=95 ;

--
-- Contenu de la table `ext_translations`
--

INSERT INTO `ext_translations` (`id`, `locale`, `object_class`, `field`, `foreign_key`, `content`) VALUES
(1, 'en', 'Unit_Model_PhysicalQuantity', 'name', '1', 'Without dimension'),
(2, 'en', 'Unit_Model_PhysicalQuantity', 'name', '2', 'Length'),
(3, 'en', 'Unit_Model_PhysicalQuantity', 'name', '3', 'Mass'),
(4, 'en', 'Unit_Model_PhysicalQuantity', 'name', '4', 'Time'),
(5, 'en', 'Unit_Model_PhysicalQuantity', 'name', '5', 'Numerical'),
(6, 'en', 'Unit_Model_PhysicalQuantity', 'name', '6', 'Area'),
(7, 'en', 'Unit_Model_PhysicalQuantity', 'name', '7', 'Speed'),
(8, 'en', 'Unit_Model_PhysicalQuantity', 'name', '9', 'Energy'),
(9, 'en', 'Unit_Model_PhysicalQuantity', 'name', '10', 'Power'),
(10, 'en', 'Unit_Model_Unit', 'name', '1', 'percent'),
(11, 'en', 'Unit_Model_Unit', 'symbol', '1', '%'),
(12, 'en', 'Unit_Model_Unit', 'name', '3', 'meter'),
(13, 'en', 'Unit_Model_Unit', 'symbol', '3', 'm'),
(14, 'en', 'Unit_Model_Unit', 'name', '4', 'kilometer'),
(15, 'en', 'Unit_Model_Unit', 'symbol', '4', 'km'),
(16, 'en', 'Unit_Model_Unit', 'name', '5', 'hundred kilometers'),
(17, 'en', 'Unit_Model_Unit', 'symbol', '5', '100 km'),
(18, 'en', 'Unit_Model_Unit', 'name', '6', 'thousand kilometers'),
(19, 'en', 'Unit_Model_Unit', 'symbol', '6', '1000 km'),
(20, 'en', 'Unit_Model_Unit', 'name', '7', 'gram'),
(21, 'en', 'Unit_Model_Unit', 'symbol', '7', 'g'),
(22, 'en', 'Unit_Model_Unit', 'name', '8', 'kilogram'),
(23, 'en', 'Unit_Model_Unit', 'symbol', '8', 'kg'),
(24, 'en', 'Unit_Model_Unit', 'name', '10', 'second'),
(25, 'en', 'Unit_Model_Unit', 'symbol', '10', 's'),
(26, 'en', 'Unit_Model_Unit', 'name', '11', 'hour'),
(27, 'en', 'Unit_Model_Unit', 'symbol', '11', 'h'),
(28, 'en', 'Unit_Model_Unit', 'name', '12', 'day'),
(29, 'en', 'Unit_Model_Unit', 'symbol', '12', 'day'),
(30, 'en', 'Unit_Model_Unit', 'name', '13', 'month'),
(31, 'en', 'Unit_Model_Unit', 'symbol', '13', 'month'),
(32, 'en', 'Unit_Model_Unit', 'name', '14', 'year'),
(33, 'en', 'Unit_Model_Unit', 'symbol', '14', 'yr'),
(34, 'en', 'Unit_Model_Unit', 'name', '18', 'square meter'),
(35, 'en', 'Unit_Model_Unit', 'symbol', '18', 'm²'),
(36, 'en', 'Unit_Model_Unit', 'name', '20', 'meter per second'),
(37, 'en', 'Unit_Model_Unit', 'symbol', '20', 'm/s'),
(38, 'en', 'Unit_Model_Unit', 'name', '21', 'knot'),
(39, 'en', 'Unit_Model_Unit', 'symbol', '21', 'kt'),
(40, 'en', 'Unit_Model_Unit', 'name', '22', 'liter'),
(41, 'en', 'Unit_Model_Unit', 'symbol', '22', 'ℓ'),
(42, 'en', 'Unit_Model_Unit', 'name', '23', 'hectoliter'),
(43, 'en', 'Unit_Model_Unit', 'symbol', '23', 'hℓ'),
(44, 'en', 'Unit_Model_Unit', 'name', '24', 'cubic metre'),
(45, 'en', 'Unit_Model_Unit', 'symbol', '24', 'm3'),
(46, 'en', 'Unit_Model_Unit', 'name', '25', 'barrel'),
(47, 'en', 'Unit_Model_Unit', 'symbol', '25', 'bbl'),
(48, 'en', 'Unit_Model_Unit', 'name', '28', 'megajoule'),
(49, 'en', 'Unit_Model_Unit', 'symbol', '28', 'MJ'),
(50, 'en', 'Unit_Model_Unit', 'name', '29', 'kilowatt-hour'),
(51, 'en', 'Unit_Model_Unit', 'symbol', '29', 'kWh'),
(52, 'en', 'Unit_Model_Unit', 'name', '31', 'megawatt-hour'),
(53, 'en', 'Unit_Model_Unit', 'symbol', '31', 'MWh'),
(54, 'en', 'Unit_Model_Unit', 'name', '32', 'gigawatt-hour'),
(55, 'en', 'Unit_Model_Unit', 'symbol', '32', 'GWh'),
(56, 'en', 'Unit_Model_Unit', 'name', '33', 'tonne of oil equivalent'),
(57, 'en', 'Unit_Model_Unit', 'symbol', '33', 'toe'),
(58, 'en', 'Unit_Model_PhysicalQuantity', 'name', '8', 'Volume'),
(59, 'en', 'Unit_Model_Unit', 'name', '39', 'device'),
(60, 'en', 'Unit_Model_Unit', 'symbol', '39', 'device'),
(61, 'en', 'Unit_Model_Unit', 'name', '40', 'bottle'),
(62, 'en', 'Unit_Model_Unit', 'symbol', '40', 'bottle'),
(63, 'en', 'Unit_Model_Unit', 'name', '41', 'housing'),
(64, 'en', 'Unit_Model_Unit', 'symbol', '41', 'housing'),
(65, 'en', 'Unit_Model_Unit', 'name', '42', 'man'),
(66, 'en', 'Unit_Model_Unit', 'symbol', '42', 'man'),
(67, 'en', 'Unit_Model_Unit', 'name', '45', 'passenger'),
(68, 'en', 'Unit_Model_Unit', 'symbol', '45', 'passenger'),
(69, 'en', 'Unit_Model_Unit', 'name', '46', 'person'),
(70, 'en', 'Unit_Model_Unit', 'symbol', '46', 'person'),
(71, 'en', 'Unit_Model_Unit', 'name', '47', 'meal'),
(72, 'en', 'Unit_Model_Unit', 'symbol', '47', 'meal'),
(73, 'en', 'Unit_Model_Unit', 'name', '48', 'unit'),
(74, 'en', 'Unit_Model_Unit', 'symbol', '48', 'unit'),
(75, 'en', 'Unit_Model_Unit', 'name', '49', 'vehicle'),
(76, 'en', 'Unit_Model_Unit', 'symbol', '49', 'vehicle'),
(77, 'en', 'Unit_Model_Unit', 'name', '50', 'visitor'),
(78, 'en', 'Unit_Model_Unit', 'symbol', '50', 'visitor'),
(79, 'en', 'Unit_Model_Unit_Extension', 'name', '1', 'CO2 equivalent'),
(80, 'en', 'Unit_Model_Unit_Extension', 'symbol', '1', 'CO2 eq.'),
(81, 'en', 'Unit_Model_Unit_Extension', 'name', '2', 'carbon equivalent'),
(82, 'en', 'Unit_Model_Unit_Extension', 'symbol', '2', 'C eq.'),
(83, 'en', 'Unit_Model_Unit', 'name', '51', 'gram CO2 equivalent'),
(84, 'en', 'Unit_Model_Unit', 'symbol', '51', 'g CO2 eq.'),
(85, 'en', 'Unit_Model_Unit', 'name', '52', 'kilogram CO2 equivalent'),
(86, 'en', 'Unit_Model_Unit', 'symbol', '52', 'kg CO2 eq.'),
(87, 'en', 'Unit_Model_Unit', 'name', '53', 'tonne CO2 equivalent'),
(88, 'en', 'Unit_Model_Unit', 'symbol', '53', 't CO2 eq.'),
(89, 'en', 'Unit_Model_Unit', 'name', '54', 'gram carbon equivalent'),
(90, 'en', 'Unit_Model_Unit', 'symbol', '54', 'g C eq.'),
(91, 'en', 'Unit_Model_Unit', 'name', '55', 'kilogram carbon equivalent'),
(92, 'en', 'Unit_Model_Unit', 'symbol', '55', 'kg C eq.'),
(93, 'en', 'Unit_Model_Unit', 'name', '56', 'tonne carbon equivalent'),
(94, 'en', 'Unit_Model_Unit', 'symbol', '56', 't C eq.');

-- --------------------------------------------------------

--
-- Structure de la table `inventory_association`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `inventory_association`;
CREATE TABLE IF NOT EXISTS `inventory_association` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `inventory_ordered`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `inventory_ordered`;
CREATE TABLE IF NOT EXISTS `inventory_ordered` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `position` int(11) NOT NULL,
  `context` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `inventory_simple`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `inventory_simple`;
CREATE TABLE IF NOT EXISTS `inventory_simple` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `creationDate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `inventory_simpleassociation`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `inventory_simpleassociation`;
CREATE TABLE IF NOT EXISTS `inventory_simpleassociation` (
  `associationid` int(11) NOT NULL,
  `simpleid` int(11) NOT NULL,
  PRIMARY KEY (`associationid`,`simpleid`),
  KEY `IDX_133087E5941F12C9` (`associationid`),
  KEY `IDX_133087E5D56788B8` (`simpleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `inventory_simpleexample`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `inventory_simpleexample`;
CREATE TABLE IF NOT EXISTS `inventory_simpleexample` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `inventory_translated`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `inventory_translated`;
CREATE TABLE IF NOT EXISTS `inventory_translated` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `keyword_association`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `keyword_association`;
CREATE TABLE IF NOT EXISTS `keyword_association` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idSubjectKeyword` int(11) DEFAULT NULL,
  `idObjectKeyword` int(11) DEFAULT NULL,
  `idPredicate` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `associationUniqueness` (`idSubjectKeyword`,`idObjectKeyword`,`idPredicate`),
  KEY `IDX_94D78DCA38188F90` (`idSubjectKeyword`),
  KEY `IDX_94D78DCAE879530A` (`idObjectKeyword`),
  KEY `IDX_94D78DCACCF1812D` (`idPredicate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `keyword_keyword`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `keyword_keyword`;
CREATE TABLE IF NOT EXISTS `keyword_keyword` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ref` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_124AB077146F3EA3` (`ref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `keyword_predicate`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `keyword_predicate`;
CREATE TABLE IF NOT EXISTS `keyword_predicate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ref` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `reverseRef` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `reverseLabel` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_80B4108D146F3EA3` (`ref`),
  UNIQUE KEY `UNIQ_80B4108D937092D8` (`reverseRef`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `orga_axis`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `orga_axis`;
CREATE TABLE IF NOT EXISTS `orga_axis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ref` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `contextualizing` tinyint(1) NOT NULL,
  `position` int(11) NOT NULL,
  `idOrganization` int(11) NOT NULL,
  `idDirectNarrower` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `axisRefUniquenessInOrganization` (`ref`,`idOrganization`),
  KEY `IDX_3AD44EC39F70C78D` (`idOrganization`),
  KEY `IDX_3AD44EC32F89D6F2` (`idDirectNarrower`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Contenu de la table `orga_axis`
--

INSERT INTO `orga_axis` (`id`, `ref`, `label`, `contextualizing`, `position`, `idOrganization`, `idDirectNarrower`) VALUES
(1, 'annee', 'Année', 0, 1, 1, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `orga_cell`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `orga_cell`;
CREATE TABLE IF NOT EXISTS `orga_cell` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `membersHashKey` varchar(7650) COLLATE utf8_unicode_ci NOT NULL,
  `relevant` tinyint(1) NOT NULL,
  `allParentsRelevant` tinyint(1) NOT NULL,
  `inventoryStatus` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `idDocLibraryForAFInputSetsPrimary` int(11) DEFAULT NULL,
  `idAFInputSetPrimary` int(11) DEFAULT NULL,
  `idDocBibliographyForAFInputSetPrimary` int(11) DEFAULT NULL,
  `idDWCube` int(11) DEFAULT NULL,
  `idDocLibraryForSocialGenericActions` int(11) DEFAULT NULL,
  `idDocLibraryForSocialContextActions` int(11) DEFAULT NULL,
  `idGranularity` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_745C4FDB84E76030` (`idDocLibraryForAFInputSetsPrimary`),
  UNIQUE KEY `UNIQ_745C4FDBBEDB9775` (`idAFInputSetPrimary`),
  UNIQUE KEY `UNIQ_745C4FDB1DFF84D5` (`idDocBibliographyForAFInputSetPrimary`),
  UNIQUE KEY `UNIQ_745C4FDB7A0D9634` (`idDWCube`),
  UNIQUE KEY `UNIQ_745C4FDB64F57618` (`idDocLibraryForSocialGenericActions`),
  UNIQUE KEY `UNIQ_745C4FDB20C970BF` (`idDocLibraryForSocialContextActions`),
  KEY `IDX_745C4FDBAF7E3C66` (`idGranularity`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Contenu de la table `orga_cell`
--

INSERT INTO `orga_cell` (`id`, `membersHashKey`, `relevant`, `allParentsRelevant`, `inventoryStatus`, `idDocLibraryForAFInputSetsPrimary`, `idAFInputSetPrimary`, `idDocBibliographyForAFInputSetPrimary`, `idDWCube`, `idDocLibraryForSocialGenericActions`, `idDocLibraryForSocialContextActions`, `idGranularity`) VALUES
(1, '', 1, 1, 'notLaunched', NULL, NULL, NULL, NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Structure de la table `orga_cellsgroup`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `orga_cellsgroup`;
CREATE TABLE IF NOT EXISTS `orga_cellsgroup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idContainerCell` int(11) DEFAULT NULL,
  `idInputGranularity` int(11) DEFAULT NULL,
  `idAF` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `CellAFUniqueness` (`idContainerCell`,`idInputGranularity`),
  KEY `IDX_262E11F21F8004D0` (`idContainerCell`),
  KEY `IDX_262E11F24CB6F1FF` (`idInputGranularity`),
  KEY `IDX_262E11F25E699E88` (`idAF`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `orga_cell_afinputsetprimarycomments`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `orga_cell_afinputsetprimarycomments`;
CREATE TABLE IF NOT EXISTS `orga_cell_afinputsetprimarycomments` (
  `idCell` int(11) NOT NULL,
  `idSocialComment` int(11) NOT NULL,
  PRIMARY KEY (`idCell`,`idSocialComment`),
  UNIQUE KEY `UNIQ_1CEA5B3B7655B281` (`idSocialComment`),
  KEY `IDX_1CEA5B3BB87AD97C` (`idCell`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `orga_cell_contextactions`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `orga_cell_contextactions`;
CREATE TABLE IF NOT EXISTS `orga_cell_contextactions` (
  `idCell` int(11) NOT NULL,
  `idSocialContextAction` int(11) NOT NULL,
  PRIMARY KEY (`idCell`,`idSocialContextAction`),
  UNIQUE KEY `UNIQ_288C8481ABA90A50` (`idSocialContextAction`),
  KEY `IDX_288C8481B87AD97C` (`idCell`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `orga_cell_dwresults`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `orga_cell_dwresults`;
CREATE TABLE IF NOT EXISTS `orga_cell_dwresults` (
  `idCell` int(11) NOT NULL,
  `idDWResult` int(11) NOT NULL,
  PRIMARY KEY (`idCell`,`idDWResult`),
  UNIQUE KEY `UNIQ_60EB5482156E9CC` (`idDWResult`),
  KEY `IDX_60EB548B87AD97C` (`idCell`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `orga_cell_genericactions`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `orga_cell_genericactions`;
CREATE TABLE IF NOT EXISTS `orga_cell_genericactions` (
  `idCell` int(11) NOT NULL,
  `idSocialGenericAction` int(11) NOT NULL,
  PRIMARY KEY (`idCell`,`idSocialGenericAction`),
  UNIQUE KEY `UNIQ_6CB082269382DE3C` (`idSocialGenericAction`),
  KEY `IDX_6CB08226B87AD97C` (`idCell`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `orga_cell_member`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `orga_cell_member`;
CREATE TABLE IF NOT EXISTS `orga_cell_member` (
  `idCell` int(11) NOT NULL,
  `idMember` int(11) NOT NULL,
  PRIMARY KEY (`idCell`,`idMember`),
  KEY `IDX_C00FE2CAB87AD97C` (`idCell`),
  KEY `IDX_C00FE2CA13F552E2` (`idMember`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `orga_granularity`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `orga_granularity`;
CREATE TABLE IF NOT EXISTS `orga_granularity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ref` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `navigable` tinyint(1) NOT NULL,
  `position` int(11) NOT NULL,
  `cellsWithACL` tinyint(1) NOT NULL,
  `cellsGenerateDWCubes` tinyint(1) NOT NULL,
  `cellsWithOrgaTab` tinyint(1) NOT NULL,
  `cellsWithAFConfigTab` tinyint(1) NOT NULL,
  `cellsWithSocialGenericActions` tinyint(1) NOT NULL,
  `cellsWithSocialContextActions` tinyint(1) NOT NULL,
  `cellsWithInputDocs` tinyint(1) NOT NULL,
  `idDWCube` int(11) DEFAULT NULL,
  `idOrganization` int(11) NOT NULL,
  `idInputConfigGranularity` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `granularityRefUniquenessInOrganization` (`ref`,`idOrganization`),
  UNIQUE KEY `UNIQ_9457AFC27A0D9634` (`idDWCube`),
  KEY `IDX_9457AFC29F70C78D` (`idOrganization`),
  KEY `IDX_9457AFC25A4F7F9` (`idInputConfigGranularity`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Contenu de la table `orga_granularity`
--

INSERT INTO `orga_granularity` (`id`, `ref`, `navigable`, `position`, `cellsWithACL`, `cellsGenerateDWCubes`, `cellsWithOrgaTab`, `cellsWithAFConfigTab`, `cellsWithSocialGenericActions`, `cellsWithSocialContextActions`, `cellsWithInputDocs`, `idDWCube`, `idOrganization`, `idInputConfigGranularity`) VALUES
(1, 'global', 1, 1, 0, 0, 0, 0, 0, 0, 0, NULL, 1, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `orga_granularity_axis`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `orga_granularity_axis`;
CREATE TABLE IF NOT EXISTS `orga_granularity_axis` (
  `idGranularity` int(11) NOT NULL,
  `idAxis` int(11) NOT NULL,
  PRIMARY KEY (`idGranularity`,`idAxis`),
  KEY `IDX_821B9C1CAF7E3C66` (`idGranularity`),
  KEY `IDX_821B9C1CF6F2D864` (`idAxis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `orga_granularity_dwreport`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `orga_granularity_dwreport`;
CREATE TABLE IF NOT EXISTS `orga_granularity_dwreport` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idGranularityDWReport` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_F7DF0FB4E6DE15BA` (`idGranularityDWReport`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `orga_granularity_dwreport_cell_dwreport`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `orga_granularity_dwreport_cell_dwreport`;
CREATE TABLE IF NOT EXISTS `orga_granularity_dwreport_cell_dwreport` (
  `idGranularityDWReport` int(11) NOT NULL,
  `idCellDWReport` int(11) NOT NULL,
  PRIMARY KEY (`idGranularityDWReport`,`idCellDWReport`),
  KEY `IDX_8D216F42E6DE15BA` (`idGranularityDWReport`),
  KEY `IDX_8D216F42136CB86D` (`idCellDWReport`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `orga_member`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `orga_member`;
CREATE TABLE IF NOT EXISTS `orga_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ref` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `parentMembersHashKey` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `label` varchar(1275) COLLATE utf8_unicode_ci NOT NULL,
  `idAxis` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `memberRefUniquenessInAxisAndParentMembersSashKey` (`ref`,`parentMembersHashKey`,`idAxis`),
  KEY `IDX_EBDF829BF6F2D864` (`idAxis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `orga_member_association`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `orga_member_association`;
CREATE TABLE IF NOT EXISTS `orga_member_association` (
  `idParent` int(11) NOT NULL,
  `idChild` int(11) NOT NULL,
  PRIMARY KEY (`idParent`,`idChild`),
  KEY `IDX_11D7D5B5E9FC8D5` (`idParent`),
  KEY `IDX_11D7D5B35771734` (`idChild`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `orga_organization`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `orga_organization`;
CREATE TABLE IF NOT EXISTS `orga_organization` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `idGranularityForInventoryStatus` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_4EF089EF632614CF` (`idGranularityForInventoryStatus`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Contenu de la table `orga_organization`
--

INSERT INTO `orga_organization` (`id`, `label`, `idGranularityForInventoryStatus`) VALUES
(1, 'Organisation test', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `simulation_scenario`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `simulation_scenario`;
CREATE TABLE IF NOT EXISTS `simulation_scenario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `idAFInputSetPrimary` int(11) DEFAULT NULL,
  `idDWMember` int(11) NOT NULL,
  `idSet` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_26EFBDE442D8D2A7` (`idDWMember`),
  UNIQUE KEY `UNIQ_26EFBDE4BEDB9775` (`idAFInputSetPrimary`),
  KEY `IDX_26EFBDE4C75C385B` (`idSet`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `simulation_scenario_dwresults`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `simulation_scenario_dwresults`;
CREATE TABLE IF NOT EXISTS `simulation_scenario_dwresults` (
  `idScenario` int(11) NOT NULL,
  `idDWResult` int(11) NOT NULL,
  PRIMARY KEY (`idScenario`,`idDWResult`),
  UNIQUE KEY `UNIQ_CBC5AE3B2156E9CC` (`idDWResult`),
  KEY `IDX_CBC5AE3B6DE6E283` (`idScenario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `simulation_set`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `simulation_set`;
CREATE TABLE IF NOT EXISTS `simulation_set` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `idDWCube` int(11) NOT NULL,
  `idDWAxis` int(11) NOT NULL,
  `idUser` int(11) NOT NULL,
  `idAF` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_E11EE25C7A0D9634` (`idDWCube`),
  UNIQUE KEY `UNIQ_E11EE25CCFFCA176` (`idDWAxis`),
  KEY `IDX_E11EE25CFE6E88D7` (`idUser`),
  KEY `IDX_E11EE25C5E699E88` (`idAF`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `social_action`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `social_action`;
CREATE TABLE IF NOT EXISTS `social_action` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `idBibliography` int(11) DEFAULT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_ED55A08DD2D14599` (`idBibliography`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `social_actionkeyfigure`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `social_actionkeyfigure`;
CREATE TABLE IF NOT EXISTS `social_actionkeyfigure` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `unitRef` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `social_action_comments`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `social_action_comments`;
CREATE TABLE IF NOT EXISTS `social_action_comments` (
  `idAction` int(11) NOT NULL,
  `idComment` int(11) NOT NULL,
  PRIMARY KEY (`idAction`,`idComment`),
  KEY `IDX_6E487FC424DD2408` (`idAction`),
  KEY `IDX_6E487FC484CD399E` (`idComment`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `social_comment`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `social_comment`;
CREATE TABLE IF NOT EXISTS `social_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text` longtext COLLATE utf8_unicode_ci,
  `creationDate` datetime NOT NULL,
  `idAuthor` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_19D6C6B5DEBE7052` (`idAuthor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `social_contextaction`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `social_contextaction`;
CREATE TABLE IF NOT EXISTS `social_contextaction` (
  `id` int(11) NOT NULL,
  `launchDate` datetime DEFAULT NULL,
  `targetDate` datetime DEFAULT NULL,
  `achievementDate` datetime DEFAULT NULL,
  `progress` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `personInCharge` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `idGenericAction` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_84779C2C69808104` (`idGenericAction`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `social_contextactionkeyfigure`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `social_contextactionkeyfigure`;
CREATE TABLE IF NOT EXISTS `social_contextactionkeyfigure` (
  `value` double DEFAULT NULL,
  `idActionKeyFigure` int(11) NOT NULL,
  `idContextAction` int(11) NOT NULL,
  PRIMARY KEY (`idActionKeyFigure`,`idContextAction`),
  KEY `IDX_7208CDDE52C14EAD` (`idActionKeyFigure`),
  KEY `IDX_7208CDDE51AB5568` (`idContextAction`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `social_genericaction`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `social_genericaction`;
CREATE TABLE IF NOT EXISTS `social_genericaction` (
  `id` int(11) NOT NULL,
  `idTheme` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_BC5C484080B1A415` (`idTheme`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `social_message`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `social_message`;
CREATE TABLE IF NOT EXISTS `social_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text` longtext COLLATE utf8_unicode_ci,
  `creationDate` datetime NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sent` tinyint(1) NOT NULL,
  `idAuthor` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_3B1FA4A6DEBE7052` (`idAuthor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `social_message_group_recipients`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `social_message_group_recipients`;
CREATE TABLE IF NOT EXISTS `social_message_group_recipients` (
  `idMessage` int(11) NOT NULL,
  `idGroup` int(11) NOT NULL,
  PRIMARY KEY (`idMessage`,`idGroup`),
  KEY `IDX_6AE4E9F0A6045B8D` (`idMessage`),
  KEY `IDX_6AE4E9F07A0407D8` (`idGroup`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `social_message_user_recipients`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `social_message_user_recipients`;
CREATE TABLE IF NOT EXISTS `social_message_user_recipients` (
  `idMessage` int(11) NOT NULL,
  `idUser` int(11) NOT NULL,
  PRIMARY KEY (`idMessage`,`idUser`),
  KEY `IDX_8DB9A0B7A6045B8D` (`idMessage`),
  KEY `IDX_8DB9A0B7FE6E88D7` (`idUser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `social_news`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `social_news`;
CREATE TABLE IF NOT EXISTS `social_news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text` longtext COLLATE utf8_unicode_ci,
  `creationDate` datetime NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `publicationDate` datetime DEFAULT NULL,
  `published` tinyint(1) NOT NULL,
  `idAuthor` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_746C6ADDDEBE7052` (`idAuthor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `social_news_comments`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `social_news_comments`;
CREATE TABLE IF NOT EXISTS `social_news_comments` (
  `idNews` int(11) NOT NULL,
  `idComment` int(11) NOT NULL,
  PRIMARY KEY (`idNews`,`idComment`),
  KEY `IDX_F0EEC2476E2EC7CE` (`idNews`),
  KEY `IDX_F0EEC24784CD399E` (`idComment`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `social_theme`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `social_theme`;
CREATE TABLE IF NOT EXISTS `social_theme` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `social_usergroup`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `social_usergroup`;
CREATE TABLE IF NOT EXISTS `social_usergroup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ref` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_2C3DF3BA146F3EA3` (`ref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `social_usergroup_users`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `social_usergroup_users`;
CREATE TABLE IF NOT EXISTS `social_usergroup_users` (
  `idUserGroup` int(11) NOT NULL,
  `idUser` int(11) NOT NULL,
  PRIMARY KEY (`idUserGroup`,`idUser`),
  KEY `IDX_D8A9E317774F7C45` (`idUserGroup`),
  KEY `IDX_D8A9E317FE6E88D7` (`idUser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `techno_category`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `techno_category`;
CREATE TABLE IF NOT EXISTS `techno_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  `idParentCategory` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_5392070E2526073F` (`idParentCategory`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `techno_component`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `techno_component`;
CREATE TABLE IF NOT EXISTS `techno_component` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `documentation` longtext COLLATE utf8_unicode_ci,
  `refBaseUnit` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `refUnit` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `techno_component_tags`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `techno_component_tags`;
CREATE TABLE IF NOT EXISTS `techno_component_tags` (
  `idComponent` int(11) NOT NULL,
  `idTag` int(11) NOT NULL,
  PRIMARY KEY (`idComponent`,`idTag`),
  UNIQUE KEY `UNIQ_E03C2B5D22C1AA04` (`idTag`),
  KEY `IDX_E03C2B5DB5148A01` (`idComponent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `techno_element`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `techno_element`;
CREATE TABLE IF NOT EXISTS `techno_element` (
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `techno_element_coeff`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `techno_element_coeff`;
CREATE TABLE IF NOT EXISTS `techno_element_coeff` (
  `id` int(11) NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `techno_element_process`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `techno_element_process`;
CREATE TABLE IF NOT EXISTS `techno_element_process` (
  `id` int(11) NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `techno_family`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `techno_family`;
CREATE TABLE IF NOT EXISTS `techno_family` (
  `id` int(11) NOT NULL,
  `ref` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `position` int(11) NOT NULL,
  `idCategory` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_26CF4071146F3EA3` (`ref`),
  KEY `IDX_26CF407155EF339A` (`idCategory`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `techno_family_cell`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `techno_family_cell`;
CREATE TABLE IF NOT EXISTS `techno_family_cell` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `membersHashKey` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `idFamily` int(11) NOT NULL,
  `idChosenElement` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `coordinates` (`idFamily`,`membersHashKey`),
  KEY `IDX_301B9BDCC6F789C1` (`idFamily`),
  KEY `IDX_301B9BDC46A12416` (`idChosenElement`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `techno_family_cells_common_tags`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `techno_family_cells_common_tags`;
CREATE TABLE IF NOT EXISTS `techno_family_cells_common_tags` (
  `idFamily` int(11) NOT NULL,
  `idTag` int(11) NOT NULL,
  PRIMARY KEY (`idFamily`,`idTag`),
  UNIQUE KEY `UNIQ_1F9BC23F22C1AA04` (`idTag`),
  KEY `IDX_1F9BC23FC6F789C1` (`idFamily`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `techno_family_cell_members`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `techno_family_cell_members`;
CREATE TABLE IF NOT EXISTS `techno_family_cell_members` (
  `idCell` int(11) NOT NULL,
  `idMember` int(11) NOT NULL,
  PRIMARY KEY (`idCell`,`idMember`),
  KEY `IDX_8A180155B87AD97C` (`idCell`),
  KEY `IDX_8A18015513F552E2` (`idMember`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `techno_family_coeff`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `techno_family_coeff`;
CREATE TABLE IF NOT EXISTS `techno_family_coeff` (
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `techno_family_dimension`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `techno_family_dimension`;
CREATE TABLE IF NOT EXISTS `techno_family_dimension` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `position` int(11) NOT NULL,
  `orientation` int(11) NOT NULL,
  `query` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `idFamily` int(11) NOT NULL,
  `idMeaning` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `family_meaning` (`idFamily`,`idMeaning`),
  KEY `IDX_CD0456BEC6F789C1` (`idFamily`),
  KEY `IDX_CD0456BE2F889BF0` (`idMeaning`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `techno_family_member`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `techno_family_member`;
CREATE TABLE IF NOT EXISTS `techno_family_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `refKeyword` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  `idDimension` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniqueMembers` (`idDimension`,`refKeyword`),
  KEY `IDX_252046D63671EACA` (`idDimension`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `techno_family_process`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `techno_family_process`;
CREATE TABLE IF NOT EXISTS `techno_family_process` (
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `techno_meaning`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `techno_meaning`;
CREATE TABLE IF NOT EXISTS `techno_meaning` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `refKeyword` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_E40910B51904C7DD` (`refKeyword`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `techno_tag`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `techno_tag`;
CREATE TABLE IF NOT EXISTS `techno_tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `meaning_id` int(11) DEFAULT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_DDCB964620A7F0E6` (`meaning_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `tec_component`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `tec_component`;
CREATE TABLE IF NOT EXISTS `tec_component` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `modifier` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `idParent` int(11) DEFAULT NULL,
  `nodeType` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_44344EF45E9FC8D5` (`idParent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `tec_composite`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `tec_composite`;
CREATE TABLE IF NOT EXISTS `tec_composite` (
  `id` int(11) NOT NULL,
  `operator` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tec_expression`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `tec_expression`;
CREATE TABLE IF NOT EXISTS `tec_expression` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `expression` longtext COLLATE utf8_unicode_ci,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `rootNode` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_97E26EBC750166F` (`rootNode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `tec_leaf`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `tec_leaf`;
CREATE TABLE IF NOT EXISTS `tec_leaf` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `unit_discreteunit`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `unit_discreteunit`;
CREATE TABLE IF NOT EXISTS `unit_discreteunit` (
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Contenu de la table `unit_discreteunit`
--

INSERT INTO `unit_discreteunit` (`id`) VALUES
(38),
(39),
(40),
(41),
(42),
(43),
(44),
(45),
(46),
(47),
(48),
(49),
(50);

-- --------------------------------------------------------

--
-- Structure de la table `unit_extendedunit`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `unit_extendedunit`;
CREATE TABLE IF NOT EXISTS `unit_extendedunit` (
  `id` int(11) NOT NULL,
  `multiplier` int(11) NOT NULL,
  `idExtentedUnit` int(11) DEFAULT NULL,
  `idStandardUnit` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_51496D378C0D5E9D` (`idExtentedUnit`),
  KEY `IDX_51496D379C15CDB5` (`idStandardUnit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Contenu de la table `unit_extendedunit`
--

INSERT INTO `unit_extendedunit` (`id`, `multiplier`, `idExtentedUnit`, `idStandardUnit`) VALUES
(51, 0, 1, 7),
(52, 1, 1, 8),
(53, 1000, 1, 9),
(54, 0, 2, 7),
(55, 4, 2, 8),
(56, 4000, 2, 9);

-- --------------------------------------------------------

--
-- Structure de la table `unit_physicalquantity`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `unit_physicalquantity`;
CREATE TABLE IF NOT EXISTS `unit_physicalquantity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ref` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `symbol` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `isBase` tinyint(1) NOT NULL,
  `idReferenceUnit` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_43B52022146F3EA3` (`ref`),
  KEY `IDX_43B520223FCED0B8` (`idReferenceUnit`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=11 ;

--
-- Contenu de la table `unit_physicalquantity`
--

INSERT INTO `unit_physicalquantity` (`id`, `ref`, `name`, `symbol`, `isBase`, `idReferenceUnit`) VALUES
(1, 'sans_dimension', 'Sans dimension', NULL, 0, 2),
(2, 'l', 'Longueur', 'L', 1, 3),
(3, 'm', 'Masse', 'M', 1, 8),
(4, 't', 'Temps', 'T', 1, 10),
(5, 'numeraire', 'Numéraire', 'Num', 1, 15),
(6, 'l2', 'Surface', 'L2', 0, 18),
(7, 'l/t', 'Vitesse', 'L/T', 0, 20),
(8, 'l3', 'Volume', 'L3', 0, 24),
(9, 'ml2/t2', 'Énergie', 'ML2/T2', 0, 26),
(10, 'ml2/t3', 'Puissance', 'ML2/T3', 0, 34);

-- --------------------------------------------------------

--
-- Structure de la table `unit_physicalquantitycomponent`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `unit_physicalquantitycomponent`;
CREATE TABLE IF NOT EXISTS `unit_physicalquantitycomponent` (
  `exponent` int(11) NOT NULL,
  `idDerivedPhysicalQuantity` int(11) NOT NULL,
  `idBasePhysicalQuantity` int(11) NOT NULL,
  PRIMARY KEY (`idDerivedPhysicalQuantity`,`idBasePhysicalQuantity`),
  KEY `IDX_65FFF342D600A4FE` (`idDerivedPhysicalQuantity`),
  KEY `IDX_65FFF3422F4F1DF8` (`idBasePhysicalQuantity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Contenu de la table `unit_physicalquantitycomponent`
--

INSERT INTO `unit_physicalquantitycomponent` (`exponent`, `idDerivedPhysicalQuantity`, `idBasePhysicalQuantity`) VALUES
(0, 1, 2),
(0, 1, 3),
(0, 1, 4),
(0, 1, 5),
(1, 2, 2),
(1, 3, 3),
(1, 4, 4),
(1, 5, 5),
(2, 6, 2),
(0, 6, 3),
(0, 6, 4),
(0, 6, 5),
(1, 7, 2),
(0, 7, 3),
(-1, 7, 4),
(0, 7, 5),
(3, 8, 2),
(0, 8, 3),
(0, 8, 4),
(0, 8, 5),
(2, 9, 2),
(1, 9, 3),
(-2, 9, 4),
(0, 9, 5),
(2, 10, 2),
(1, 10, 3),
(-3, 10, 4),
(0, 10, 5);

-- --------------------------------------------------------

--
-- Structure de la table `unit_standardunit`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `unit_standardunit`;
CREATE TABLE IF NOT EXISTS `unit_standardunit` (
  `id` int(11) NOT NULL,
  `multiplier` double NOT NULL,
  `idPhysicalQuantity` int(11) DEFAULT NULL,
  `idUnitSystem` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_268F62D185CF6229` (`idPhysicalQuantity`),
  KEY `IDX_268F62D14D1F98C0` (`idUnitSystem`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Contenu de la table `unit_standardunit`
--

INSERT INTO `unit_standardunit` (`id`, `multiplier`, `idPhysicalQuantity`, `idUnitSystem`) VALUES
(1, 0.01, 1, 1),
(2, 1, 1, 1),
(3, 1, 2, 1),
(4, 1000, 2, 1),
(5, 100000, 2, 1),
(6, 1000000, 2, 1),
(7, 0.001, 3, 1),
(8, 1, 3, 1),
(9, 1000, 3, 1),
(10, 1, 4, 1),
(11, 3600, 4, 1),
(12, 86400, 4, 1),
(13, 2629743.83, 4, 1),
(14, 31556925.96, 4, 1),
(15, 1, 5, 1),
(16, 1000, 5, 1),
(17, 0.7671123589, 5, 1),
(18, 1, 6, 1),
(19, 10000, 6, 1),
(20, 1, 7, 1),
(21, 0.51444444, 7, 2),
(22, 0.001, 8, 1),
(23, 0.1, 8, 1),
(24, 1, 8, 1),
(25, 0.1589873, 8, 1),
(26, 1, 9, 1),
(27, 1000, 9, 1),
(28, 1000000, 9, 1),
(29, 3600000, 9, 1),
(30, 1000000000, 9, 1),
(31, 3600000000, 9, 1),
(32, 3600000000000, 9, 1),
(33, 41868000000, 9, 1),
(34, 1, 10, 1),
(35, 1000, 10, 1),
(36, 1000000, 10, 1),
(37, 1000000000, 10, 1);

-- --------------------------------------------------------

--
-- Structure de la table `unit_unit`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `unit_unit`;
CREATE TABLE IF NOT EXISTS `unit_unit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ref` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `symbol` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `unitType` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_72BEC094146F3EA3` (`ref`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=57 ;

--
-- Contenu de la table `unit_unit`
--

INSERT INTO `unit_unit` (`id`, `ref`, `name`, `symbol`, `unitType`) VALUES
(1, 'pourcent', 'pourcent', '%', 'standard'),
(2, 'un', '', '', 'standard'),
(3, 'm', 'mètre', 'm', 'standard'),
(4, 'km', 'kilomètre', 'km', 'standard'),
(5, '100km', 'cent kilomètres', '100 km', 'standard'),
(6, '1000km', 'mille kilomètres', '1000 km', 'standard'),
(7, 'g', 'gramme', 'g', 'standard'),
(8, 'kg', 'kilogramme', 'kg', 'standard'),
(9, 't', 'tonne', 't', 'standard'),
(10, 's', 'seconde', 's', 'standard'),
(11, 'h', 'heure', 'h', 'standard'),
(12, 'jour', 'jour', 'jour', 'standard'),
(13, 'mois', 'mois', 'mois', 'standard'),
(14, 'an', 'an', 'an', 'standard'),
(15, 'euro', 'euro', '€', 'standard'),
(16, 'kiloeuro', 'kiloeuro', 'k€', 'standard'),
(17, 'dollar', 'dollar', '$', 'standard'),
(18, 'm2', 'mètre carré', 'm²', 'standard'),
(19, 'ha', 'hectare', 'ha', 'standard'),
(20, 'm/s', 'mètre par seconde', 'm/s', 'standard'),
(21, 'noeud', 'nœud', 'kt', 'standard'),
(22, 'l', 'litre', 'ℓ', 'standard'),
(23, 'hl', 'hectolitre', 'hℓ', 'standard'),
(24, 'm3', 'mètre cube', 'm3', 'standard'),
(25, 'bl', 'baril', 'bl', 'standard'),
(26, 'j', 'joule', 'J', 'standard'),
(27, 'kj', 'kilojoule', 'kJ', 'standard'),
(28, 'mj', 'mégajoule', 'MJ', 'standard'),
(29, 'kwh', 'kilowatt-heure', 'kWh', 'standard'),
(30, 'gj', 'gigajoule', 'GJ', 'standard'),
(31, 'mwh', 'mégawatt-heure', 'MWh', 'standard'),
(32, 'gwh', 'gigawatt-heure', 'GWh', 'standard'),
(33, 'tep', 'tonne équivalent pétrole', 'TEP', 'standard'),
(34, 'w', 'watt', 'W', 'standard'),
(35, 'kw', 'kilowatt', 'kW', 'standard'),
(36, 'mw', 'megawatt', 'MW', 'standard'),
(37, 'gw', 'gigawatt', 'GW', 'standard'),
(38, 'animal', 'animal', 'animal', 'discrete'),
(39, 'appareil', 'appareil', 'appareil', 'discrete'),
(40, 'bouteille', 'bouteille', 'bouteille', 'discrete'),
(41, 'logement', 'logement', 'logement', 'discrete'),
(42, 'homme', 'homme', 'homme', 'discrete'),
(43, 'machine', 'machine', 'machine', 'discrete'),
(44, 'occupant', 'occupant', 'occupant', 'discrete'),
(45, 'passager', 'passager', 'passager', 'discrete'),
(46, 'personne', 'personne', 'personne', 'discrete'),
(47, 'repas', 'repas', 'repas', 'discrete'),
(48, 'unite', 'unité', 'unité', 'discrete'),
(49, 'vehicule', 'véhicule', 'véhicule', 'discrete'),
(50, 'visiteur', 'visiteur', 'visiteur', 'discrete'),
(51, 'g_co2e', 'gramme équivalent CO2', 'g équ. CO2', 'extended'),
(52, 'kg_co2e', 'kilogramme équivalent CO2', 'kg équ. CO2', 'extended'),
(53, 't_co2e', 'tonne équivalent CO2', 't équ. CO2', 'extended'),
(54, 'g_ce', 'gramme équivalent carbone', 'g équ. C', 'extended'),
(55, 'kg_ce', 'kilogramme équivalent carbone', 'kg équ. C', 'extended'),
(56, 't_ce', 'tonne équivalent carbone', 't équ. C', 'extended');

-- --------------------------------------------------------

--
-- Structure de la table `unit_unitextension`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `unit_unitextension`;
CREATE TABLE IF NOT EXISTS `unit_unitextension` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ref` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `symbol` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `multiplier` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_C63973E6146F3EA3` (`ref`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Contenu de la table `unit_unitextension`
--

INSERT INTO `unit_unitextension` (`id`, `ref`, `name`, `symbol`, `multiplier`) VALUES
(1, 'co2e', 'équivalent CO2', 'équ. CO2', 1),
(2, 'ce', 'équivalent carbone', 'équ. C', 4);

-- --------------------------------------------------------

--
-- Structure de la table `unit_unitsystem`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `unit_unitsystem`;
CREATE TABLE IF NOT EXISTS `unit_unitsystem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ref` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_BDB2DA4146F3EA3` (`ref`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Contenu de la table `unit_unitsystem`
--

INSERT INTO `unit_unitsystem` (`id`, `ref`, `name`) VALUES
(1, 'international', 'International'),
(2, 'anglo_saxon', 'Anglo-saxon');

-- --------------------------------------------------------

--
-- Structure de la table `user_authorization`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `user_authorization`;
CREATE TABLE IF NOT EXISTS `user_authorization` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `idIdentity` int(11) NOT NULL,
  `idResource` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniqueAuthorization` (`idIdentity`,`action`,`idResource`),
  KEY `IDX_7ADBD57A3936C39F` (`idIdentity`),
  KEY `IDX_7ADBD57AEF32DE4D` (`idResource`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=31 ;

--
-- Contenu de la table `user_authorization`
--

INSERT INTO `user_authorization` (`id`, `action`, `idIdentity`, `idResource`) VALUES
(1, 'User_Model_Action_Default::1', 1, 5),
(4, 'User_Model_Action_Default::1', 2, 1),
(9, 'User_Model_Action_Default::1', 2, 4),
(2, 'User_Model_Action_Default::1', 2, 5),
(8, 'User_Model_Action_Default::16', 2, 2),
(5, 'User_Model_Action_Default::2', 2, 1),
(11, 'User_Model_Action_Default::2', 2, 4),
(6, 'User_Model_Action_Default::4', 2, 2),
(10, 'User_Model_Action_Default::4', 2, 4),
(3, 'User_Model_Action_Default::4', 2, 5),
(7, 'User_Model_Action_Default::8', 2, 2),
(12, 'User_Model_Action_Default::8', 2, 4),
(13, 'User_Model_Action_Default::1', 3, 6),
(14, 'User_Model_Action_Default::4', 3, 6),
(15, 'User_Model_Action_Default::1', 4, 7),
(16, 'User_Model_Action_Default::4', 4, 7),
(17, 'User_Model_Action_Default::8', 4, 7),
(22, 'Orga_Action_Cell::256', 5, 8),
(23, 'Orga_Action_Cell::512', 5, 8),
(18, 'User_Model_Action_Default::1', 5, 7),
(19, 'User_Model_Action_Default::1', 5, 8),
(21, 'User_Model_Action_Default::128', 5, 8),
(20, 'User_Model_Action_Default::4', 5, 8),
(26, 'Orga_Action_Cell::256', 6, 8),
(27, 'Orga_Action_Cell::512', 6, 8),
(24, 'User_Model_Action_Default::1', 6, 7),
(25, 'User_Model_Action_Default::1', 6, 8),
(30, 'Orga_Action_Cell::256', 7, 8),
(28, 'User_Model_Action_Default::1', 7, 7),
(29, 'User_Model_Action_Default::1', 7, 8);

-- --------------------------------------------------------

--
-- Structure de la table `user_resource`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `user_resource`;
CREATE TABLE IF NOT EXISTS `user_resource` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `resource_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `entityName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `entityIdentifier` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_161439B55E237E06` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=9 ;

--
-- Contenu de la table `user_resource`
--

INSERT INTO `user_resource` (`id`, `resource_type`, `entityName`, `entityIdentifier`, `name`) VALUES
(1, 'entityResource', 'User_Model_User', NULL, NULL),
(2, 'entityResource', 'User_Model_Role', 1, NULL),
(3, 'entityResource', 'User_Model_Role', 2, NULL),
(4, 'entityResource', 'Orga_Model_Organization', NULL, NULL),
(5, 'namedResource', NULL, NULL, 'referential'),
(6, 'entityResource', 'User_Model_User', 3, NULL),
(7, 'entityResource', 'Orga_Model_Organization', 1, NULL),
(8, 'entityResource', 'Orga_Model_Cell', 1, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `user_role`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `user_role`;
CREATE TABLE IF NOT EXISTS `user_role` (
  `id` int(11) NOT NULL,
  `ref` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_F2BEB3E146F3EA3` (`ref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Contenu de la table `user_role`
--

INSERT INTO `user_role` (`id`, `ref`, `name`) VALUES
(1, 'user', 'Utilisateur'),
(2, 'sysadmin', 'Administrateur système'),
(4, 'organizationAdministrator_1', 'organizationAdministrator'),
(5, 'cellAdministrator_1', 'cellAdministrator'),
(6, 'cellContributor_1', 'cellContributor'),
(7, 'cellObserver_1', 'cellObserver');

-- --------------------------------------------------------

--
-- Structure de la table `user_securityidentity`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `user_securityidentity`;
CREATE TABLE IF NOT EXISTS `user_securityidentity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=8 ;

--
-- Contenu de la table `user_securityidentity`
--

INSERT INTO `user_securityidentity` (`id`, `type`) VALUES
(1, 'role'),
(2, 'role'),
(3, 'user'),
(4, 'role'),
(5, 'role'),
(6, 'role'),
(7, 'role');

-- --------------------------------------------------------

--
-- Structure de la table `user_user`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `user_user`;
CREATE TABLE IF NOT EXISTS `user_user` (
  `id` int(11) NOT NULL,
  `lastName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `firstName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `emailValidated` tinyint(1) NOT NULL,
  `emailKey` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `creationDate` datetime NOT NULL,
  `locale` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_D5D1B71DE7927C74` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Contenu de la table `user_user`
--

INSERT INTO `user_user` (`id`, `lastName`, `firstName`, `email`, `emailValidated`, `emailKey`, `enabled`, `password`, `creationDate`, `locale`) VALUES
(3, 'Administrateur', NULL, 'admin', 1, NULL, 1, 'b2302c515a17bdb2bbe22b8fedaff704', '2013-06-26 08:59:35', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `user_userroles`
--
-- Création: Mar 25 Juin 2013 à 08:54
--

DROP TABLE IF EXISTS `user_userroles`;
CREATE TABLE IF NOT EXISTS `user_userroles` (
  `idUser` int(11) NOT NULL,
  `idRole` int(11) NOT NULL,
  PRIMARY KEY (`idUser`,`idRole`),
  KEY `IDX_1F2E4A8EFE6E88D7` (`idUser`),
  KEY `IDX_1F2E4A8E2494D4F4` (`idRole`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Contenu de la table `user_userroles`
--

INSERT INTO `user_userroles` (`idUser`, `idRole`) VALUES
(3, 2),
(3, 4),
(3, 5);

--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `af_action`
--
ALTER TABLE `af_action`
  ADD CONSTRAINT `FK_E42D79D9413CA315` FOREIGN KEY (`idCondition`) REFERENCES `af_condition` (`id`),
  ADD CONSTRAINT `FK_E42D79D956BE68EA` FOREIGN KEY (`idTargetComponent`) REFERENCES `af_component` (`id`);

--
-- Contraintes pour la table `af_action_setalgovalue`
--
ALTER TABLE `af_action_setalgovalue`
  ADD CONSTRAINT `FK_B35712EBBF396750` FOREIGN KEY (`id`) REFERENCES `af_action` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_B35712EB675FA209` FOREIGN KEY (`idAlgo`) REFERENCES `algo_algo` (`id`);

--
-- Contraintes pour la table `af_action_setoptionstate`
--
ALTER TABLE `af_action_setoptionstate`
  ADD CONSTRAINT `FK_15CE5C0BBF396750` FOREIGN KEY (`id`) REFERENCES `af_action` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_15CE5C0B3997A82A` FOREIGN KEY (`idOption`) REFERENCES `af_component_select_option` (`id`);

--
-- Contraintes pour la table `af_action_setstate`
--
ALTER TABLE `af_action_setstate`
  ADD CONSTRAINT `FK_5502344ABF396750` FOREIGN KEY (`id`) REFERENCES `af_action` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `af_action_setvalue`
--
ALTER TABLE `af_action_setvalue`
  ADD CONSTRAINT `FK_EBE6BE85BF396750` FOREIGN KEY (`id`) REFERENCES `af_action` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `af_action_setvalue_checkbox`
--
ALTER TABLE `af_action_setvalue_checkbox`
  ADD CONSTRAINT `FK_3A80D622BF396750` FOREIGN KEY (`id`) REFERENCES `af_action` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `af_action_setvalue_numeric`
--
ALTER TABLE `af_action_setvalue_numeric`
  ADD CONSTRAINT `FK_8DCF5B8BF396750` FOREIGN KEY (`id`) REFERENCES `af_action` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `af_action_setvalue_select_multi`
--
ALTER TABLE `af_action_setvalue_select_multi`
  ADD CONSTRAINT `FK_CF36C797BF396750` FOREIGN KEY (`id`) REFERENCES `af_action` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_CF36C7973ADB05F1` FOREIGN KEY (`options_id`) REFERENCES `af_component_select_option` (`id`);

--
-- Contraintes pour la table `af_action_setvalue_select_single`
--
ALTER TABLE `af_action_setvalue_select_single`
  ADD CONSTRAINT `FK_D4AC72F5BF396750` FOREIGN KEY (`id`) REFERENCES `af_action` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_D4AC72F53997A82A` FOREIGN KEY (`idOption`) REFERENCES `af_component_select_option` (`id`);

--
-- Contraintes pour la table `af_af`
--
ALTER TABLE `af_af`
  ADD CONSTRAINT `FK_CF3726955EF339A` FOREIGN KEY (`idCategory`) REFERENCES `af_category` (`id`),
  ADD CONSTRAINT `FK_CF372694A1389BB` FOREIGN KEY (`idRootGroup`) REFERENCES `af_component_group` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `FK_CF37269B66DCADA` FOREIGN KEY (`idAlgoSet`) REFERENCES `algo_set` (`id`),
  ADD CONSTRAINT `FK_CF37269CC831C02` FOREIGN KEY (`idMainAlgo`) REFERENCES `algo_selection_main` (`id`);

--
-- Contraintes pour la table `af_category`
--
ALTER TABLE `af_category`
  ADD CONSTRAINT `FK_D8162A7D2526073F` FOREIGN KEY (`idParentCategory`) REFERENCES `af_category` (`id`);

--
-- Contraintes pour la table `af_component`
--
ALTER TABLE `af_component`
  ADD CONSTRAINT `FK_8BF704C37A0407D8` FOREIGN KEY (`idGroup`) REFERENCES `af_component_group` (`id`),
  ADD CONSTRAINT `FK_8BF704C35E699E88` FOREIGN KEY (`idAF`) REFERENCES `af_af` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `af_component_checkbox`
--
ALTER TABLE `af_component_checkbox`
  ADD CONSTRAINT `FK_1963FEE0BF396750` FOREIGN KEY (`id`) REFERENCES `af_component` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `af_component_field`
--
ALTER TABLE `af_component_field`
  ADD CONSTRAINT `FK_84219665BF396750` FOREIGN KEY (`id`) REFERENCES `af_component` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `af_component_group`
--
ALTER TABLE `af_component_group`
  ADD CONSTRAINT `FK_B21497F8BF396750` FOREIGN KEY (`id`) REFERENCES `af_component` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `af_component_numeric`
--
ALTER TABLE `af_component_numeric`
  ADD CONSTRAINT `FK_3293884FBF396750` FOREIGN KEY (`id`) REFERENCES `af_component` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `af_component_select`
--
ALTER TABLE `af_component_select`
  ADD CONSTRAINT `FK_13457202BF396750` FOREIGN KEY (`id`) REFERENCES `af_component` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `af_component_select_multi`
--
ALTER TABLE `af_component_select_multi`
  ADD CONSTRAINT `FK_F218271FBF396750` FOREIGN KEY (`id`) REFERENCES `af_component` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `af_component_select_multi_defaultvalues`
--
ALTER TABLE `af_component_select_multi_defaultvalues`
  ADD CONSTRAINT `FK_67AB7A012098DCB0` FOREIGN KEY (`idSelectOption`) REFERENCES `af_component_select_option` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_67AB7A01B117EB8E` FOREIGN KEY (`idSelectMulti`) REFERENCES `af_component_select_multi` (`id`);

--
-- Contraintes pour la table `af_component_select_option`
--
ALTER TABLE `af_component_select_option`
  ADD CONSTRAINT `FK_A7D370AE28E3425A` FOREIGN KEY (`idSelect`) REFERENCES `af_component_select` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `af_component_select_single`
--
ALTER TABLE `af_component_select_single`
  ADD CONSTRAINT `FK_37F25707BF396750` FOREIGN KEY (`id`) REFERENCES `af_component` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_37F257079AFC98DC` FOREIGN KEY (`idDefaultValue`) REFERENCES `af_component_select_option` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `af_component_subaf`
--
ALTER TABLE `af_component_subaf`
  ADD CONSTRAINT `FK_C6EB6B3EBF396750` FOREIGN KEY (`id`) REFERENCES `af_component` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_C6EB6B3E532BBE7A` FOREIGN KEY (`idCalledAF`) REFERENCES `af_af` (`id`);

--
-- Contraintes pour la table `af_component_subaf_notrepeated`
--
ALTER TABLE `af_component_subaf_notrepeated`
  ADD CONSTRAINT `FK_A0DF8CB1BF396750` FOREIGN KEY (`id`) REFERENCES `af_component` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `af_component_subaf_repeated`
--
ALTER TABLE `af_component_subaf_repeated`
  ADD CONSTRAINT `FK_F0CC3AACBF396750` FOREIGN KEY (`id`) REFERENCES `af_component` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `af_condition`
--
ALTER TABLE `af_condition`
  ADD CONSTRAINT `FK_7FDF2DD75E699E88` FOREIGN KEY (`idAF`) REFERENCES `af_af` (`id`);

--
-- Contraintes pour la table `af_condition_elementary`
--
ALTER TABLE `af_condition_elementary`
  ADD CONSTRAINT `FK_F5C5CED9BF396750` FOREIGN KEY (`id`) REFERENCES `af_condition` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_F5C5CED94C310645` FOREIGN KEY (`idField`) REFERENCES `af_component_field` (`id`);

--
-- Contraintes pour la table `af_condition_elementary_checkbox`
--
ALTER TABLE `af_condition_elementary_checkbox`
  ADD CONSTRAINT `FK_C53FB53ABF396750` FOREIGN KEY (`id`) REFERENCES `af_condition` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `af_condition_elementary_numeric`
--
ALTER TABLE `af_condition_elementary_numeric`
  ADD CONSTRAINT `FK_B8D59D61BF396750` FOREIGN KEY (`id`) REFERENCES `af_condition` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `af_condition_elementary_select_multi`
--
ALTER TABLE `af_condition_elementary_select_multi`
  ADD CONSTRAINT `FK_5E559064BF396750` FOREIGN KEY (`id`) REFERENCES `af_condition` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_5E5590643997A82A` FOREIGN KEY (`idOption`) REFERENCES `af_component_select_option` (`id`);

--
-- Contraintes pour la table `af_condition_elementary_select_single`
--
ALTER TABLE `af_condition_elementary_select_single`
  ADD CONSTRAINT `FK_F089B204BF396750` FOREIGN KEY (`id`) REFERENCES `af_condition` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_F089B2043997A82A` FOREIGN KEY (`idOption`) REFERENCES `af_component_select_option` (`id`);

--
-- Contraintes pour la table `af_condition_expression`
--
ALTER TABLE `af_condition_expression`
  ADD CONSTRAINT `FK_D62FDD25BF396750` FOREIGN KEY (`id`) REFERENCES `af_condition` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_D62FDD252C508E17` FOREIGN KEY (`idTECExpression`) REFERENCES `tec_expression` (`id`);

--
-- Contraintes pour la table `af_input`
--
ALTER TABLE `af_input`
  ADD CONSTRAINT `FK_85DD0DB5DDF99681` FOREIGN KEY (`idInputSet`) REFERENCES `af_inputset` (`id`);

--
-- Contraintes pour la table `af_inputset_primary`
--
ALTER TABLE `af_inputset_primary`
  ADD CONSTRAINT `FK_FC79CE8BBF396750` FOREIGN KEY (`id`) REFERENCES `af_inputset` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `af_inputset_sub`
--
ALTER TABLE `af_inputset_sub`
  ADD CONSTRAINT `FK_B26B7850BF396750` FOREIGN KEY (`id`) REFERENCES `af_inputset` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `af_input_checkbox`
--
ALTER TABLE `af_input_checkbox`
  ADD CONSTRAINT `FK_D4525836BF396750` FOREIGN KEY (`id`) REFERENCES `af_input` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `af_input_group`
--
ALTER TABLE `af_input_group`
  ADD CONSTRAINT `FK_A76E7905BF396750` FOREIGN KEY (`id`) REFERENCES `af_input` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `af_input_numeric`
--
ALTER TABLE `af_input_numeric`
  ADD CONSTRAINT `FK_D433CDBABF396750` FOREIGN KEY (`id`) REFERENCES `af_input` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `af_input_select_multi`
--
ALTER TABLE `af_input_select_multi`
  ADD CONSTRAINT `FK_F5A5937BF396750` FOREIGN KEY (`id`) REFERENCES `af_input` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `af_input_select_single`
--
ALTER TABLE `af_input_select_single`
  ADD CONSTRAINT `FK_2BABD83BF396750` FOREIGN KEY (`id`) REFERENCES `af_input` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `af_input_subaf`
--
ALTER TABLE `af_input_subaf`
  ADD CONSTRAINT `FK_D39185C3BF396750` FOREIGN KEY (`id`) REFERENCES `af_input` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `af_input_subaf_notrepeated`
--
ALTER TABLE `af_input_subaf_notrepeated`
  ADD CONSTRAINT `FK_882F985BF396750` FOREIGN KEY (`id`) REFERENCES `af_input` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_882F985794A9F5B` FOREIGN KEY (`idSub`) REFERENCES `af_inputset_sub` (`id`);

--
-- Contraintes pour la table `af_input_subaf_repeated`
--
ALTER TABLE `af_input_subaf_repeated`
  ADD CONSTRAINT `FK_1A2C357FBF396750` FOREIGN KEY (`id`) REFERENCES `af_input` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `af_input_subaf_repeated_value`
--
ALTER TABLE `af_input_subaf_repeated_value`
  ADD CONSTRAINT `FK_3E6E256E794A9F5B` FOREIGN KEY (`idSub`) REFERENCES `af_inputset_sub` (`id`),
  ADD CONSTRAINT `FK_3E6E256E444242C1` FOREIGN KEY (`idInputSubAF`) REFERENCES `af_input_subaf_repeated` (`id`);

--
-- Contraintes pour la table `af_output_element`
--
ALTER TABLE `af_output_element`
  ADD CONSTRAINT `FK_FFD106E7DDF99681` FOREIGN KEY (`idInputSet`) REFERENCES `af_inputset` (`id`),
  ADD CONSTRAINT `FK_FFD106E79AFBF5DA` FOREIGN KEY (`idOutputSet`) REFERENCES `af_output_outputset` (`id`);

--
-- Contraintes pour la table `af_output_element_indexes`
--
ALTER TABLE `af_output_element_indexes`
  ADD CONSTRAINT `FK_18A421F797B7241C` FOREIGN KEY (`idIndex`) REFERENCES `af_output_index` (`id`),
  ADD CONSTRAINT `FK_18A421F769F7E6E1` FOREIGN KEY (`idOutputElement`) REFERENCES `af_output_element` (`id`);

--
-- Contraintes pour la table `af_output_outputset`
--
ALTER TABLE `af_output_outputset`
  ADD CONSTRAINT `FK_1C1A514ADDF99681` FOREIGN KEY (`idInputSet`) REFERENCES `af_inputset_primary` (`id`);

--
-- Contraintes pour la table `af_output_total`
--
ALTER TABLE `af_output_total`
  ADD CONSTRAINT `FK_355225DF9AFBF5DA` FOREIGN KEY (`idOutputSet`) REFERENCES `af_output_outputset` (`id`);

--
-- Contraintes pour la table `algo_algo`
--
ALTER TABLE `algo_algo`
  ADD CONSTRAINT `FK_3080AD55C75C385B` FOREIGN KEY (`idSet`) REFERENCES `algo_set` (`id`);

--
-- Contraintes pour la table `algo_condition`
--
ALTER TABLE `algo_condition`
  ADD CONSTRAINT `FK_97932817BF396750` FOREIGN KEY (`id`) REFERENCES `algo_algo` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `algo_condition_elementary`
--
ALTER TABLE `algo_condition_elementary`
  ADD CONSTRAINT `FK_DAD6E72ABF396750` FOREIGN KEY (`id`) REFERENCES `algo_algo` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `algo_condition_elementary_boolean`
--
ALTER TABLE `algo_condition_elementary_boolean`
  ADD CONSTRAINT `FK_98E32E44BF396750` FOREIGN KEY (`id`) REFERENCES `algo_algo` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `algo_condition_elementary_numeric`
--
ALTER TABLE `algo_condition_elementary_numeric`
  ADD CONSTRAINT `FK_55C73A67BF396750` FOREIGN KEY (`id`) REFERENCES `algo_algo` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `algo_condition_elementary_select`
--
ALTER TABLE `algo_condition_elementary_select`
  ADD CONSTRAINT `FK_9ABD9699BF396750` FOREIGN KEY (`id`) REFERENCES `algo_algo` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `algo_condition_elementary_select_multi`
--
ALTER TABLE `algo_condition_elementary_select_multi`
  ADD CONSTRAINT `FK_F5DBBEC7BF396750` FOREIGN KEY (`id`) REFERENCES `algo_algo` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `algo_condition_elementary_select_single`
--
ALTER TABLE `algo_condition_elementary_select_single`
  ADD CONSTRAINT `FK_BFFDCE78BF396750` FOREIGN KEY (`id`) REFERENCES `algo_algo` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `algo_condition_expression`
--
ALTER TABLE `algo_condition_expression`
  ADD CONSTRAINT `FK_F93CF4D6BF396750` FOREIGN KEY (`id`) REFERENCES `algo_algo` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_F93CF4D65AC448EB` FOREIGN KEY (`idExpression`) REFERENCES `tec_expression` (`id`);

--
-- Contraintes pour la table `algo_index`
--
ALTER TABLE `algo_index`
  ADD CONSTRAINT `FK_F53DE6CC55E2C741` FOREIGN KEY (`idAlgoNumeric`) REFERENCES `algo_numeric` (`id`);

--
-- Contraintes pour la table `algo_index_algo`
--
ALTER TABLE `algo_index_algo`
  ADD CONSTRAINT `FK_733C5BB7BF396750` FOREIGN KEY (`id`) REFERENCES `algo_index` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_733C5BB7675FA209` FOREIGN KEY (`idAlgo`) REFERENCES `algo_selection_textkey` (`id`);

--
-- Contraintes pour la table `algo_index_fixed`
--
ALTER TABLE `algo_index_fixed`
  ADD CONSTRAINT `FK_A5C0705DBF396750` FOREIGN KEY (`id`) REFERENCES `algo_index` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `algo_numeric`
--
ALTER TABLE `algo_numeric`
  ADD CONSTRAINT `FK_A34AF3B0BF396750` FOREIGN KEY (`id`) REFERENCES `algo_algo` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `algo_numeric_constant`
--
ALTER TABLE `algo_numeric_constant`
  ADD CONSTRAINT `FK_5AAF68ADBF396750` FOREIGN KEY (`id`) REFERENCES `algo_algo` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `algo_numeric_expression`
--
ALTER TABLE `algo_numeric_expression`
  ADD CONSTRAINT `FK_3FA88546BF396750` FOREIGN KEY (`id`) REFERENCES `algo_algo` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_3FA885465AC448EB` FOREIGN KEY (`idExpression`) REFERENCES `tec_expression` (`id`);

--
-- Contraintes pour la table `algo_numeric_input`
--
ALTER TABLE `algo_numeric_input`
  ADD CONSTRAINT `FK_A91893D7BF396750` FOREIGN KEY (`id`) REFERENCES `algo_algo` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `algo_numeric_parameter`
--
ALTER TABLE `algo_numeric_parameter`
  ADD CONSTRAINT `FK_A69D11EBF396750` FOREIGN KEY (`id`) REFERENCES `algo_algo` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `algo_parametercoordinate`
--
ALTER TABLE `algo_parametercoordinate`
  ADD CONSTRAINT `FK_6EE2BBED675FA209` FOREIGN KEY (`idAlgo`) REFERENCES `algo_numeric_parameter` (`id`);

--
-- Contraintes pour la table `algo_parametercoordinate_algo`
--
ALTER TABLE `algo_parametercoordinate_algo`
  ADD CONSTRAINT `FK_A29AABA0BF396750` FOREIGN KEY (`id`) REFERENCES `algo_parametercoordinate` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_A29AABA048D02A31` FOREIGN KEY (`idAlgoKeyword`) REFERENCES `algo_selection_textkey` (`id`);

--
-- Contraintes pour la table `algo_parametercoordinate_fixed`
--
ALTER TABLE `algo_parametercoordinate_fixed`
  ADD CONSTRAINT `FK_26C2536ABF396750` FOREIGN KEY (`id`) REFERENCES `algo_parametercoordinate` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `algo_selection`
--
ALTER TABLE `algo_selection`
  ADD CONSTRAINT `FK_BCE0AC83BF396750` FOREIGN KEY (`id`) REFERENCES `algo_algo` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `algo_selection_main`
--
ALTER TABLE `algo_selection_main`
  ADD CONSTRAINT `FK_52564FAABF396750` FOREIGN KEY (`id`) REFERENCES `algo_algo` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_52564FAA5AC448EB` FOREIGN KEY (`idExpression`) REFERENCES `tec_expression` (`id`);

--
-- Contraintes pour la table `algo_selection_textkey`
--
ALTER TABLE `algo_selection_textkey`
  ADD CONSTRAINT `FK_98280064BF396750` FOREIGN KEY (`id`) REFERENCES `algo_algo` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `algo_selection_textkey_expression`
--
ALTER TABLE `algo_selection_textkey_expression`
  ADD CONSTRAINT `FK_D167CFECBF396750` FOREIGN KEY (`id`) REFERENCES `algo_algo` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_D167CFEC5AC448EB` FOREIGN KEY (`idExpression`) REFERENCES `tec_expression` (`id`);

--
-- Contraintes pour la table `algo_selection_textkey_input`
--
ALTER TABLE `algo_selection_textkey_input`
  ADD CONSTRAINT `FK_9632D29ABF396750` FOREIGN KEY (`id`) REFERENCES `algo_algo` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `classif_axis`
--
ALTER TABLE `classif_axis`
  ADD CONSTRAINT `FK_CD8BEA0E2F89D6F2` FOREIGN KEY (`idDirectNarrower`) REFERENCES `classif_axis` (`id`);

--
-- Contraintes pour la table `classif_contextindicator`
--
ALTER TABLE `classif_contextindicator`
  ADD CONSTRAINT `FK_FE1067E72DDEB6E5` FOREIGN KEY (`idIndicator`) REFERENCES `classif_indicator` (`id`),
  ADD CONSTRAINT `FK_FE1067E7F2E4EE8C` FOREIGN KEY (`idContext`) REFERENCES `classif_context` (`id`);

--
-- Contraintes pour la table `classif_contextindicator_axes`
--
ALTER TABLE `classif_contextindicator_axes`
  ADD CONSTRAINT `FK_7EB1ADE5F6F2D864` FOREIGN KEY (`idAxis`) REFERENCES `classif_axis` (`id`),
  ADD CONSTRAINT `FK_7EB1ADE5F2E4EE8C2DDEB6E5` FOREIGN KEY (`idContext`, `idIndicator`) REFERENCES `classif_contextindicator` (`idContext`, `idIndicator`);

--
-- Contraintes pour la table `classif_member`
--
ALTER TABLE `classif_member`
  ADD CONSTRAINT `FK_4430BB36F6F2D864` FOREIGN KEY (`idAxis`) REFERENCES `classif_axis` (`id`);

--
-- Contraintes pour la table `classif_member_association`
--
ALTER TABLE `classif_member_association`
  ADD CONSTRAINT `FK_4997A6F535771734` FOREIGN KEY (`idChild`) REFERENCES `classif_member` (`id`),
  ADD CONSTRAINT `FK_4997A6F55E9FC8D5` FOREIGN KEY (`idParent`) REFERENCES `classif_member` (`id`);

--
-- Contraintes pour la table `doc_bibliography_referenceddocuments`
--
ALTER TABLE `doc_bibliography_referenceddocuments`
  ADD CONSTRAINT `FK_79D8433E8BCAA02D` FOREIGN KEY (`idDocument`) REFERENCES `doc_document` (`id`),
  ADD CONSTRAINT `FK_79D8433ED2D14599` FOREIGN KEY (`idBibliography`) REFERENCES `doc_bibliography` (`id`);

--
-- Contraintes pour la table `doc_document`
--
ALTER TABLE `doc_document`
  ADD CONSTRAINT `FK_589A2E83B139F34E` FOREIGN KEY (`idLibrary`) REFERENCES `doc_library` (`id`);

--
-- Contraintes pour la table `dw_axis`
--
ALTER TABLE `dw_axis`
  ADD CONSTRAINT `FK_829918992F89D6F2` FOREIGN KEY (`idDirectNarrower`) REFERENCES `dw_axis` (`id`),
  ADD CONSTRAINT `FK_829918994303EF26` FOREIGN KEY (`idCube`) REFERENCES `dw_cube` (`id`);

--
-- Contraintes pour la table `dw_filter`
--
ALTER TABLE `dw_filter`
  ADD CONSTRAINT `FK_26A3DEACF6F2D864` FOREIGN KEY (`idAxis`) REFERENCES `dw_axis` (`id`),
  ADD CONSTRAINT `FK_26A3DEACA73EDF1E` FOREIGN KEY (`idReport`) REFERENCES `dw_report` (`id`);

--
-- Contraintes pour la table `dw_filter_member`
--
ALTER TABLE `dw_filter_member`
  ADD CONSTRAINT `FK_949AC92C13F552E2` FOREIGN KEY (`idMember`) REFERENCES `dw_member` (`id`),
  ADD CONSTRAINT `FK_949AC92C1CD5F787` FOREIGN KEY (`idFilter`) REFERENCES `dw_filter` (`id`);

--
-- Contraintes pour la table `dw_indicator`
--
ALTER TABLE `dw_indicator`
  ADD CONSTRAINT `FK_E4E6543C4303EF26` FOREIGN KEY (`idCube`) REFERENCES `dw_cube` (`id`);

--
-- Contraintes pour la table `dw_member`
--
ALTER TABLE `dw_member`
  ADD CONSTRAINT `FK_29837BC9F6F2D864` FOREIGN KEY (`idAxis`) REFERENCES `dw_axis` (`id`);

--
-- Contraintes pour la table `dw_member_association`
--
ALTER TABLE `dw_member_association`
  ADD CONSTRAINT `FK_16CAAD2935771734` FOREIGN KEY (`idChild`) REFERENCES `dw_member` (`id`),
  ADD CONSTRAINT `FK_16CAAD295E9FC8D5` FOREIGN KEY (`idParent`) REFERENCES `dw_member` (`id`);

--
-- Contraintes pour la table `dw_report`
--
ALTER TABLE `dw_report`
  ADD CONSTRAINT `FK_9D48F635D78AE2A5` FOREIGN KEY (`idDenominatorAxis2`) REFERENCES `dw_axis` (`id`),
  ADD CONSTRAINT `FK_9D48F6354303EF26` FOREIGN KEY (`idCube`) REFERENCES `dw_cube` (`id`),
  ADD CONSTRAINT `FK_9D48F6354E83B31F` FOREIGN KEY (`idDenominatorAxis1`) REFERENCES `dw_axis` (`id`),
  ADD CONSTRAINT `FK_9D48F63590356F88` FOREIGN KEY (`idNumeratorAxis2`) REFERENCES `dw_axis` (`id`),
  ADD CONSTRAINT `FK_9D48F63593C3E32` FOREIGN KEY (`idNumeratorAxis1`) REFERENCES `dw_axis` (`id`),
  ADD CONSTRAINT `FK_9D48F635D7B4CDCB` FOREIGN KEY (`idNumerator`) REFERENCES `dw_indicator` (`id`),
  ADD CONSTRAINT `FK_9D48F635EE70132` FOREIGN KEY (`idDenominator`) REFERENCES `dw_indicator` (`id`);

--
-- Contraintes pour la table `dw_result`
--
ALTER TABLE `dw_result`
  ADD CONSTRAINT `FK_4A0D40A22DDEB6E5` FOREIGN KEY (`idIndicator`) REFERENCES `dw_indicator` (`id`),
  ADD CONSTRAINT `FK_4A0D40A24303EF26` FOREIGN KEY (`idCube`) REFERENCES `dw_cube` (`id`);

--
-- Contraintes pour la table `dw_result_member`
--
ALTER TABLE `dw_result_member`
  ADD CONSTRAINT `FK_5D2EAC0E13F552E2` FOREIGN KEY (`idMember`) REFERENCES `dw_member` (`id`),
  ADD CONSTRAINT `FK_5D2EAC0E707B6989` FOREIGN KEY (`idResult`) REFERENCES `dw_result` (`id`);

--
-- Contraintes pour la table `inventory_simpleassociation`
--
ALTER TABLE `inventory_simpleassociation`
  ADD CONSTRAINT `FK_133087E5D56788B8` FOREIGN KEY (`simpleid`) REFERENCES `inventory_simple` (`id`),
  ADD CONSTRAINT `FK_133087E5941F12C9` FOREIGN KEY (`associationid`) REFERENCES `inventory_association` (`id`);

--
-- Contraintes pour la table `keyword_association`
--
ALTER TABLE `keyword_association`
  ADD CONSTRAINT `FK_94D78DCACCF1812D` FOREIGN KEY (`idPredicate`) REFERENCES `keyword_predicate` (`id`),
  ADD CONSTRAINT `FK_94D78DCA38188F90` FOREIGN KEY (`idSubjectKeyword`) REFERENCES `keyword_keyword` (`id`),
  ADD CONSTRAINT `FK_94D78DCAE879530A` FOREIGN KEY (`idObjectKeyword`) REFERENCES `keyword_keyword` (`id`);

--
-- Contraintes pour la table `orga_axis`
--
ALTER TABLE `orga_axis`
  ADD CONSTRAINT `FK_3AD44EC32F89D6F2` FOREIGN KEY (`idDirectNarrower`) REFERENCES `orga_axis` (`id`),
  ADD CONSTRAINT `FK_3AD44EC39F70C78D` FOREIGN KEY (`idOrganization`) REFERENCES `orga_organization` (`id`);

--
-- Contraintes pour la table `orga_cell`
--
ALTER TABLE `orga_cell`
  ADD CONSTRAINT `FK_745C4FDBAF7E3C66` FOREIGN KEY (`idGranularity`) REFERENCES `orga_granularity` (`id`),
  ADD CONSTRAINT `FK_745C4FDB1DFF84D5` FOREIGN KEY (`idDocBibliographyForAFInputSetPrimary`) REFERENCES `doc_bibliography` (`id`),
  ADD CONSTRAINT `FK_745C4FDB20C970BF` FOREIGN KEY (`idDocLibraryForSocialContextActions`) REFERENCES `doc_library` (`id`),
  ADD CONSTRAINT `FK_745C4FDB64F57618` FOREIGN KEY (`idDocLibraryForSocialGenericActions`) REFERENCES `doc_library` (`id`),
  ADD CONSTRAINT `FK_745C4FDB7A0D9634` FOREIGN KEY (`idDWCube`) REFERENCES `dw_cube` (`id`),
  ADD CONSTRAINT `FK_745C4FDB84E76030` FOREIGN KEY (`idDocLibraryForAFInputSetsPrimary`) REFERENCES `doc_library` (`id`),
  ADD CONSTRAINT `FK_745C4FDBBEDB9775` FOREIGN KEY (`idAFInputSetPrimary`) REFERENCES `af_inputset_primary` (`id`);

--
-- Contraintes pour la table `orga_cellsgroup`
--
ALTER TABLE `orga_cellsgroup`
  ADD CONSTRAINT `FK_262E11F25E699E88` FOREIGN KEY (`idAF`) REFERENCES `af_af` (`id`),
  ADD CONSTRAINT `FK_262E11F21F8004D0` FOREIGN KEY (`idContainerCell`) REFERENCES `orga_cell` (`id`),
  ADD CONSTRAINT `FK_262E11F24CB6F1FF` FOREIGN KEY (`idInputGranularity`) REFERENCES `orga_granularity` (`id`);

--
-- Contraintes pour la table `orga_cell_afinputsetprimarycomments`
--
ALTER TABLE `orga_cell_afinputsetprimarycomments`
  ADD CONSTRAINT `FK_1CEA5B3B7655B281` FOREIGN KEY (`idSocialComment`) REFERENCES `social_comment` (`id`),
  ADD CONSTRAINT `FK_1CEA5B3BB87AD97C` FOREIGN KEY (`idCell`) REFERENCES `orga_cell` (`id`);

--
-- Contraintes pour la table `orga_cell_contextactions`
--
ALTER TABLE `orga_cell_contextactions`
  ADD CONSTRAINT `FK_288C8481ABA90A50` FOREIGN KEY (`idSocialContextAction`) REFERENCES `social_contextaction` (`id`),
  ADD CONSTRAINT `FK_288C8481B87AD97C` FOREIGN KEY (`idCell`) REFERENCES `orga_cell` (`id`);

--
-- Contraintes pour la table `orga_cell_dwresults`
--
ALTER TABLE `orga_cell_dwresults`
  ADD CONSTRAINT `FK_60EB5482156E9CC` FOREIGN KEY (`idDWResult`) REFERENCES `dw_result` (`id`),
  ADD CONSTRAINT `FK_60EB548B87AD97C` FOREIGN KEY (`idCell`) REFERENCES `orga_cell` (`id`);

--
-- Contraintes pour la table `orga_cell_genericactions`
--
ALTER TABLE `orga_cell_genericactions`
  ADD CONSTRAINT `FK_6CB082269382DE3C` FOREIGN KEY (`idSocialGenericAction`) REFERENCES `social_genericaction` (`id`),
  ADD CONSTRAINT `FK_6CB08226B87AD97C` FOREIGN KEY (`idCell`) REFERENCES `orga_cell` (`id`);

--
-- Contraintes pour la table `orga_cell_member`
--
ALTER TABLE `orga_cell_member`
  ADD CONSTRAINT `FK_C00FE2CA13F552E2` FOREIGN KEY (`idMember`) REFERENCES `orga_member` (`id`),
  ADD CONSTRAINT `FK_C00FE2CAB87AD97C` FOREIGN KEY (`idCell`) REFERENCES `orga_cell` (`id`);

--
-- Contraintes pour la table `orga_granularity`
--
ALTER TABLE `orga_granularity`
  ADD CONSTRAINT `FK_9457AFC25A4F7F9` FOREIGN KEY (`idInputConfigGranularity`) REFERENCES `orga_granularity` (`id`),
  ADD CONSTRAINT `FK_9457AFC27A0D9634` FOREIGN KEY (`idDWCube`) REFERENCES `dw_cube` (`id`),
  ADD CONSTRAINT `FK_9457AFC29F70C78D` FOREIGN KEY (`idOrganization`) REFERENCES `orga_organization` (`id`);

--
-- Contraintes pour la table `orga_granularity_axis`
--
ALTER TABLE `orga_granularity_axis`
  ADD CONSTRAINT `FK_821B9C1CF6F2D864` FOREIGN KEY (`idAxis`) REFERENCES `orga_axis` (`id`),
  ADD CONSTRAINT `FK_821B9C1CAF7E3C66` FOREIGN KEY (`idGranularity`) REFERENCES `orga_granularity` (`id`);

--
-- Contraintes pour la table `orga_granularity_dwreport`
--
ALTER TABLE `orga_granularity_dwreport`
  ADD CONSTRAINT `FK_F7DF0FB4E6DE15BA` FOREIGN KEY (`idGranularityDWReport`) REFERENCES `dw_report` (`id`);

--
-- Contraintes pour la table `orga_granularity_dwreport_cell_dwreport`
--
ALTER TABLE `orga_granularity_dwreport_cell_dwreport`
  ADD CONSTRAINT `FK_8D216F42136CB86D` FOREIGN KEY (`idCellDWReport`) REFERENCES `dw_report` (`id`),
  ADD CONSTRAINT `FK_8D216F42E6DE15BA` FOREIGN KEY (`idGranularityDWReport`) REFERENCES `orga_granularity_dwreport` (`id`);

--
-- Contraintes pour la table `orga_member`
--
ALTER TABLE `orga_member`
  ADD CONSTRAINT `FK_EBDF829BF6F2D864` FOREIGN KEY (`idAxis`) REFERENCES `orga_axis` (`id`);

--
-- Contraintes pour la table `orga_member_association`
--
ALTER TABLE `orga_member_association`
  ADD CONSTRAINT `FK_11D7D5B35771734` FOREIGN KEY (`idChild`) REFERENCES `orga_member` (`id`),
  ADD CONSTRAINT `FK_11D7D5B5E9FC8D5` FOREIGN KEY (`idParent`) REFERENCES `orga_member` (`id`);

--
-- Contraintes pour la table `orga_organization`
--
ALTER TABLE `orga_organization`
  ADD CONSTRAINT `FK_4EF089EF632614CF` FOREIGN KEY (`idGranularityForInventoryStatus`) REFERENCES `orga_granularity` (`id`);

--
-- Contraintes pour la table `simulation_scenario`
--
ALTER TABLE `simulation_scenario`
  ADD CONSTRAINT `FK_26EFBDE4C75C385B` FOREIGN KEY (`idSet`) REFERENCES `simulation_set` (`id`),
  ADD CONSTRAINT `FK_26EFBDE442D8D2A7` FOREIGN KEY (`idDWMember`) REFERENCES `dw_member` (`id`),
  ADD CONSTRAINT `FK_26EFBDE4BEDB9775` FOREIGN KEY (`idAFInputSetPrimary`) REFERENCES `af_inputset_primary` (`id`);

--
-- Contraintes pour la table `simulation_scenario_dwresults`
--
ALTER TABLE `simulation_scenario_dwresults`
  ADD CONSTRAINT `FK_CBC5AE3B2156E9CC` FOREIGN KEY (`idDWResult`) REFERENCES `dw_result` (`id`),
  ADD CONSTRAINT `FK_CBC5AE3B6DE6E283` FOREIGN KEY (`idScenario`) REFERENCES `simulation_scenario` (`id`);

--
-- Contraintes pour la table `simulation_set`
--
ALTER TABLE `simulation_set`
  ADD CONSTRAINT `FK_E11EE25C5E699E88` FOREIGN KEY (`idAF`) REFERENCES `af_af` (`id`),
  ADD CONSTRAINT `FK_E11EE25C7A0D9634` FOREIGN KEY (`idDWCube`) REFERENCES `dw_cube` (`id`),
  ADD CONSTRAINT `FK_E11EE25CCFFCA176` FOREIGN KEY (`idDWAxis`) REFERENCES `dw_axis` (`id`),
  ADD CONSTRAINT `FK_E11EE25CFE6E88D7` FOREIGN KEY (`idUser`) REFERENCES `user_user` (`id`);

--
-- Contraintes pour la table `social_action`
--
ALTER TABLE `social_action`
  ADD CONSTRAINT `FK_ED55A08DD2D14599` FOREIGN KEY (`idBibliography`) REFERENCES `doc_bibliography` (`id`);

--
-- Contraintes pour la table `social_action_comments`
--
ALTER TABLE `social_action_comments`
  ADD CONSTRAINT `FK_6E487FC484CD399E` FOREIGN KEY (`idComment`) REFERENCES `social_comment` (`id`),
  ADD CONSTRAINT `FK_6E487FC424DD2408` FOREIGN KEY (`idAction`) REFERENCES `social_action` (`id`);

--
-- Contraintes pour la table `social_comment`
--
ALTER TABLE `social_comment`
  ADD CONSTRAINT `FK_19D6C6B5DEBE7052` FOREIGN KEY (`idAuthor`) REFERENCES `user_user` (`id`);

--
-- Contraintes pour la table `social_contextaction`
--
ALTER TABLE `social_contextaction`
  ADD CONSTRAINT `FK_84779C2CBF396750` FOREIGN KEY (`id`) REFERENCES `social_action` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_84779C2C69808104` FOREIGN KEY (`idGenericAction`) REFERENCES `social_genericaction` (`id`);

--
-- Contraintes pour la table `social_contextactionkeyfigure`
--
ALTER TABLE `social_contextactionkeyfigure`
  ADD CONSTRAINT `FK_7208CDDE51AB5568` FOREIGN KEY (`idContextAction`) REFERENCES `social_contextaction` (`id`),
  ADD CONSTRAINT `FK_7208CDDE52C14EAD` FOREIGN KEY (`idActionKeyFigure`) REFERENCES `social_actionkeyfigure` (`id`);

--
-- Contraintes pour la table `social_genericaction`
--
ALTER TABLE `social_genericaction`
  ADD CONSTRAINT `FK_BC5C4840BF396750` FOREIGN KEY (`id`) REFERENCES `social_action` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_BC5C484080B1A415` FOREIGN KEY (`idTheme`) REFERENCES `social_theme` (`id`);

--
-- Contraintes pour la table `social_message`
--
ALTER TABLE `social_message`
  ADD CONSTRAINT `FK_3B1FA4A6DEBE7052` FOREIGN KEY (`idAuthor`) REFERENCES `user_user` (`id`);

--
-- Contraintes pour la table `social_message_group_recipients`
--
ALTER TABLE `social_message_group_recipients`
  ADD CONSTRAINT `FK_6AE4E9F07A0407D8` FOREIGN KEY (`idGroup`) REFERENCES `social_usergroup` (`id`),
  ADD CONSTRAINT `FK_6AE4E9F0A6045B8D` FOREIGN KEY (`idMessage`) REFERENCES `social_message` (`id`);

--
-- Contraintes pour la table `social_message_user_recipients`
--
ALTER TABLE `social_message_user_recipients`
  ADD CONSTRAINT `FK_8DB9A0B7FE6E88D7` FOREIGN KEY (`idUser`) REFERENCES `user_user` (`id`),
  ADD CONSTRAINT `FK_8DB9A0B7A6045B8D` FOREIGN KEY (`idMessage`) REFERENCES `social_message` (`id`);

--
-- Contraintes pour la table `social_news`
--
ALTER TABLE `social_news`
  ADD CONSTRAINT `FK_746C6ADDDEBE7052` FOREIGN KEY (`idAuthor`) REFERENCES `user_user` (`id`);

--
-- Contraintes pour la table `social_news_comments`
--
ALTER TABLE `social_news_comments`
  ADD CONSTRAINT `FK_F0EEC24784CD399E` FOREIGN KEY (`idComment`) REFERENCES `social_comment` (`id`),
  ADD CONSTRAINT `FK_F0EEC2476E2EC7CE` FOREIGN KEY (`idNews`) REFERENCES `social_news` (`id`);

--
-- Contraintes pour la table `social_usergroup_users`
--
ALTER TABLE `social_usergroup_users`
  ADD CONSTRAINT `FK_D8A9E317FE6E88D7` FOREIGN KEY (`idUser`) REFERENCES `user_user` (`id`),
  ADD CONSTRAINT `FK_D8A9E317774F7C45` FOREIGN KEY (`idUserGroup`) REFERENCES `social_usergroup` (`id`);

--
-- Contraintes pour la table `techno_category`
--
ALTER TABLE `techno_category`
  ADD CONSTRAINT `FK_5392070E2526073F` FOREIGN KEY (`idParentCategory`) REFERENCES `techno_category` (`id`);

--
-- Contraintes pour la table `techno_component_tags`
--
ALTER TABLE `techno_component_tags`
  ADD CONSTRAINT `FK_E03C2B5D22C1AA04` FOREIGN KEY (`idTag`) REFERENCES `techno_tag` (`id`),
  ADD CONSTRAINT `FK_E03C2B5DB5148A01` FOREIGN KEY (`idComponent`) REFERENCES `techno_component` (`id`);

--
-- Contraintes pour la table `techno_element`
--
ALTER TABLE `techno_element`
  ADD CONSTRAINT `FK_9A78BE8EBF396750` FOREIGN KEY (`id`) REFERENCES `techno_component` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `techno_element_coeff`
--
ALTER TABLE `techno_element_coeff`
  ADD CONSTRAINT `FK_3C24A942BF396750` FOREIGN KEY (`id`) REFERENCES `techno_component` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `techno_element_process`
--
ALTER TABLE `techno_element_process`
  ADD CONSTRAINT `FK_77D4D37FBF396750` FOREIGN KEY (`id`) REFERENCES `techno_component` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `techno_family`
--
ALTER TABLE `techno_family`
  ADD CONSTRAINT `FK_26CF4071BF396750` FOREIGN KEY (`id`) REFERENCES `techno_component` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_26CF407155EF339A` FOREIGN KEY (`idCategory`) REFERENCES `techno_category` (`id`);

--
-- Contraintes pour la table `techno_family_cell`
--
ALTER TABLE `techno_family_cell`
  ADD CONSTRAINT `FK_301B9BDC46A12416` FOREIGN KEY (`idChosenElement`) REFERENCES `techno_element` (`id`),
  ADD CONSTRAINT `FK_301B9BDCC6F789C1` FOREIGN KEY (`idFamily`) REFERENCES `techno_family` (`id`);

--
-- Contraintes pour la table `techno_family_cells_common_tags`
--
ALTER TABLE `techno_family_cells_common_tags`
  ADD CONSTRAINT `FK_1F9BC23F22C1AA04` FOREIGN KEY (`idTag`) REFERENCES `techno_tag` (`id`),
  ADD CONSTRAINT `FK_1F9BC23FC6F789C1` FOREIGN KEY (`idFamily`) REFERENCES `techno_family` (`id`);

--
-- Contraintes pour la table `techno_family_cell_members`
--
ALTER TABLE `techno_family_cell_members`
  ADD CONSTRAINT `FK_8A18015513F552E2` FOREIGN KEY (`idMember`) REFERENCES `techno_family_member` (`id`),
  ADD CONSTRAINT `FK_8A180155B87AD97C` FOREIGN KEY (`idCell`) REFERENCES `techno_family_cell` (`id`);

--
-- Contraintes pour la table `techno_family_coeff`
--
ALTER TABLE `techno_family_coeff`
  ADD CONSTRAINT `FK_63059DE5BF396750` FOREIGN KEY (`id`) REFERENCES `techno_component` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `techno_family_dimension`
--
ALTER TABLE `techno_family_dimension`
  ADD CONSTRAINT `FK_CD0456BE2F889BF0` FOREIGN KEY (`idMeaning`) REFERENCES `techno_meaning` (`id`),
  ADD CONSTRAINT `FK_CD0456BEC6F789C1` FOREIGN KEY (`idFamily`) REFERENCES `techno_family` (`id`);

--
-- Contraintes pour la table `techno_family_member`
--
ALTER TABLE `techno_family_member`
  ADD CONSTRAINT `FK_252046D63671EACA` FOREIGN KEY (`idDimension`) REFERENCES `techno_family_dimension` (`id`);

--
-- Contraintes pour la table `techno_family_process`
--
ALTER TABLE `techno_family_process`
  ADD CONSTRAINT `FK_B72652C5BF396750` FOREIGN KEY (`id`) REFERENCES `techno_component` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `techno_tag`
--
ALTER TABLE `techno_tag`
  ADD CONSTRAINT `FK_DDCB964620A7F0E6` FOREIGN KEY (`meaning_id`) REFERENCES `techno_meaning` (`id`);

--
-- Contraintes pour la table `tec_component`
--
ALTER TABLE `tec_component`
  ADD CONSTRAINT `FK_44344EF45E9FC8D5` FOREIGN KEY (`idParent`) REFERENCES `tec_composite` (`id`);

--
-- Contraintes pour la table `tec_composite`
--
ALTER TABLE `tec_composite`
  ADD CONSTRAINT `FK_35C0E2FBBF396750` FOREIGN KEY (`id`) REFERENCES `tec_component` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `tec_expression`
--
ALTER TABLE `tec_expression`
  ADD CONSTRAINT `FK_97E26EBC750166F` FOREIGN KEY (`rootNode`) REFERENCES `tec_composite` (`id`);

--
-- Contraintes pour la table `tec_leaf`
--
ALTER TABLE `tec_leaf`
  ADD CONSTRAINT `FK_D61B2896BF396750` FOREIGN KEY (`id`) REFERENCES `tec_component` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `unit_discreteunit`
--
ALTER TABLE `unit_discreteunit`
  ADD CONSTRAINT `FK_4CDB8FFEBF396750` FOREIGN KEY (`id`) REFERENCES `unit_unit` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `unit_extendedunit`
--
ALTER TABLE `unit_extendedunit`
  ADD CONSTRAINT `FK_51496D37BF396750` FOREIGN KEY (`id`) REFERENCES `unit_unit` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_51496D378C0D5E9D` FOREIGN KEY (`idExtentedUnit`) REFERENCES `unit_unitextension` (`id`),
  ADD CONSTRAINT `FK_51496D379C15CDB5` FOREIGN KEY (`idStandardUnit`) REFERENCES `unit_standardunit` (`id`);

--
-- Contraintes pour la table `unit_physicalquantity`
--
ALTER TABLE `unit_physicalquantity`
  ADD CONSTRAINT `FK_43B520223FCED0B8` FOREIGN KEY (`idReferenceUnit`) REFERENCES `unit_standardunit` (`id`);

--
-- Contraintes pour la table `unit_physicalquantitycomponent`
--
ALTER TABLE `unit_physicalquantitycomponent`
  ADD CONSTRAINT `FK_65FFF3422F4F1DF8` FOREIGN KEY (`idBasePhysicalQuantity`) REFERENCES `unit_physicalquantity` (`id`),
  ADD CONSTRAINT `FK_65FFF342D600A4FE` FOREIGN KEY (`idDerivedPhysicalQuantity`) REFERENCES `unit_physicalquantity` (`id`);

--
-- Contraintes pour la table `unit_standardunit`
--
ALTER TABLE `unit_standardunit`
  ADD CONSTRAINT `FK_268F62D1BF396750` FOREIGN KEY (`id`) REFERENCES `unit_unit` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_268F62D14D1F98C0` FOREIGN KEY (`idUnitSystem`) REFERENCES `unit_unitsystem` (`id`),
  ADD CONSTRAINT `FK_268F62D185CF6229` FOREIGN KEY (`idPhysicalQuantity`) REFERENCES `unit_physicalquantity` (`id`);

--
-- Contraintes pour la table `user_authorization`
--
ALTER TABLE `user_authorization`
  ADD CONSTRAINT `FK_7ADBD57AEF32DE4D` FOREIGN KEY (`idResource`) REFERENCES `user_resource` (`id`),
  ADD CONSTRAINT `FK_7ADBD57A3936C39F` FOREIGN KEY (`idIdentity`) REFERENCES `user_securityidentity` (`id`);

--
-- Contraintes pour la table `user_role`
--
ALTER TABLE `user_role`
  ADD CONSTRAINT `FK_F2BEB3EBF396750` FOREIGN KEY (`id`) REFERENCES `user_securityidentity` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_user`
--
ALTER TABLE `user_user`
  ADD CONSTRAINT `FK_D5D1B71DBF396750` FOREIGN KEY (`id`) REFERENCES `user_securityidentity` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_userroles`
--
ALTER TABLE `user_userroles`
  ADD CONSTRAINT `FK_1F2E4A8E2494D4F4` FOREIGN KEY (`idRole`) REFERENCES `user_role` (`id`),
  ADD CONSTRAINT `FK_1F2E4A8EFE6E88D7` FOREIGN KEY (`idUser`) REFERENCES `user_user` (`id`);
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
