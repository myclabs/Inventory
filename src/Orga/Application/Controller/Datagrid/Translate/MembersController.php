<?php

use Core\Annotation\Secure;
use Orga\Domain\Member;
use Orga\Domain\Workspace;

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
     * @Secure("editWorkspace")
     */
    public function getelementsAction()
    {
        $this->request->filter->addCondition(
            Member::QUERY_AXIS,
            Workspace::load($this->getParam('workspace'))->getAxisByRef(
                $this->getParam('axis')
            )
        );

        foreach (Member::loadList($this->request) as $member) {
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
        $this->totalElements = Member::countTotal($this->request);

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editWorkspace")
     */
    public function updateelementAction()
    {
        $workspace = Workspace::load($this->getParam('workspace'));
        $axis = $workspace->getAxisByRef($this->getParam('axis'));
        $member = $axis->getMemberByCompleteRef($this->update['index']);
        $member->getLabel()->set($this->update['value'], $this->update['column']);

        $this->data = $member->getLabel()->get($this->update['column']);
        $this->send(true);
    }
}
