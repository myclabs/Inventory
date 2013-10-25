<?php

use User\Architecture\TypeMapping\ActionType;
use User\Domain\ACL\ACLFilterEntry;

/**
 * Requete avec filtres et tri.
 *
 * @author matthieu.napoli
 */
class Core_Model_Query
{
    /**
     * Nombre d'éléments maximum renvoyés par la requête.
     *
     * @var int|null Si null, pas de maximum
     */
    public $totalElements = null;

    /**
     * Nombre d'éléments à ignorer (offset de début).
     *
     * @var int
     */
    public $startIndex = null;

    /**
     * Tri.
     *
     * @var Core_Model_Order
     */
    public $order = null;

    /**
     * Filtre.
     *
     * @var Core_Model_Filter
     */
    public $filter = null;

    /**
     * Filtrage en utilisant les ACL.
     *
     * @var Core_Model_AclFilter
     */
    public $aclFilter = null;

    /**
     * Parametres personnalisés.
     *
     * @var array(nomParametre => valeur)
     */
    protected $parameters = [];

    /**
     * Classe de l'objet sur lequel est appliqué la requête.
     * @var string
     */
    public $entityName = null;

    /**
     * Alias principal de l'objet sur lequel est appliqué la requête.
     * @var string
     */
    public $rootAlias = null;


    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->order = new Core_Model_Order();
        $this->filter = new Core_Model_Filter();
        $this->aclFilter = new Core_Model_AclFilter();
    }

    /**
     * Méthode appelé lors du clonage.
     */
    public function __clone()
    {
        $this->order = clone $this->order;
        $this->filter = clone $this->filter;
        $this->aclFilter = clone $this->aclFilter;
    }

    /**
     * Valide les attributs de la classe.
     *
     * @throws Core_Exception_InvalidArgument
     * @return void
     */
    public function validate()
    {
        $this->order->validate();
        $this->filter->validate();
        $this->aclFilter->validate();
        // Vérification de startIndex
        if (!is_null($this->startIndex)) {
            if ((!is_int($this->startIndex)) || ($this->startIndex < 0)) {
                throw new Core_Exception_InvalidArgument('startIndex has invalid value (should be 0 or positive int)');
            }
        }
        // Vérification de totalElements
        if (!is_null($this->totalElements)) {
            if ((!is_int($this->totalElements)) || ($this->totalElements < 1)) {
                throw new Core_Exception_InvalidArgument('totalElements has invalid value (should be a positive int)');
            }
        }
        // Si totalElements n'est pas définie, startIndex ne doit pas être défini
        if ((is_null($this->totalElements)) && (!is_null($this->startIndex))) {
            throw new Core_Exception_InvalidArgument('When totalElements is null, startIndex has to be null too.');
        }
    }

    /**
     * Retourne les paramètres personnalisés qui ont été ajouté à la requête
     *
     * @return array(nomParametre => valeur)
     */
    public function getCustomParameters()
    {
        return $this->parameters;
    }

    /**
     * Définit les parametres personnalises
     *
     * Préferer la définition des paramètres un à un : $requete->name = valeur
     *
     * @param array(nomParametre => valeur) $parameters Paramètres
     *
     * @return void
     */
    public function setCustomParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Fonction magique PHP __get
     *
     * @param mixed $property
     *
     * @throws Core_Exception_UndefinedAttribute
     * @return mixed
     */
    public function & __get($property)
    {
        if (isset($this->parameters[$property])) {
            return $this->parameters[$property];
        } else {
            throw new Core_Exception_UndefinedAttribute("Attempt to access undefined custom property : $property");
        }
    }

    /**
     * Fonction magique PHP __set
     *
     * @param mixed $property
     * @param mixed $value
     */
    public function __set($property, $value)
    {
        $this->parameters[$property] = $value;
    }

    /**
     * Fonction magique PHP __isset
     *
     * @param mixed $property
     * @return bool
     */
    public function __isset($property)
    {
        return isset($this->parameters[$property]);
    }

    /**
     * Ajoute au queryBuilder de Doctrine les paramètres de la requête, sans limitation de résultats.
     *
     * @param Doctrine\ORM\QueryBuilder $queryBuilder
     *
     * @return void
     */
    public function parseToQueryBuilderWithoutLimit(Doctrine\ORM\QueryBuilder $queryBuilder)
    {
        // Vérifie le format des données.
        $this->validate();
        $this->addOrderToQueryBuilder($queryBuilder);
        $this->addFilterToQueryBuilder($queryBuilder);
        $this->addAclFilterToQueryBuilder($queryBuilder);
    }

    /**
     * Ajoute au queryBuilder de Doctrine les paramètres de la requête, avec limitation des résultats.
     *
     * @param Doctrine\ORM\QueryBuilder $queryBuilder
     *
     * @return void
     */
    public function parseToQueryBuilderWithLimit(Doctrine\ORM\QueryBuilder $queryBuilder)
    {
        $this->parseToQueryBuilderWithoutLimit($queryBuilder);
        $this->addLimitToQueryBuilder($queryBuilder);
    }

    /**
     * Ajoute au QueryBuilder de Doctrine l'ordre fourni par la requête.
     *
     * @param Doctrine\ORM\QueryBuilder $queryBuilder
     *
     * @return void
     */
    protected function addOrderToQueryBuilder(Doctrine\ORM\QueryBuilder $queryBuilder)
    {
        foreach ($this->order->getOrders() as $order) {
            $queryBuilder->addOrderBy($this->getFullAlias($order), $order['direction']);
        }
    }

    /**
     * Ajoute au QueryBuilder de Doctrine les filtres fourni par la requête.
     *
     * @param Doctrine\ORM\QueryBuilder $queryBuilder
     *
     * @return void
     */
    protected function addFilterToQueryBuilder(Doctrine\ORM\QueryBuilder $queryBuilder)
    {
        $logicalExpression = $this->getMainExpression($queryBuilder);
        if ($logicalExpression->count() > 0) {
            $queryBuilder->add('where', $logicalExpression);
        }
    }

    /**
     * Renvoie l'expression logique correspondant au filtre principal.
     *
     * @param Doctrine\ORM\QueryBuilder $queryBuilder
     *
     * @return Doctrine\ORM\Query\Expr\Composite
     */
    protected function getMainExpression(Doctrine\ORM\QueryBuilder $queryBuilder)
    {
        return $this->getExpression($queryBuilder, $this->filter, 'main');
    }

    /**
     * Renvoie une expression logique en fonction d'une filtre.
     *
     * @param Doctrine\ORM\QueryBuilder $queryBuilder
     * @param Core_Model_Filter         $filter
     * @param string                    $bindBase
     *
     * @throws Core_Exception_InvalidArgument
     * @return Doctrine\ORM\Query\Expr\Composite
     */
    protected function getExpression(Doctrine\ORM\QueryBuilder $queryBuilder, Core_Model_Filter $filter,
                                     $bindBase = null
    ) {
        /* @var $logicalExpression Doctrine\ORM\Query\Expr\Composite */
        if ($filter->condition === Core_Model_Filter::CONDITION_OR) {
            $logicalExpression = $queryBuilder->expr()->orx();
        } else {
            $logicalExpression = $queryBuilder->expr()->andx();
        }
        foreach ($filter->getConditions() as $key => $condition) {
            $bindKey = (string) $bindBase . $key;
            // Création de l'expression de la condition.
            switch ($condition['operator']) {
                case Core_Model_Filter::OPERATOR_EQUAL:
                    $conditionExpression = $queryBuilder->expr()->eq($this->getFullAlias($condition), ':' . $bindKey);
                    break;
                case Core_Model_Filter::OPERATOR_CONTAINS:
                case Core_Model_Filter::OPERATOR_BEGINS:
                case Core_Model_Filter::OPERATOR_ENDS:
                    $conditionExpression = $queryBuilder->expr()->like($this->getFullAlias($condition), ':' . $bindKey);
                    break;
                case Core_Model_Filter::OPERATOR_HIGHER:
                    $conditionExpression = $queryBuilder->expr()->gt($this->getFullAlias($condition), ':' . $bindKey);
                    break;
                case Core_Model_Filter::OPERATOR_LOWER:
                    $conditionExpression = $queryBuilder->expr()->lt($this->getFullAlias($condition), ':' . $bindKey);
                    break;
                case Core_Model_Filter::OPERATOR_HIGHER_EQUAL:
                    $conditionExpression = $queryBuilder->expr()->gte($this->getFullAlias($condition), ':' . $bindKey);
                    break;
                case Core_Model_Filter::OPERATOR_LOWER_EQUAL:
                    $conditionExpression = $queryBuilder->expr()->lte($this->getFullAlias($condition), ':' . $bindKey);
                    break;
                case Core_Model_Filter::OPERATOR_NOT_EQUAL:
                    $conditionExpression = $queryBuilder->expr()->neq($this->getFullAlias($condition), ':' . $bindKey);
                    break;
                case Core_Model_Filter::OPERATOR_NULL:
                    $conditionExpression = $queryBuilder->expr()->isNull($this->getFullAlias($condition));
                    break;
                case Core_Model_Filter::OPERATOR_NOT_NULL:
                    $conditionExpression = $queryBuilder->expr()->isNotNull($this->getFullAlias($condition));
                    break;
                case Core_Model_Filter::OPERATOR_SUB_FILTER:
                    $conditionExpression = $this->getExpression($queryBuilder, $condition['value'], $condition['name']);
                    break;
                default:
                    throw new Core_Exception_InvalidArgument("Unknown operator " . $condition['operator']);
            }
            // Ajout de l'expression condition du filtre à la condition globale du query builder.
            $logicalExpression->add($conditionExpression);
            // Ajout du paramètre correspondant au filtre.
            switch ($condition['operator']) {
                case Core_Model_Filter::OPERATOR_EQUAL:
                case Core_Model_Filter::OPERATOR_HIGHER:
                case Core_Model_Filter::OPERATOR_LOWER:
                case Core_Model_Filter::OPERATOR_HIGHER_EQUAL:
                case Core_Model_Filter::OPERATOR_LOWER_EQUAL:
                case Core_Model_Filter::OPERATOR_NOT_EQUAL:
                    $queryBuilder->setParameter($bindKey, $condition['value']);
                    break;
                case Core_Model_Filter::OPERATOR_CONTAINS:
                    $queryBuilder->setParameter($bindKey, '%' . $condition['value'] . '%');
                    break;
                case Core_Model_Filter::OPERATOR_BEGINS:
                    $queryBuilder->setParameter($bindKey, $condition['value'] . '%');
                    break;
                case Core_Model_Filter::OPERATOR_ENDS:
                    $queryBuilder->setParameter($bindKey, '%' . $condition['value']);
                    break;
                case Core_Model_Filter::OPERATOR_NULL:
                case Core_Model_Filter::OPERATOR_NOT_NULL:
                case Core_Model_Filter::OPERATOR_SUB_FILTER:
                default:
            }
        }
        return $logicalExpression;
    }

    /**
     * Ajoute au QueryBuilder de Doctrine les filtres d'ACL fourni par la requête.
     *
     * @param Doctrine\ORM\QueryBuilder $queryBuilder
     *
     * @return void
     */
    protected function addAclFilterToQueryBuilder(Doctrine\ORM\QueryBuilder $queryBuilder)
    {
        if ($this->aclFilter->enabled) {
            $queryBuilder->innerJoin(ACLFilterEntry::class, 'acl_cache');

            $queryBuilder->andWhere('acl_cache.idUser = :aclUserId');
            $queryBuilder->andWhere('acl_cache.action = :aclAction');
            $queryBuilder->andWhere('acl_cache.entityName = :aclEntityName');
            $queryBuilder->andWhere('acl_cache.entityIdentifier = ' . $this->rootAlias . '.id');

            $queryBuilder->setParameter('aclEntityName', $this->entityName);
            $queryBuilder->setParameter('aclAction', $this->aclFilter->action, ActionType::TYPE_NAME);
            $queryBuilder->setParameter('aclUserId', $this->aclFilter->user->getId());
        }
    }

    /**
     * Ajoute au QueryBuilder de Doctrine l'offset et la limit fourni par la requête.
     *
     * @param Doctrine\ORM\QueryBuilder $queryBuilder
     *
     * @return void
     */
    protected function addLimitToQueryBuilder(Doctrine\ORM\QueryBuilder $queryBuilder)
    {
        if (($this->totalElements !== null) && ($this->totalElements > 0)) {
            if ($this->startIndex !== null) {
                $queryBuilder->setFirstResult($this->startIndex);
            }
            $queryBuilder->setMaxResults($this->totalElements);
        }
    }

    /**
     * Renvoie l'alias de l'attribut préfixé par l'alias de l'objet ou le root alias.
     *
     * @param array $condition
     *
     * @throws Core_Exception_UndefinedAttribute
     * @return string
     */
    protected function getFullAlias(array $condition)
    {
        if (empty($condition['alias'])) {
            if (($this->rootAlias === null) || ($this->rootAlias === '')) {
                throw new Core_Exception_UndefinedAttribute(
                    'Neither Alias or RootAlias for condition "' . $condition['name'] . '" are defined'
                );
            } else {
                $baseAlias = $this->rootAlias;
            }
        } else {
            $baseAlias = $condition['alias'];
        }
        return $baseAlias . '.' . $condition['name'];
    }
}
