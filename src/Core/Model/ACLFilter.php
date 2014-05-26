<?php

use User\Domain\User;

/**
 * Filtre par les ACL dans une requête.
 */
class Core_Model_ACLFilter
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
     * @var User|null
     */
    public $user;

    /**
     * Action demandée sur la ressource
     *
     * @var string|null
     */
    public $action;

    public function enable(User $user, $action)
    {
        $this->enabled = true;
        $this->user = $user;
        $this->action = $action;
    }

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
