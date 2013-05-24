<?php
/**
 * Classe Orga_Datagrid_Translate_MembersController
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Classe du controller du datagrid des traductions des members.
 * @package Orga
 * @subpackage Controller
 */
class Orga_Datagrid_Translate_MembersController extends UI_Controller_Datagrid
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
     * @Secure("editOrgaCube")
     */
    public function getelementsAction()
    {
        $this->request->filter->addCondition(
            Orga_Model_Member::QUERY_AXIS,
            Orga_Model_Axis::loadByRefAndCube($this->_getParam('refAxis'), Orga_Model_Cube::load($this->_getParam('idCube')))
        );

        foreach (Orga_Model_Member::loadList($this->request) as $member) {
            $data = array();
            $data['index'] = $member->getCompleteRef();
            $data['identifier'] = $member->getCompleteRef();

            foreach (Zend_Registry::get('languages') as $language) {
                $locale = Core_Locale::load($language);
                $member->reloadWithLocale($locale);
                $data[$language] = $member->getLabel();
            }
            $this->addline($data);
        }
        $this->totalElements = Orga_Model_Member::countTotal($this->request);

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editOrgaCube")
     */
    public function updateelementAction()
    {
        Core_Tools::dump(Orga_Model_Cube::load($this->_getParam('idCube')));
        Core_Tools::dump(Orga_Model_Axis::loadByRefAndCube($this->_getParam('refAxis'), Orga_Model_Cube::load($this->_getParam('idCube'))));
        $member = Orga_Model_Member::loadByCompleteRefAndAxis(
            $this->update['index'],
            Orga_Model_Axis::loadByRefAndCube($this->_getParam('refAxis'), Orga_Model_Cube::load($this->_getParam('idCube')))
        );
        $member->setTranslationLocale(Core_Locale::load($this->update['column']));
        $member->setLabel($this->update['value']);
        $this->data = $member->getLabel();

        $this->send(true);
    }
}