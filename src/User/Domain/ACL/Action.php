<?php

namespace User\Domain\ACL;

use Core_Exception_InvalidArgument;
use MyCLabs\Enum\Enum;

/**
 * Actions pouvant être réalisées sur les ressources
 *
 * @author matthieu.napoli
 */
class Action extends Enum
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
     * @return Action
     */
    public static function VIEW()
    {
        return new static(self::VIEW);
    }

    /**
     * @return Action
     */
    public static function CREATE()
    {
        return new static(self::CREATE);
    }

    /**
     * @return Action
     */
    public static function EDIT()
    {
        return new static(self::EDIT);
    }

    /**
     * @return Action
     */
    public static function DELETE()
    {
        return new static(self::DELETE);
    }

    /**
     * @return Action
     */
    public static function UNDELETE()
    {
        return new static(self::UNDELETE);
    }

    /**
     * @return Action
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

    /**
     * @return string
     */
    public function exportToString()
    {
        // Représentation du genre Action::READ
        return get_class($this) . '::' . $this->getValue();
    }

    /**
     * @param string $str
     * @throws Core_Exception_InvalidArgument
     * @return Action
     */
    public static function importFromString($str)
    {
        if ($str === null) {
            throw new Core_Exception_InvalidArgument("Unable to resolve ACL Action from null string");
        }
        $array = explode('::', $str, 2);
        if (count($array) != 2) {
            throw new Core_Exception_InvalidArgument("Unable to resolve ACL Action: $str");
        }
        $class = $array[0];
        $enumValue = $array[1];
        return new $class($enumValue);
    }
}
