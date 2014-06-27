<?php
/**
 * @author cyril.perraud
 * @package    DW
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use DW\Application\Service\ReportService;
use DW\Domain\Report;

/**
 * @package    DW
 * @subpackage Controller
 */
class DW_Datagrid_ValuesController extends UI_Controller_Datagrid
{
    /**
     * @Inject("session.storage.name")
     */
    private $sessionStorageName;

    /**
     * @Inject("DW\Application\Service\ReportService")
     * @var ReportService
     */
    private $reportService;

    /**
     * Fonction renvoyant la liste des éléments peuplant la Datagrid.
     * @Secure("viewReport")
     */
    public function getelementsAction()
    {
        $locale = Core_Locale::loadDefault();

        $hash = $this->getParam('hashReport');
        $zendSessionReport = new Zend_Session_Namespace($this->sessionStorageName);

        $report = $this->reportService->getReportFromJson($zendSessionReport->$hash);

        foreach ($report->getValues() as $value) {
            $data = [];
            foreach ($value['members'] as $member) {
                $data['valueAxis'.$member->getAxis()->getRef()] = $this->translator->get($member->getLabel());
            }
            $data['valueDigital'] = $locale->formatNumber($value['value'], 3);
            $data['valueUncertainty'] = $value['uncertainty'];
            $this->addLine($data);
        }

        $this->entityManager->clear();

        $this->send();
    }
}
