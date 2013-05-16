<?php
/**
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Singleton
 */

/**
 * Classe Singleton abstraite
 *
 * @package    Core
 * @subpackage Singleton
 */
abstract class Core_Singleton implements Core_Singleton_Abstract
{

    /**
     * Renvoie l'instance Singleton de la classe.
     *
     * @return static
     */
    public static function getInstance()
    {
        // Un tableau statique contenant les instances de
        // toutes les classes filles
        static $_instances = array();
        // Récupère le nom de la classe appelée (PHP 5.3, Late Static Binding)
        $classname = get_called_class();
        // Vérifie si l'instance a déjà été chargée
        if (! isset($_instances[$classname])) {
            // Si l'instance n'existe pas on la charge
            $_instances[$classname] = new $classname();
        }
        return $_instances[$classname];
    }

    /**
     * Le constructeur peut être redéclaré dans les classes filles
     * mais sera en protected pour éviter qu'il soit possible de faire
     * $o = new ClasseFille()   (on sera obligé d'utiliser getInstance())
     */
    protected function __construct()
    {
    }

    //@codeCoverageIgnoreStart
    /**
     * On déclare cette méchode en final private pour interdire son
     * utilisation par des classes filles
     */
    final private function __clone()
    {
    }
    //@codeCoverageIgnoreEnd

}
