<?php
/**
 * @author matthieu.napoli
 * @package Keyword
 */

namespace Keyword\Architecture\TypeMapping;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Keyword\Application\KeywordService;
use Keyword\Application\KeywordDTO;

/**
 * Mapping d'un objet Value en champ de BDD
 * @package Keyword
 */
class DoctrineKeyword extends Type
{
    const TYPE_NAME = 'keyword_dto';

    /**
     * @Inject
     * @var KeywordService
     */
    protected $keywordService;

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
     * @return KeywordDTO|null
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        return $this->keywordService->get($value);
    }

    /**
     * @param KeywordDTO|null $value
     * @param AbstractPlatform $platform
     * @return string|null
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        return $value->getRef();
    }

}
