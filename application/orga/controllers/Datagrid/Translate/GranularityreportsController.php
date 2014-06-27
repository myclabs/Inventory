<?php

use Core\Annotation\Secure;
use DW\Domain\Report;

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
            Report::QUERY_CUBE,
            $organization->getGranularityByRef($this->getParam('refGranularity'))->getDWCube()
        );

        foreach (Report::loadList($this->request) as $report) {
            $data = array();
            $data['index'] = $report->getKey()['id'];
            $data['identifier'] = $report->getKey()['id'];

            foreach ($this->languages as $language) {
                $data[$language] = $report->getLabel()->get($language);
            }
            $this->addline($data);
        }
        $this->totalElements = Report::countTotal($this->request);

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editOrganization")
     */
    public function updateelementAction()
    {
        $report = Report::load($this->update['index']);
        $report->getLabel()->set($this->update['value'], $this->update['column']);
        $this->data = $report->getLabel()->get($this->update['column']);

        $this->send(true);
    }
}
