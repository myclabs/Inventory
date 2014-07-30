<?php
/**
 * Classe Orga_Datagrid_Translate_AxesController
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use Orga\Domain\Axis;
use Orga\Domain\Workspace;

/**
 * Classe du controller du datagrid des traductions des axes.
 * @package Orga
 * @subpackage Controller
 */
class Orga_Datagrid_Translate_AxesController extends UI_Controller_Datagrid
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
            Axis::QUERY_WORKSPACE,
            Workspace::load($this->getParam('workspace'))
        );

        foreach (Axis::loadList($this->request) as $axis) {
            $data = array();
            $data['index'] = $axis->getRef();
            $data['identifier'] = $axis->getRef();

            foreach ($this->languages as $language) {
                $data[$language] = $axis->getLabel()->get($language);
            }
            $this->addline($data);
        }
        $this->totalElements = Axis::countTotal($this->request);

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editWorkspace")
     */
    public function updateelementAction()
    {
        $axis = Workspace::load($this->getParam('workspace'))->getAxisByRef($this->update['index']);
        $axis->getLabel()->set($this->update['value'], $this->update['column']);
        $this->data = $axis->getLabel()->get($this->update['column']);

        $this->send(true);
    }
}
