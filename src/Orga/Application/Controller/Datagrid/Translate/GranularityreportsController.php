<?php

use Core\Annotation\Secure;
use DW\Domain\Report;
use Orga\Domain\Workspace;

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
     * @Secure("editWorkspace")
     */
    public function getelementsAction()
    {
        $workspace = Workspace::load($this->getParam('workspace'));
        $this->request->filter->addCondition(
            Report::QUERY_CUBE,
            $workspace->getGranularityByRef($this->getParam('granularity'))->getDWCube()
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
     * @Secure("editWorkspace")
     */
    public function updateelementAction()
    {
        $report = Report::load($this->update['index']);
        $report->getLabel()->set($this->update['value'], $this->update['column']);
        $this->data = $report->getLabel()->get($this->update['column']);

        $this->send(true);
    }
}
