<?php
/**
 * Classe Orga_Datagrid_Translate_AxesController
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */

use Core\Annotation\Secure;

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
     * @Secure("editOrganization")
     */
    public function getelementsAction()
    {
        $this->request->filter->addCondition(
            Orga_Model_Axis::QUERY_ORGANIZATION,
            Orga_Model_Organization::load($this->getParam('idOrganization'))
        );

        foreach (Orga_Model_Axis::loadList($this->request) as $axis) {
            $data = array();
            $data['index'] = $axis->getRef();
            $data['identifier'] = $axis->getRef();

            foreach ($this->languages as $language) {
                $data[$language] = $axis->getLabel()->get($language);
            }
            $this->addline($data);
        }
        $this->totalElements = Orga_Model_Axis::countTotal($this->request);

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editOrganization")
     */
    public function updateelementAction()
    {
        $axis = Orga_Model_Organization::load($this->getParam('idOrganization'))->getAxisByRef($this->update['index']);
        $axis->getLabel()->set($this->update['value'], $this->update['column']);
        $this->data = $axis->getLabel()->get($this->update['column']);

        $this->send(true);
    }
}
