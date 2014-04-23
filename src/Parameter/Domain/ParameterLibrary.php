<?php

namespace Parameter\Domain;

use Account\Domain\Account;
use Core_Model_Entity;
use Core_Model_Entity_Translatable;
use Core_Model_Filter;
use Core_Model_Query;
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
     * @var bool
     */
    protected $public = false;

    /**
     * @param Account $account
     * @param string  $label
     * @param bool    $public
     */
    public function __construct(Account $account, $label, $public = false)
    {
        $this->account = $account;
        $this->label = $label;
        $this->public = $public;

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
     * @param string $ref
     * @throws \Core_Exception_NotFound
     * @return Family
     */
    public function getFamily($ref)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('ref', $ref));

        $families = $this->families->matching($criteria);

        if (count($families) === 0) {
            throw new \Core_Exception_NotFound;
        }

        return $families->first();
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
     * @return ParameterLibrary[]
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
     * @return ParameterLibrary[]
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
