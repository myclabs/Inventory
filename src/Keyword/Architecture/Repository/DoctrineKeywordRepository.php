<?php

namespace Keyword\Architecture\Repository;

use Core\Domain\DoctrineEntityRepository;
use Core\Domain\Translatable\TranslatableRepository;
use Core_Exception_NotFound;
use Doctrine\ORM\QueryBuilder;
use Keyword\Domain\Keyword;
use Keyword\Domain\KeywordRepository;

/**
 * Gère les Keyword.
 * @author valentin.claras
 */
class DoctrineKeywordRepository extends DoctrineEntityRepository implements KeywordRepository
{
    use TranslatableRepository;

    /**
     * Renoie les messages d'erreur concernant la validation d'une ref.
     *
     * @param string $ref
     *
     * @return mixed string null
     */
    public function getErrorMessageForRef($ref)
    {
        try {
            \Core_Tools::checkRef($ref);
        } catch (\Core_Exception_User $e) {
            return $e->getMessage();
        }
        if ($ref === 'this') {
            return __('Keyword', 'list', 'keywordRefThis');
        }
        try {
            $existingKeywordWithRef = $this->getOneByRef($ref);
            return __('UI', 'formValidation', 'alreadyUsedIdentifier', array('REF' => $ref));
        } catch (\Core_Exception_NotFound $e) {
            // Pas de Keyword trouvé.
        }
        return null;
    }

    /**
     * Vérifie la disponibilité d'une référence pour un keyword.
     *
     * @param string $ref
     *
     * @throws \Core_Exception_User
     */
    public function checkRef($ref)
    {
        \Core_Tools::checkRef($ref);
        if ($ref === 'this') {
            throw new \Core_Exception_User('Keyword', 'list', 'keywordRefThis');
        }
        try {
            $existingKeywordWithRef = $this->getOneByRef($ref);
            throw new \Core_Exception_User('UI', 'formValidation', 'alreadyUsedIdentifier', array('REF' => $ref));
        } catch (\Core_Exception_NotFound $e) {
            // Pas de Keyword trouvé.
        }
    }

    /**
     * Retourne un Keyword grâce à son ref.
     *
     * @param string $keywordRef
     * @throws \Core_Exception_NotFound
     * @throws \Core_Exception_TooMany
     * @return Keyword
     */
    public function getOneByRef($refKeyword)
    {
        return $this->getOneBy(['ref' => $refKeyword]);
    }

    /**
     * Charge la liste des Keyword ne possédant pas d'association en tant qu'objet.
     *
     * @return Keyword[]
     */
    public function getRoots()
    {
        $queryBuilderLoadListRoots = $this->createQueryBuilder('Keyword');
        $queryBuilderLoadListRoots->distinct();
        $queryBuilderLoadListRoots->leftJoin('Keyword.objectAssociations', 'Association');
        $queryBuilderLoadListRoots->addGroupBy('Keyword.id');
        $queryBuilderLoadListRoots->having($queryBuilderLoadListRoots->expr()->eq('count(Association.predicate)', 0));

        $query = $queryBuilderLoadListRoots->getQuery();
        $query->setHint(
            \Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER,
            'Gedmo\Translatable\Query\TreeWalker\TranslationWalker'
        );
        $query->setHint(
            \Gedmo\Translatable\TranslatableListener::HINT_TRANSLATABLE_LOCALE,
            \Core_Locale::loadDefault()->getLanguage()
        );

        return $query->getResult();
    }

}
