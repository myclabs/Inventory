<?php

namespace Orga;

use Mnapoli\Translated\Translator;
use MyCLabs\ACL\ACL;
use Orga\ViewModel\GranularityView;
use Orga\ViewModel\OrganizationView;
use User\Domain\ACL\Actions;
use Orga_Model_Organization;
use Orga_Model_Cell;
use Orga_Service_ACLManager;
use User\Domain\User;
use Core_Model_Query;

/**
 * Crée des représentations simplifiées de la vue d'une organisation pour un utilisateur.
 *
 * @author matthieu.napoli
 */
class OrganizationViewFactory
{
    /**
     * @var ACL
     */
    private $acl;

    /**
     * @var Orga_Service_ACLManager
     */
    private $orgaACLManager;

    /**
     * @var Translator
     */
    private $translator;

    public function __construct(ACL $acl, Orga_Service_ACLManager $orgaACLManager, Translator $translator)
    {
        $this->acl = $acl;
        $this->orgaACLManager = $orgaACLManager;
        $this->translator = $translator;
    }

    public function createOrganizationView(Orga_Model_Organization $organization, User $connectedUser)
    {
        $organizationView = new OrganizationView();
        $organizationView->id = $organization->getId();
        $organizationView->label = $this->translator->get($organization->getLabel());
        if ($organizationView->label == '') {
            $organizationView->label = __('Orga', 'navigation', 'defaultOrganizationLabel');
        }

        // Vérification d'accèr à l'édition.
        $organizationView->canBeEdited = $this->acl->isAllowed(
            $connectedUser,
            Actions::EDIT,
            $organization
        );

        // TODO à simplifier tout ça
        if (!$organizationView->canBeEdited) {
            // Edition de la cellule globale ?
            $query = new Core_Model_Query();
            $query->filter->addCondition(Orga_Model_Cell::QUERY_GRANULARITY, $organization->getGranularityByRef('global'));
            $query->aclFilter->enabled = true;
            $query->aclFilter->user = $connectedUser;
            $query->aclFilter->action = Actions::EDIT;
            if (Orga_Model_Cell::countTotal($query) > 0) {
                $organizationView->canBeEdited = true;
            }
        }
        if (!$organizationView->canBeEdited) {
            // Edition d'au moins un axe ?
            $axesCanEdit = $this->orgaACLManager->getAxesCanEdit($connectedUser, $organization);
            if (count($axesCanEdit) > 0) {
                $organizationView->canBeEdited = true;
            }
        }
        if (!$organizationView->canBeEdited) {
            // Edition d'au moins une granularité de pertinence ou de DW ?
            foreach ($this->orgaACLManager->getGranularitiesCanEdit($connectedUser, $organization) as $granularity) {
                if ($granularity->getCellsControlRelevance() || $granularity->getCellsGenerateDWCubes()) {
                    $organizationView->canBeEdited = true;
                    break;
                }
            }
        }

        // Granularités
        foreach ($organization->getGranularities() as $granularity) {
            $granularityView = new GranularityView();
            $granularityView->id = $granularity->getId();
            $granularityView->label = $this->translator->get($granularity->getLabel());

            $organizationView->granularities[] = $granularityView;
        }

        // TODO faire les cellules ?

        return $organizationView;
    }
}
