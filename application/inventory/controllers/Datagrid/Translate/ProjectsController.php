<?php
/**
 * Classe Inventory_Datagrid_Translate_ProjectsController
 * @author valentin.claras
 * @package Inventory
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Classe du controller du datagrid des traductions des projects.
 * @package Inventory
 * @subpackage Controller
 */
class Inventory_Datagrid_Translate_ProjectsController extends UI_Controller_Datagrid
{
    /**
     * Désativation du fallback des traduction
     */
    public function init()
    {
        parent::init();
        Zend_Registry::get('doctrineTranslate')->setTranslationFallback(false);
    }

    /**
     * Fonction renvoyant la liste des éléments peuplant la Datagrid.
     *
     * @Secure("editProjects")
     */
    public function getelementsAction()
    {
        $this->request->aclFilter->enabled = true;
        $this->request->aclFilter->user = $this->_helper->auth();
        $this->request->aclFilter->action = User_Model_Action_Default::VIEW();

        foreach (Inventory_Model_Project::loadList($this->request) as $project) {
            $data = array();
            $data['index'] = $project->getKey()['id'];
            $data['identifier'] = $project->getKey()['id'];

            foreach (Zend_Registry::get('languages') as $language) {
                $locale = Core_Locale::load($language);
                $project->reloadWithLocale($locale);
                $data[$language] = $project->getLabel();
            }

            $data['axes'] = $this->cellLink('orga/translate/axes/idCube/'.$project->getOrgaCube()->getKey()['id']);
            $data['members'] = $this->cellLink('orga/translate/members/idCube/'.$project->getOrgaCube()->getKey()['id']);
            $this->addline($data);
        }
        $this->totalElements = Inventory_Model_Project::countTotal($this->request);

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editProject")
     */
    public function updateelementAction()
    {
        $project = Inventory_Model_Project::load($this->update['index']);
        $project->setTranslationLocale(Core_Locale::load($this->update['column']));
        $project->setLabel($this->update['value']);
        $this->data = $project->getLabel();

        $this->send(true);
    }
}