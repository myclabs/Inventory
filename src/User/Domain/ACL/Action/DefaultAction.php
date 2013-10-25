<?php

namespace User\Domain\ACL\Action;

/**
 * Actions standard pouvant être réalisées sur les ressources
 *
 * @author matthieu.napoli
 */
class DefaultAction extends Action
{
    /**
     * Consultation d'une ressource
     */
    const VIEW = 1;

    /**
     * Création d'une ressource
     */
    const CREATE = 2;

    /**
     * Modification d'une ressource
     */
    const EDIT = 4;

    /**
     * Suppression d'une ressource
     */
    const DELETE = 8;

    /**
     * Annulation de la suppression d'une ressource
     */
    const UNDELETE = 16;

    /**
     * Donner les droits sur cette ressource
     */
    const ALLOW = 128;


    /**
     * @return DefaultAction
     */
    public static function VIEW()
    {
        return new static(self::VIEW);
    }

    /**
     * @return DefaultAction
     */
    public static function CREATE()
    {
        return new static(self::CREATE);
    }

    /**
     * @return DefaultAction
     */
    public static function EDIT()
    {
        return new static(self::EDIT);
    }

    /**
     * @return DefaultAction
     */
    public static function DELETE()
    {
        return new static(self::DELETE);
    }

    /**
     * @return DefaultAction
     */
    public static function UNDELETE()
    {
        return new static(self::UNDELETE);
    }

    /**
     * @return DefaultAction
     */
    public static function ALLOW()
    {
        return new static(self::ALLOW);
    }

    /**
     * @return string Libellé de l'action
     */
    public function getLabel()
    {
        switch ($this->value) {
            case self::ALLOW:
                return __('UI', 'verb', 'authorize');
            case self::CREATE:
                return __('UI', 'verb', 'add');
            case self::DELETE:
                return __('UI', 'verb', 'delete');
            case self::EDIT:
                return __('UI', 'verb', 'edit');
            case self::UNDELETE:
                return __('UI', 'verb', 'undelete');
            case self::VIEW:
                return __('UI', 'verb', 'consult');
        }
        return '';
    }
}
