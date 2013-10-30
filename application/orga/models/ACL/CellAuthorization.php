<?php

namespace Orga\Model\ACL;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Orga_Model_Cell;
use User\Domain\ACL\Action;
use User\Domain\ACL\Authorization\Authorization;
use User\Domain\User;

/**
 * Autorisation d'accès à une cellule.
 *
 * @author matthieu.napoli
 */
class CellAuthorization extends Authorization
{
    /**
     * @var Orga_Model_Cell
     */
    protected $resource;

    /**
     * Héritage des droits entre ressources.
     *
     * @var CellAuthorization
     */
    protected $parentAuthorization;

    /**
     * @var CellAuthorization[]|Collection
     */
    protected $childAuthorizations;

    /**
     * @param User                   $user
     * @param Action                 $action
     * @param Orga_Model_Cell        $resource
     */
    public function __construct(User $user, Action $action, Orga_Model_Cell $resource)
    {
        $this->user = $user;
        $this->setAction($action);
        $this->resource = $resource;

        $this->resource->addToACL($this);

        $this->childAuthorizations = new ArrayCollection();
    }

    /**
     * Crée une autorisation qui hérite de celle-ci.
     *
     * @param Orga_Model_Cell $cell
     * @return CellAuthorization
     */
    public function createChildAuthorization(Orga_Model_Cell $cell)
    {
        $authorization = new self($this->user, $this->getAction(), $cell);
        $authorization->parentAuthorization = $this;

        $this->childAuthorizations->add($authorization);

        return $authorization;
    }

    /**
     * @return Orga_Model_Cell
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @return CellAuthorization
     */
    public function getParentAuthorization()
    {
        return $this->parentAuthorization;
    }

    /**
     * @return CellAuthorization[]
     */
    public function getChildAuthorizations()
    {
        return $this->childAuthorizations;
    }
}
