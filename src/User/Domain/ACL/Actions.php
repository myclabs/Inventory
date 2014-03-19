<?php

namespace User\Domain\ACL;

use MyCLabs\ACL\Model\Actions as BaseActions;

/**
 * Actions des ACL personnalisés.
 */
class Actions extends BaseActions
{
    /**
     * Modifier une saisie.
     */
    const INPUT = 'input';

    /**
     * Analyser les données.
     */
    const ANALYZE = 'analyze';

    /**
     * Gérer l'inventaire.
     */
    const MANAGE_INVENTORY = 'manageInventory';

    public $input = false;
    public $analyze = false;
    public $manageInventory = false;

    /**
     * {@inheritdoc}
     */
    public static function all()
    {
        return new static([
            static::VIEW,
            static::CREATE,
            static::EDIT,
            static::DELETE,
            static::UNDELETE,
            static::ALLOW,
            static::INPUT,
            static::ANALYZE,
            static::MANAGE_INVENTORY,
        ]);
    }
}
