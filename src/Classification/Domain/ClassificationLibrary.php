<?php

namespace Classification\Domain;

use Account\Domain\Account;
use Core\Translation\TranslatedString;
use Core_Exception_NotFound;
use Core_Exception_TooMany;
use Core_Model_Entity;
use Core_Model_Filter;
use Core_Model_Query;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use MyCLabs\ACL\Model\CascadingResource;
use MyCLabs\ACL\Model\EntityResource;

/**
 * Bibliothèque de classification.
 *
 * @author matthieu.napoli
 */
class ClassificationLibrary extends Core_Model_Entity implements EntityResource, CascadingResource
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var Account
     */
    protected $account;

    /**
     * @var TranslatedString
     */
    protected $label;

    /**
     * @var Indicator[]|Collection
     */
    protected $indicators;

    /**
     * @var Axis[]|Collection
     */
    protected $axes;

    /**
     * @var Context[]|Collection
     */
    protected $contexts;

    /**
     * @var ContextIndicator[]|Collection
     */
    protected $contextIndicators;

    /**
     * @var bool
     */
    protected $public = false;


    /**
     * @param Account          $account
     * @param TranslatedString $label
     * @param bool             $public
     */
    public function __construct(Account $account, TranslatedString $label, $public = false)
    {
        $this->account = $account;
        $this->label = $label;
        $this->public = $public;

        $this->indicators = new ArrayCollection();
        $this->axes = new ArrayCollection();
        $this->contexts = new ArrayCollection();
        $this->contextIndicators = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return TranslatedString
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param Indicator $indicator
     */
    public function addIndicator(Indicator $indicator)
    {
        $this->indicators->add($indicator);
    }

    /**
     * @param Indicator $indicator
     */
    public function removeIndicator(Indicator $indicator)
    {
        $this->indicators->removeElement($indicator);
    }

    /**
     * @param $ref
     * @throws \Core_Exception_NotFound
     * @throws \Core_Exception_TooMany
     * @return Axis
     */
    public function getIndicatorByRef($ref)
    {
        $criteria = Criteria::create();
        $criteria->where($criteria->expr()->eq('ref', $ref));
        $indicator = $this->indicators->matching($criteria)->toArray();

        if (count($indicator) === 0) {
            throw new Core_Exception_NotFound('No Indicator in ClassificationLibrary matching ref "'.$ref.'".');
        } elseif (count($indicator) > 1) {
            throw new Core_Exception_TooMany('Too many Indicator in ClassificationLibrary matching "'.$ref.'".');
        }

        return array_pop($indicator);
    }

    /**
     * @return Indicator[]|Collection
     */
    public function getIndicators()
    {
        return $this->indicators;
    }

    /**
     * @param Axis $axis
     */
    public function addAxis(Axis $axis)
    {
        $this->axes[] = $axis;
    }

    /**
     * @param Axis $axis
     */
    public function removeAxis(Axis $axis)
    {
        $this->axes->removeElement($axis);
    }

    /**
     * @param $ref
     * @throws \Core_Exception_NotFound
     * @throws \Core_Exception_TooMany
     * @return Axis
     */
    public function getAxisByRef($ref)
    {
        $criteria = Criteria::create();
        $criteria->where($criteria->expr()->eq('ref', $ref));
        $axis = $this->axes->matching($criteria)->toArray();

        if (count($axis) === 0) {
            throw new Core_Exception_NotFound('No Axis in ClassificationLibrary matching ref "'.$ref.'".');
        } elseif (count($axis) > 1) {
            throw new Core_Exception_TooMany('Too many Axis in ClassificationLibrary matching "'.$ref.'".');
        }

        return array_pop($axis);
    }

    /**
     * @return Axis[]
     */
    public function getRootAxes()
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('directNarrower', null));
        $criteria->orderBy(['position' => Criteria::ASC]);

        return $this->axes->matching($criteria)->toArray();
    }

    /**
     * @return Axis[]
     */
    public function getAxesOrderedAsAscendantTree()
    {
        $axes = [];
        foreach ($this->getRootAxes() as $rootAxis) {
            $axes = array_merge($axes, $rootAxis->getDirectBroaders());
            $axes[] = $rootAxis;
        }
        return $axes;
    }

    /**
     * @return Axis[]|Collection
     */
    public function getAxes()
    {
        return $this->axes;
    }

    /**
     * @param Context $context
     */
    public function addContext(Context $context)
    {
        $this->contexts[] = $context;
    }

    /**
     * @param Context $context
     */
    public function removeContext(Context $context)
    {
        $this->contexts->removeElement($context);
    }

    /**
     * @param $ref
     * @throws \Core_Exception_NotFound
     * @throws \Core_Exception_TooMany
     * @return Context
     */
    public function getContextByRef($ref)
    {
        $criteria = Criteria::create();
        $criteria->where($criteria->expr()->eq('ref', $ref));
        $context = $this->contexts->matching($criteria)->toArray();

        if (count($context) === 0) {
            throw new Core_Exception_NotFound('No Context in ClassificationLibrary matching ref "'.$ref.'".');
        } elseif (count($context) > 1) {
            throw new Core_Exception_TooMany('Too many Context in ClassificationLibrary matching "'.$ref.'".');
        }

        return array_pop($context);
    }

    /**
     * @return Context[]|Collection
     */
    public function getContexts()
    {
        return $this->contexts;
    }

    /**
     * @param ContextIndicator $contextIndicator
     */
    public function addContextIndicator(ContextIndicator $contextIndicator)
    {
        $this->contextIndicators[] = $contextIndicator;
    }

    /**
     * @param ContextIndicator $contextIndicator
     */
    public function removeContextIndicator(ContextIndicator $contextIndicator)
    {
        $this->contextIndicators->removeElement($contextIndicator);
    }

    /**
     * @return ContextIndicator[]
     */
    public function getContextIndicators()
    {
        return $this->contextIndicators->toArray();
    }

    /**
     * @param $refContext
     * @param $refIndicator
     * @throws \Core_Exception_NotFound
     * @return ContextIndicator
     */
    public function getContextIndicatorByRef($refContext, $refIndicator)
    {
        $context = $this->getContextByRef($refContext);
        $indicator = $this->getIndicatorByRef($refIndicator);

        $criteria = Criteria::create();
        $criteria->where($criteria->expr()->eq('context', $context));
        $criteria->andWhere($criteria->expr()->eq('indicator', $indicator));
        $context = $this->contextIndicators->matching($criteria);

        if ($context->isEmpty()) {
            throw new Core_Exception_NotFound;
        }

        return $context->first();
    }

    /**
     * @return bool Est-ce que la bibliothèque est publique ?
     */
    public function isPublic()
    {
        return $this->public;
    }

    /**
     * Rend publique (ou non) la bibliothèque.
     *
     * @param bool $public
     */
    public function setPublic($public)
    {
        $this->public = $public;
    }

    /**
     * {@inheritdoc}
     */
    public function getParentResources(EntityManager $entityManager)
    {
        return [ $this->account ];
    }

    /**
     * {@inheritdoc}
     */
    public function getSubResources(EntityManager $entityManager)
    {
        return [];
    }

    /**
     * @param Account $account
     * @return ClassificationLibrary[]
     */
    public static function loadByAccount(Account $account)
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition('account', $account);

        return self::getEntityRepository()->loadList($query);
    }

    /**
     * Renvoie toutes les librairies utilisables dans le compte donné.
     * Cela inclut les librairies du compte, mais également les librairies publiques.
     * @param Account $account
     * @return ClassificationLibrary[]
     */
    public static function loadUsableInAccount(Account $account)
    {
        $query = new Core_Model_Query();
        $query->filter->condition = Core_Model_Filter::CONDITION_OR;
        $query->filter->addCondition('account', $account);
        $query->filter->addCondition('public', true);

        return self::getEntityRepository()->loadList($query);
    }
}
