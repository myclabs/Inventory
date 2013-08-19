<?php
/**
 * @author valentin.claras
 * @package Orga
 */

/**
 * Actions pouvant être réalisées sur les ressources Report.
 *
 * @package Orga
 */
class Orga_Action_Report extends User_Model_Action
{

    /**
     * Éditer une ressource.
     */
    const EDIT = 5;


    /**
     * @return Orga_Action_Report
     */
    public static function EDIT()
    {
        return new static(self::EDIT);
    }

    /**
     * @return string Libellé de l'action
     */
    public function getLabel()
    {
        switch ($this->value) {
            case self::EDIT:
                return __('UI', 'verb', 'edit');
        }
        return '';
    }

}
