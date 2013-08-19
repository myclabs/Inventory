<?php
/**
 * @author matthieu.napoli
 * @package Calc
 */

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Mapping d'un objet UnitValue en champ de BDD
 * @package Calc
 */
class Calc_TypeMapping_UnitValue extends Type
{

    const TYPE_NAME = 'calc_unitvalue';

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
     * @param string|null $value
     * @param AbstractPlatform $platform
     * @return Calc_UnitValue|null
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        return Calc_UnitValue::createFromString($value);
    }

    /**
     * @param Calc_UnitValue|null $value
     * @param AbstractPlatform $platform
     * @return string|null
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        return $value->exportToString();
    }

}
