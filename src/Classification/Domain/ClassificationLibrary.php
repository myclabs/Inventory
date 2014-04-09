<?php

namespace Classification\Domain;

use Account\Domain\Account;
use Core_Model_Entity;
use Core_Model_Entity_Translatable;
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
    use Core_Model_Entity_Translatable;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var Account
     */
    protected $account;

    /**
     * @var string
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
     * @param Account $account
     * @param string  $label
     */
    public function __construct(Account $account, $label)
    {
        $this->account = $account;
        $this->label = $label;

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
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return Indicator[]
     */
    public function getIndicators()
    {
        return $this->indicators->toArray();
    }

    public function addIndicator(Indicator $indicator)
    {
        $this->indicators->add($indicator);
    }

    public function removeIndicator(Indicator $indicator)
    {
        $this->indicators->removeElement($indicator);
    }

    /**
     * @return Axis[]
     */
    public function getAxes()
    {
        return $this->axes->toArray();
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

    public function addAxis(Axis $axis)
    {
        $this->axes[] = $axis;
    }

    public function removeAxis(Axis $axis)
    {
        $this->axes->removeElement($axis);
    }

    /**
     * @return Context[]
     */
    public function getContexts()
    {
        return $this->contexts->toArray();
    }

    public function addContext(Context $context)
    {
        $this->contexts[] = $context;
    }

    public function removeContext(Context $context)
    {
        $this->contexts->removeElement($context);
    }

    /**
     * @return ContextIndicator[]
     */
    public function getContextIndicators()
    {
        return $this->contextIndicators->toArray();
    }

    public function addContextIndicator(ContextIndicator $contextIndicator)
    {
        $this->contextIndicators[] = $contextIndicator;
    }

    public function removeContextIndicator(ContextIndicator $contextIndicator)
    {
        $this->contextIndicators->removeElement($contextIndicator);
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
}
