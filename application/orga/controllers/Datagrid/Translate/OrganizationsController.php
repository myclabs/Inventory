<?php

use Core\Annotation\Secure;

class Orga_Datagrid_Translate_OrganizationsController extends UI_Controller_Datagrid
{
    /**
     * @Inject("translation.languages")
     * @var string[]
     */
    private $languages;

    /**
     * Fonction renvoyant la liste des éléments peuplant la Datagrid.
     *
     * @Secure("editOrganizations")
     */
    public function getelementsAction()
    {
        $idOrganization = $this->getParam('idOrganization');
        /** @var Orga_Model_Organization $organization */
        $organization = Orga_Model_Organization::load($idOrganization);

        $data = [];
        $data['index'] = $organization->getId();
        $data['identifier'] = $organization->getId();

        foreach ($this->languages as $language) {
            $data[$language] = $organization->getLabel()->get($language);
        }

        $this->addline($data);

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editOrganization")
     */
    public function updateelementAction()
    {
        $organization = Orga_Model_Organization::load($this->update['index']);
        $organization->getLabel()->set($this->update['value'], $this->update['column']);

        $this->data = $organization->getLabel()->get($this->update['column']);
        $this->send(true);
    }
}
