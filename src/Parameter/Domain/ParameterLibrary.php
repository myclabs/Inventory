<?php

namespace Parameter\Domain;

use Account\Domain\Account;
use Core_Model_Entity;
use Core_Model_Entity_Translatable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use MyCLabs\ACL\Model\CascadingResource;
use MyCLabs\ACL\Model\EntityResource;
use Parameter\Domain\Family\Family;

/**
 * Bibliothèque de paramètres.
 *
 * @author matthieu.napoli
 */
class ParameterLibrary extends Core_Model_Entity implements EntityResource, CascadingResource
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
     * @var Category[]|Collection
     */
    protected $categories;

    /**
     * @var Family[]|Collection
     */
    protected $families;

    /**
     * @param Account $account
     * @param string  $label
     */
    public function __construct(Account $account, $label)
    {
        $this->account = $account;
        $this->label = $label;
        $this->categories = new ArrayCollection();
        $this->families = new ArrayCollection();
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

    public function addCategory(Category $category)
    {
        $this->categories->add($category);
    }

    public function removeCategory(Category $category)
    {
        $this->categories->removeElement($category);
    }

    public function addFamily(Family $family)
    {
        $this->families->add($family);
    }

    public function removeFamily(Family $family)
    {
        $this->families->removeElement($family);
    }

    /**
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param int|null $count
     * @param int|null $offset
     * @return Collection|Family[]
     */
    public function getFamilies($count = null, $offset = null)
    {
        $criteria = Criteria::create();
        $criteria->setMaxResults($count);
        $criteria->setFirstResult($offset);

        return $this->families->matching($criteria);
    }

    /**
     * @return Category[]
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @return Category[]
     */
    public function getRootCategories()
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->isNull('parentCategory'));

        return $this->categories->matching($criteria);
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
}
