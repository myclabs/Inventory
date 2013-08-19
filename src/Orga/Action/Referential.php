<?php
/**
 * @author valentin.claras
 * @package Orga
 */

/**
 * Actions pouvant être réalisées sur les ressources Referential.
 *
 * @package Orga
 */
class Orga_Action_Referential extends User_Model_Action
{

    /**
     * Traduire une ressource.
     */
    const TRANSLATE = 1024;


    /**
     * @return Orga_Action_Referential
     */
    public static function TRANSLATE()
    {
        return new static(self::TRANSLATE);
    }

    /**
     * @return string Libellé de l'action
     */
    public function getLabel()
    {
        switch ($this->value) {
            case self::TRANSLATE:
                return __('UI', 'verb', 'translate');
        }
        return '';
    }

}
