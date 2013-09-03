<?php

namespace Keyword\Architecture\Repository;

use Core\Domain\DoctrineEntityRepository;
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
        return $this->findOneBy(['ref' => $refKeyword]);
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
        $queryBuilderLoadListRoots->leftJoin('Keyword.objectAssociation', 'Association');
        $queryBuilderLoadListRoots->addGroupBy('Keyword.id');
        $queryBuilderLoadListRoots->having($queryBuilderLoadListRoots->expr()->eq('count(Association.predicate)', 0));

        $query = $queryBuilderLoadListRoots->getQuery();
        $query->setHint(
            \Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER,
            'Gedmo\Translatable\Query\TreeWalker\TranslationWalker'
        );
        $query->setHint(
            \Gedmo\Translatable\TranslatableListener::HINT_TRANSLATABLE_LOCALE,
            Core_Locale::loadDefault()->getLanguage()
        );

        return $query->getResult();
    }

}
