<?php

namespace User\Domain\ACL\Action;

use Core_Exception_InvalidArgument;
use MyCLabs\Enum\Enum;

/**
 * Classe représentant les actions pouvant être réalisées sur les ressources.
 *
 * @author matthieu.napoli
 */
abstract class Action extends Enum
{
    /**
     * @return string
     */
    public function exportToString()
    {
        // Représentation du genre DefaultAction::READ
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

    /**
     * @return string Libellé de l'action
     */
    abstract public function getLabel();
}
