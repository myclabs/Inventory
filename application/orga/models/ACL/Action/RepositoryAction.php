<?php

namespace Orga\Model\ACL\Action;

use User\Domain\ACL\Action;

/**
 * Actions pouvant être réalisées sur les référentiels.
 *
 * @author  valentin.claras
 */
class RepositoryAction extends Action
{
    /**
     * Traduire une ressource.
     */
    const TRANSLATE = 1024;


    /**
     * @return RepositoryAction
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
        return parent::getLabel();
    }
}
