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
     * @Secure("editOrgaCUbe")
     */
    public function getelementsAction()
    {
        $this->request->filter->addCondition(
            Orga_Model_Axis::QUERY_CUBE,
            Orga_Model_Cube::load($this->_getParam('idCube'))
        );

        foreach (Orga_Model_Axis::loadList($this->request) as $axis) {
            $data = array();
            $data['index'] = $axis->getRef();
            $data['identifier'] = $axis->getRef();

            foreach (Zend_Registry::get('languages') as $language) {
                $locale = Core_Locale::load($language);
                $axis->reloadWithLocale($locale);
                $data[$language] = $axis->getLabel();
            }
            $this->addline($data);
        }
        $this->totalElements = Orga_Model_Axis::countTotal($this->request);

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editOrgaCUbe")
     */
    public function updateelementAction()
    {
        $axis = Orga_Model_Axis::loadByRefAndCube($this->update['index'], Orga_Model_Cube::load($this->_getParam('idCube')));
        $axis->setTranslationLocale(Core_Locale::load($this->update['column']));
        $axis->setLabel($this->update['value']);
        $this->data = $axis->getLabel();

        $this->send(true);
    }
}