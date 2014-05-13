<?php

use Core\Annotation\Secure;

class Orga_Datagrid_Translate_GranularityreportsController extends UI_Controller_Datagrid
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
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));
        $this->request->filter->addCondition(
            DW_Model_Report::QUERY_CUBE,
            $organization->getGranularityByRef($this->getParam('refGranularity'))->getDWCube()
        );

        foreach (DW_Model_Report::loadList($this->request) as $report) {
            $data = array();
            $data['index'] = $report->getKey()['id'];
            $data['identifier'] = $report->getKey()['id'];

            foreach ($this->languages as $language) {
                $data[$language] = $report->getLabel()->get($language);
            }
            $this->addline($data);
        }
        $this->totalElements = DW_Model_Report::countTotal($this->request);

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editOrganization")
     */
    public function updateelementAction()
    {
        $report = DW_Model_Report::load($this->update['index']);
        $report->getLabel()->set($this->update['value'], $this->update['column']);
        $this->data = $report->getLabel()->get($this->update['column']);

        $this->send(true);
    }
}
