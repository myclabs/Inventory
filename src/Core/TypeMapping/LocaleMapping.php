<?php
/**
 * @author  matthieu.napoli
 * @package Core
 */

namespace Core\TypeMapping;

use Core_Locale;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Mapping d'un objet Locale en champ de BDD
 * @package Core
 */
class LocaleMapping extends Type
{

    const TYPE_NAME = 'core_locale';

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
     * @return Core_Locale
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }
        return Core_Locale::load($value);
    }

    /**
     * @param Core_Locale      $value
     * @param AbstractPlatform $platform
     * @return string
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }
        return $value->getId();
    }

}
