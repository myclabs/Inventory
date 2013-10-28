<?php

namespace User\Architecture\TypeMapping;

use Core_Exception_InvalidArgument;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use User\Domain\ACL\Action;

/**
 * Mapping d'un objet Action en champ de BDD.
 *
 * @author  matthieu.napoli
 */
class ActionType extends Type
{
    const TYPE_NAME = 'user_action';

    /**
     * @return string The name of the type being mapped
     */
    public function getName()
    {
        return self::TYPE_NAME;
    }

    /**
     * @param array            $fieldDeclaration
     * @param AbstractPlatform $platform
     * @return string
     */
    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        // Same as "string" type
        return $platform->getVarcharTypeDeclarationSQL($fieldDeclaration);
    }

    /**
     * @param AbstractPlatform $platform
     * @return int|null
     */
    public function getDefaultLength(AbstractPlatform $platform)
    {
        return $platform->getVarcharDefaultLength();
    }

    /**
     * @param string           $value
     * @param AbstractPlatform $platform
     * @throws Core_Exception_InvalidArgument
     * @return \User\Domain\ACL\Action
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }
        return Action::importFromString($value);
    }

    /**
     * @param Action $value
     * @param AbstractPlatform  $platform
     * @return string
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }
        return $value->exportToString();
    }
}
