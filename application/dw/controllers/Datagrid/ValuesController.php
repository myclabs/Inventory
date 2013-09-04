<?php
/**
 * @author cyril.perraud
 * @package    DW
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * @package    DW
 * @subpackage Controller
 */
class DW_Datagrid_ValuesController extends UI_Controller_Datagrid
{
    /**
     * Fonction renvoyant la liste des éléments peuplant la Datagrid.
     * @Secure("viewReport")
     */
    public function getelementsAction()
    {
        $locale = Core_Locale::loadDefault();

        $hash = $this->getParam('hashReport');
        $configuration = Zend_Registry::get('configuration');
        $sessionName = $configuration->sessionStorage->name.'_'.APPLICATION_ENV;
        $zendSessionReport = new Zend_Session_Namespace($sessionName);

        $report = DW_Model_Report::getFromString($zendSessionReport->$hash);

        foreach ($report->getValues() as $value) {
            $data = array();
            foreach ($value['members'] as $member) {
                $data['valueAxis'.$member->getAxis()->getRef()] = $member->getLabel();
            }
            $data['valueDigital'] = $locale->formatNumber($value['value'], 3);
            $data['valueUncertainty'] = $value['uncertainty'];
            $this->addLine($data);
        }

        $this->entityManager->clear();

        $this->send();
    }
}