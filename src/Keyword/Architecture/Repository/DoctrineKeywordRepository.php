<?php

namespace Keyword\Architecture\Repository;

use Core\Domain\DoctrineEntityRepository;
use Core\Domain\Translatable\TranslatableRepository;
use Core_Exception_NotFound;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Translatable\TranslatableListener;
use Keyword\Domain\AssociationCriteria;
use Keyword\Domain\KeywordRepository;
use Keyword\Architecture\Repository\DoctrineAssociationRepository;
use Keyword\Domain\Keyword;
use Keyword\Domain\Predicate;
use Keyword\Domain\Association;

/**
 * Gère les Keyword.
 * @author valentin.claras
 */
class DoctrineKeywordRepository extends DoctrineEntityRepository implements KeywordRepository
{
    use TranslatableRepository;

    /**
     * {@inheritdoc}
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
            $this->getByRef($ref);
            return __('UI', 'formValidation', 'alreadyUsedIdentifier', array('REF' => $ref));
        } catch (\Core_Exception_NotFound $e) {
            // Pas de Keyword trouvé.
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function checkRef($ref)
    {
        \Core_Tools::checkRef($ref);
        if ($ref === 'this') {
            throw new \Core_Exception_User('Keyword', 'list', 'keywordRefThis');
        }
        try {
            $this->getByRef($ref);
            throw new \Core_Exception_User('UI', 'formValidation', 'alreadyUsedIdentifier', array('REF' => $ref));
        } catch (\Core_Exception_NotFound $e) {
            // Pas de Keyword trouvé.
        }
    }

    /**
     * @param Keyword $entity
     */
    public function add($entity)
    {
        $this->checkRef($entity->getRef());
        parent::add($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function getByRef($keywordRef)
    {
        return $this->getBy(['ref' => $keywordRef]);
    }

    /**
     * {@inheritdoc}
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
            TranslatableListener::HINT_TRANSLATABLE_LOCALE,
            \Core_Locale::loadDefault()->getLanguage()
        );

        return $query->getResult();
    }

    /**
     * @return DoctrineAssociationRepository
     */
    protected function getAssociationRepository ()
    {
        return $this->getEntityManager()->getRepository('\Keyword\Domain\Association');
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorMessageForAssociation(Keyword $subjectKeyword, Predicate $predicate, Keyword $objectKeyword)
    {
        return $this->getAssociationRepository()->getErrorMessageForAssociation($subjectKeyword, $predicate, $objectKeyword);
    }

    /**
     * {@inheritdoc}
     */
    public function checkAssociation(Keyword $subjectKeyword, Predicate $predicate, Keyword $objectKeyword)
    {
        $this->getAssociationRepository()->checkAssociation($subjectKeyword, $predicate, $objectKeyword);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllAssociations(AssociationCriteria $criteria = null)
    {
        if ($criteria) {
            return $this->getAssociationRepository()->matching($criteria);
        }
        return $this->getAssociationRepository()->getAll();
    }

    /**
     * {@inheritdoc}
     */
    public function countAssociations()
    {
        return $this->getAssociationRepository()->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociation(Keyword $subjectKeyword, Predicate $predicate, Keyword $objectKeyword)
    {
        return $this->getAssociationRepository()->getBySubjectPredicateObject($subjectKeyword, $predicate, $objectKeyword);
    }

}
