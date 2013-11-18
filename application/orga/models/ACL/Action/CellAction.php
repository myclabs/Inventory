<?php

namespace Orga\Model\ACL\Action;

use User\Domain\ACL\Action;

/**
 * Actions pouvant être réalisées sur les ressources Cell.
 *
 * @author valentin.claras
 */
class CellAction extends Action
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
     * Voir les rapports de la cellule.
     */
    const VIEW_REPORTS = 1024;


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
     * @return self
     */
    public static function VIEW_REPORTS()
    {
        return new static(self::VIEW_REPORTS);
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
