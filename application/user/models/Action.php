<?php
/**
 * @author matthieu.napoli
 * @package User
 */

/**
 * Classe représentant les actions pouvant être réalisées sur les ressources
 *
 * @package User
 */
abstract class User_Model_Action extends Core_Enum
{

    /**
     * @return string
     */
    public function exportToString()
    {
        // Représentation du genre User_Model_Action_Default::READ
        return get_class($this) . '::' . $this->getValue();
    }

    /**
     * @param string $str
     * @throws Core_Exception_InvalidArgument
     * @return User_Model_Action
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
    public abstract function getLabel();

}
