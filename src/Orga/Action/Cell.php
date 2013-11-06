<?php

use User\Domain\ACL\Action;

/**
 * Actions pouvant être réalisées sur les ressources Cell.
 *
 * @author valentin.claras
 */
class Orga_Action_Cell extends Action
{
    /**
     * Commenter une ressource.
     */
    const COMMENT = 256;

    /**
     * Saisir une ressource.
     */
    const INPUT = 512;


    /**
     * @return self
     */
    public static function COMMENT()
    {
        return new static(self::COMMENT);
    }

    /**
     * @return self
     */
    public static function INPUT()
    {
        return new static(self::INPUT);
    }

    /**
     * @return string Libellé de l'action
     */
    public function getLabel()
    {
        switch ($this->value) {
            case self::COMMENT:
                return __('UI', 'verb', 'comment');
            case self::INPUT:
                return __('UI', 'verb', 'input');
        }
        return parent::getLabel();
    }
}
