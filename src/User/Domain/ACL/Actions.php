<?php

namespace User\Domain\ACL;

use MyCLabs\ACL\Model\Actions as BaseActions;

/**
 * Actions des ACL personnalisés.
 */
class Actions extends BaseActions
{
    /**
     * Traverser une entité ne donne pas le droit de la voir, mais permet d'accéder
     * aux sous-objets auquels ont pourrait avoir accès.
     */
    const TRAVERSE = 'traverse';

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

    public $traverse = false;
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
            static::TRAVERSE,
            static::INPUT,
            static::ANALYZE,
            static::MANAGE_INVENTORY,
        ]);
    }
}
