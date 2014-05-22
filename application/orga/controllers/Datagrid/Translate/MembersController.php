<?php

use Core\Annotation\Secure;

class Orga_Datagrid_Translate_MembersController extends UI_Controller_Datagrid
{
    /**
     * @Inject("translation.languages")
     * @var string[]
     */
    private $languages;

    /**
     * Fonction renvoyant la liste des éléments peuplant la Datagrid.
     *
     * @Secure("editOrganization")
     */
    public function getelementsAction()
    {
        $this->request->filter->addCondition(
            Orga_Model_Member::QUERY_AXIS,
            Orga_Model_Organization::load($this->getParam('idOrganization'))->getAxisByRef(
                $this->getParam('refAxis')
            )
        );

        foreach (Orga_Model_Member::loadList($this->request) as $member) {
            $data = array();
            $data['index'] = $member->getCompleteRef();
            $data['identifier'] = $member->getAxis()->getRef().' | '.$member->getRef();
            $parentMembersHshKey = $member->getParentMembersHashKey();
            if (!empty($parentMembersHshKey)) {
                $data['identifier'] .= ' ('. $parentMembersHshKey .')';
            }

            foreach ($this->languages as $language) {
                $data[$language] = $member->getLabel()->get($language);
            }
            $this->addline($data);
        }
        $this->totalElements = Orga_Model_Member::countTotal($this->request);

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editOrganization")
     */
    public function updateelementAction()
    {
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));
        $axis = $organization->getAxisByRef($this->getParam('refAxis'));
        $member = $axis->getMemberByCompleteRef($this->update['index']);
        $member->getLabel()->set($this->update['value'], $this->update['column']);

        $this->data = $member->getLabel()->get($this->update['column']);
        $this->send(true);
    }
}
