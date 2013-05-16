<?php
/**
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Singleton
 */

/**
 * Interface Singleton
 *
 * @package    Core
 * @subpackage Singleton
 */
interface Core_Singleton_Abstract
{

    /**
     * Renvoie l'instance Singleton de la classe.
     *
     * @return Core_Model_Mapper_Exemple
     */
    public static function getInstance();

}
