<?php
/**
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Model
 */

/**
 * Filtre par les ACL dans une requête.
 *
 * @package    Core
 * @subpackage Model
 */
class Core_Model_AclFilter
{

    /**
     * Activation du filtre
     *
     * @var bool
     */
    public $enabled = false;

    /**
     * Utilisateur à utiliser pour filtrer les ACL
     *
     * Optionnel : par défaut, l'utilisateur connecté est utilisé.
     *
     * @var User_Model_User|null
     */
    public $user = null;

    /**
     * Action demandée sur la ressource
     *
     * @var User_Model_Action|null
     */
    public $action = null;

    /**
     * Valide les attributs de la classe.
     *
     * @throws Core_Exception_InvalidArgument
     * @return void
     */
    public function validate()
    {
        if ($this->enabled && ($this->action == null)) {
            throw new Core_Exception_InvalidArgument("ACL Filter enabled without specifying its privilege");
        }
    }

}
