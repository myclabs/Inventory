<?php
/**
 * Classe Orga_Datagrid_Translate_GranularityreportsController
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Classe du controller du datagrid des traductions des Reports de DW issus des Granularity.
 * @package Orga
 * @subpackage Controller
 */
class Orga_Datagrid_Translate_GranularityreportsController extends UI_Controller_Datagrid
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
     * @Secure("editOrganization")
     */
    public function getelementsAction()
    {
        $this->request->filter->addCondition(
            DW_Model_Report::QUERY_CUBE,
            Orga_Model_Granularity::loadByRefAndOrganization(
                $this->getParam('refGranularity'),
                Orga_Model_Organization::load($this->getParam('idOrganization'))
            )->getDWCube()
        );

        foreach (DW_Model_Report::loadList($this->request) as $report) {
            $data = array();
            $data['index'] = $report->getKey()['id'];
            $data['identifier'] = $report->getKey()['id'];

            foreach (Zend_Registry::get('languages') as $language) {
                $locale = Core_Locale::load($language);
                $report->reloadWithLocale($locale);
                $data[$language] = $report->getLabel();
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
        $report->reloadWithLocale(Core_Locale::load($this->update['column']));
        $report->setLabel($this->update['value']);
        $this->data = $report->getLabel();

        $this->send(true);
    }
}