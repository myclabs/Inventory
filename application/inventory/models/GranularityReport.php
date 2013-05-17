<?php
/**
 * @package Inventory
 * @subpackage ModelProvider
 */

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Classe faisant le lien entre les rapport des granularités et des cellules, et gérant les ressources des Report.
 * @author valentin.claras
 * @package Inventory
 * @subpackage ModelProvider
 */
class Inventory_Model_GranularityReport extends Core_Model_Entity implements Core_Event_ObserverInterface
{
    /**
     * Identifiant unique du GranularityDataProvider.
     * @var int
     */
    protected $id = null;

    /**
     * Granularité concernée.
     *
     * @var Inventory_Model_GranularityDataProvider
     */
    protected $granularityDataProvider = null;

    /**
     * Report du Cube de DW concerné.
     *
     * @var DW_Model_Report
     */
    protected $granularityDataProviderDWReport = null;

    /**
     * Collection des CellsGroupDataProvider utilisant ce CellDataProvider.
     *
     * @var Collection
     */
    private $cellDataProviderDWReports = null;


    /**
     * Constructeur de la classe GranularityReport.
     */
    public function __construct()
    {
        $this->cellDataProviderDWReports = new ArrayCollection();
    }

    /**
     * Utilisé quand un événement est lancé.
     *
     * @param string            $event
     * @param Core_Model_Entity $subject
     * @param array                $arguments
     */
    public static function applyEvent($event, $subject, $arguments = array())
    {
        switch ($event) {
            case DW_Model_Report::EVENT_SAVE:
                try {
                    $granularityDataProvider = Inventory_Model_GranularityDataProvider::loadByDWCube($subject->getCube());
                    $granularityReport = new Inventory_Model_GranularityReport();
                    $granularityReport->setGranularityDataProvider($granularityDataProvider);
                    $granularityReport->setGranularityDataProviderDWReport($subject);
                    $granularityReport->save();
                    Inventory_Service_ETLStructure::getInstance()->createCellsReportFromGranularityReport($granularityReport);
                    Inventory_Service_ACLManager::getInstance()->addGranularityReportViewAuthorization($granularityReport);
                } catch (Core_Exception_NotFound $e) {
                    // Le Report n'est pas issue d'un Cube de GranularityDataProvider.
                }
                Inventory_Service_ACLManager::getInstance()->createReportResource($subject);
                break;
            case DW_Model_Report::EVENT_UPDATED:
                try {
                    $granularityReport = Inventory_Model_GranularityReport::loadByGranularityDataProviderDWReport($subject);
                    Inventory_Service_ETLStructure::getInstance()->updateCellsReportFromGranularityReport($granularityReport);
                } catch (Core_Exception_NotFound $e) {
                    // Le Report n'est pas issue d'un Cube de DW de GranularityDataProvider.
                }
                break;
            case DW_Model_Report::EVENT_DELETE:
                try {
                    $granularityReport = Inventory_Model_GranularityReport::loadByGranularityDataProviderDWReport($subject);
                    $granularityReport->delete();
                } catch (Core_Exception_NotFound $e) {
                    // Le Report n'est pas issue d'un Cube de GranularityDataProvider.
                }
                Inventory_Service_ACLManager::getInstance()->deleteReportResource($subject);
                break;
        }
    }

    /**
     * Charge le GranularityReport correspondant à un GranularityDataProvider.
     *
     * @param Inventory_Model_GranularityDataProvider $granularityDataProvider
     *
     * @return Inventory_Model_GranularityReport
     */
    public static function loadByGranularityDataProvider($granularityDataProvider)
    {
        return self::getEntityRepository()->loadBy(array('granularityDataProvider' => $granularityDataProvider));
    }

    /**
     * Charge le GranularityReport correspondant à un Report de DW.
     * 
     * @param DW_Model_Report $dWReport
     *
     * @return Inventory_Model_GranularityReport
     */
    public static function loadByGranularityDataProviderDWReport($dWReport)
    {
        return self::getEntityRepository()->loadBy(array('granularityDataProviderDWReport' => $dWReport));
    }

