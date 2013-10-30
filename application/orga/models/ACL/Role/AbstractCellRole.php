<?php

namespace Orga\Model\ACL\Role;

use Orga\Model\ACL\OrganizationAuthorization;
use Orga_Model_Cell;
use User\Domain\ACL\Action;
use User\Domain\ACL\Role;
use User\Domain\User;

/**
 * Classe abstraite pour factoriser du code.
 */
abstract class AbstractCellRole extends Role
{
    /**
     * @var Orga_Model_Cell
     */
    protected $cell;

    public function __construct(User $user, Orga_Model_Cell $cell)
    {
        $this->user = $user;
        $this->cell = $cell;
    }

    public function getAuthorizations()
    {
        $authorizations = [];

        // Voir l'organisation
        $authorizations[] = new OrganizationAuthorization($this->user, Action::VIEW(), $this->cell->getOrganization());

        $authorizations = array_merge($authorizations, $this->getCellAuthorizations($this->cell));

        // Cellules filles
        foreach ($this->cell->getChildCells() as $childCell) {
            $authorizations = array_merge($authorizations, $this->getCellAuthorizations($childCell));
        }

        return $authorizations;
    }

    /**
     * Retourne les autorisations pour les cellules concernées par ce role.
     *
     * @param Orga_Model_Cell $cell
     * @return \User\Domain\ACL\Authorization\Authorization[]
     */
    abstract protected function getCellAuthorizations(Orga_Model_Cell $cell);
}
