<?php
/**
 * @author matthieu.napoli
 * @package Calc
 */

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Mapping d'un objet Value en champ de BDD
 * @package Calc
 */
class Calc_TypeMapping_Value extends Type
{

    const TYPE_NAME = 'calc_value';

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
     * @param string $value
     * @param AbstractPlatform $platform
     * @return Calc_Value
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return unserialize($value);
    }

    /**
     * @param Calc_Value $value
     * @param AbstractPlatform $platform
     * @return string
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return serialize($value);
    }

}
