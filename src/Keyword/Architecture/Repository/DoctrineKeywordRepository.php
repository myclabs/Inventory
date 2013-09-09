<?php

namespace Keyword\Architecture\Repository;

use Core\Domain\DoctrineEntityRepository;
use Core\Domain\Translatable\TranslatableRepository;
use Core_Exception_NotFound;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Translatable\TranslatableListener;
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
        return $this->getOneBy(['ref' => $keywordRef]);
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
     * Renoie les messages d'erreur concernant la validation d'une Association.
     *
     * @param \Keyword\Domain\Keyword $subjectKeyword
     * @param \Keyword\Domain\Predicate $predicate
     * @param \Keyword\Domain\Keyword $objectKeyword
     *
     * @return mixed string null
     */
    public function getErrorMessageForAssociation(Keyword $subjectKeyword, Predicate $predicate, Keyword $objectKeyword)
    {
        return $this->getAssociationRepository()->getErrorMessageForAssociation($subjectKeyword, $predicate, $objectKeyword);
    }

    /**
     * Vérifie la disponibilité d'une Association.
     *
     * @param Keyword $subjectKeyword
     * @param Predicate $predicate
     * @param Association $objectKeyword
     *
     * @throws \Core_Exception_User
     */
    public function checkAssociation(Keyword $subjectKeyword, Predicate $predicate, Keyword $objectKeyword)
    {
        $this->getAssociationRepository()->checkAssociation($subjectKeyword, $predicate, $objectKeyword);
    }

    /**
     * @return Association[]
     */
    public function getAllAssociations()
    {
        return $this->getAssociationRepository()->getAll();
    }

    /**
     * @return int
     */
    public function countAssociations()
    {
        return $this->getAssociationRepository()->count();
    }

    /**
     * Charge une Association en fonction des refs de ses composants.
     *
     * @param Keyword $subjectKeyword
     * @param Predicate $predicate
     * @param Keyword $objectKeyword
     * @throws \Core_Exception_NotFound
     * @throws \Core_Exception_TooMany
     * @return Association
     */
    public function getAssociationBySubjectPredicateObject(Keyword $subjectKeyword, Predicate $predicate, Keyword $objectKeyword)
    {
        return $this->getAssociationRepository()->getBySubjectPredicateObject($subjectKeyword, $predicate, $objectKeyword);
    }

}
