<?php
/**
 * @author matthieu.napoli
 * @package Unit
 */

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Mapping d'un objet Unit API en champ de BDD
 * @package Unit
 */
class Unit_TypeMapping_UnitAPI extends Type
{

    const TYPE_NAME = 'unit_api';

    /**
     * @return string The name of the type being mapped
     */
    public function getName()
    {
        return self::TYPE_NAME;
    }

    /**
     * @param array $fieldDeclaration
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
     * @param string $unitRef
     * @param AbstractPlatform $platform
     * @return Unit_API
     */
    public function convertToPHPValue($unitRef, AbstractPlatform $platform)
    {
        if (empty($unitRef)) {
            return null;
        }
        return new Unit_API($unitRef);
    }

    /**
     * @param Unit_API $unit
     * @param AbstractPlatform $platform
     * @return string
     */
    public function convertToDatabaseValue($unit, AbstractPlatform $platform)
    {
        $ref = $unit->getRef();
        if (empty($ref)) {
            return null;
        }
        return $ref;
    }

}