    /**
     * Spécifie le GranularityDataProvider utilisant ce Granularity.
     *
     * @param Inventory_Model_GranularityDataProvider $granularityDataProvider
     */
    public function setGranularityDataProvider($granularityDataProvider)
    {
        if ($this->granularityDataProvider !== $granularityDataProvider) {
            if ($this->granularityDataProvider !== null) {
                throw new Core_Exception_Duplicate(
                    'Impossible de redéfinir le GranularityDataProvider, il a déjà été défini'
                );
            }
            $this->granularityDataProvider = $granularityDataProvider;
            $granularityDataProvider->addGranularityReport($this);
        }
    }

    /**
     * Renvoie le GranularityDataProvider.
     *
     * @return Inventory_Model_GranularityDataProvider
     */
    public function getGranularityDataProvider()
    {
        if ($this->granularityDataProvider === null) {
            throw new Core_Exception_UndefinedAttribute("Le GranularityDataProvider n'a pas été défini");
        }
        return $this->granularityDataProvider;
    }

    /**
     * Spécifie le Report de DW.
     * 
     * @param DW_Model_Report $dWReport
     */
    public function setGranularityDataProviderDWReport(DW_Model_Report $dWReport)
    {
        if ($this->granularityDataProviderDWReport !== $dWReport) {
            if ($this->granularityDataProviderDWReport !== null) {
                throw new Core_Exception_Duplicate(
                    'Impossible de redéfinir le Report de DW pour le GranularityDataProvider, il a déjà été défini'
                );
            }
            $this->granularityDataProviderDWReport = $dWReport;
        }
    }

    /**
     * Renvoie le Report de DW.
     *
     * @return DW_Model_Report
     */
    public function getGranularityDataProviderDWReport()
    {
        if ($this->granularityDataProviderDWReport === null) {
            throw new Core_Exception_UndefinedAttribute(
                "Le Report de DW pour le GranularityDataProvider n'a pas été défini"
            );
        }
        return $this->granularityDataProviderDWReport;
    }

    /**
     * Ajoute un Report de DW pour un CellDataProvider au GranularityReport.
     *
     * @param DW_Model_Report $cellDataProviderDWReport
     */
    public function addCellDataProviderDWReport(DW_Model_Report $cellDataProviderDWReport)
    {
        if (!($this->hasCellDataProviderDWReport($cellDataProviderDWReport))) {
            $this->cellDataProviderDWReports->add($cellDataProviderDWReport);
        }
    }

    /**
     * Vérifie si le GranularityReport possède le Report de DW pour un CellDataProvider donné.
     *
     * @param DW_Model_Report $cellDataProviderDWReport
     *
     * @return boolean
     */
    public function hasCellDataProviderDWReport(DW_Model_Report $cellDataProviderDWReport)
    {
        return $this->cellDataProviderDWReports->contains($cellDataProviderDWReport);
    }

    /**
     * Retire le Report de DW pour un CellDataProvider donné des Report de DW de CellDataProvider du GranularityReport.
     *
     * @param DW_Model_Report $cellDataProviderDWReport
     */
    public function removeCellDataProviderDWReport($cellDataProviderDWReport)
    {
        if ($this->hasCellDataProviderDWReport($cellDataProviderDWReport)) {
            $this->cellDataProviderDWReports->removeElement($cellDataProviderDWReport);
        }
    }

    /**
     * Vérifie que le GranularityReport possède au moins un Report de DW de CellDataProvider.
     *
     * @return bool
     */
    public function hasCellDataProviderDWReports()
    {
        return !$this->cellDataProviderDWReports->isEmpty();
    }

    /**
     * Renvoie un tableau des Report de DW de CellDataProvider du GranularityDataprovider.
     *
     * @return DW_Model_Report[]
     */
    public function getCellDataProviderDWReports()
    {
        return $this->cellDataProviderDWReports->toArray();
    }

}