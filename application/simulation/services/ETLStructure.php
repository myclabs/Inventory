<?php
/**
 * @package Simulation
 * @subpackage Service
 */

use Classification\Domain\AxisMember;
use Classification\Domain\Axis;
use Classification\Domain\Indicator;
use Doctrine\ORM\EntityManager;

/**
 * Classe permettant de construire DW
 * @author valentin.claras
 * @package Simulation
 * @subpackage Service
 */
class Simulation_Service_ETLStructure
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Simulation_Service_ETLData
     */
    private $etlDataService;

    /**
     * @param Simulation_Service_ETLData $etlDataService
     * @param EntityManager              $entityManager
     */
    public function __construct(Simulation_Service_ETLData $etlDataService, EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->etlDataService = $etlDataService;
    }


    /**
     * Traduit les labels des objets originaux dans DW.
     *
     * @param Indicator|Axis|AxisMember|Orga_Model_Axis|Orga_Model_Member $originalEntity
     * @param DW_Model_Indicator|DW_Model_Axis|DW_Model_Member $dWEntity
     */
    protected function translateEntity($originalEntity, $dWEntity)
    {
        // TODO utiliser l'injection de dépendances
        $container = \Core\ContainerSingleton::getContainer();
        /** @var $translationRepository \Gedmo\Translatable\Entity\Repository\TranslationRepository */
        $translationRepository = $this->entityManager->getRepository('Gedmo\Translatable\Entity\Translation');
        $defaultLocale = $container->get('translation.defaultLocale');

        $originalTranslations = $translationRepository->findTranslations($originalEntity);

        if (isset($originalTranslations[$defaultLocale])) {
            $dWEntity->setLabel($originalTranslations[$defaultLocale]);
        } else {
            $dWEntity->setLabel($originalEntity->getLabel());
        }
        // Traductions.
        foreach ($container->get('translation.languages') as $localeId) {
            if (isset($originalTranslations[$localeId]['label'])) {
                $translationRepository->translate(
                    $dWEntity,
                    'label',
                    $localeId,
                    $originalTranslations[$localeId]['label']
                );
            }
        }
    }

    /**
     * Vérifie que les traductions sont à jour entre les objets originaux et ceux de DW.
     *
     * @param Indicator|Axis|AxisMember|Orga_Model_Axis|Orga_Model_Member $originalEntity
     * @param DW_Model_Indicator|DW_Model_Axis|DW_Model_Member $dWEntity
     *
     * @return bool
     */
    protected function areTranslationsDifferent($originalEntity, $dWEntity)
    {
        // TODO utiliser l'injection de dépendances
        $container = \Core\ContainerSingleton::getContainer();
        /** @var $translationRepository \Gedmo\Translatable\Entity\Repository\TranslationRepository */
        $translationRepository = $this->entityManager->getRepository('Gedmo\Translatable\Entity\Translation');

        $originalTranslations = $translationRepository->findTranslations($originalEntity);
        $dWTranslations = $translationRepository->findTranslations($dWEntity);

        // Traductions
        foreach ($container->get('translation.languages') as $localeId) {
            if (isset($originalTranslations[$localeId])) {
                $originalLabel = $originalTranslations[$localeId]['label'];
            } else {
                $originalLabel = '';
            }
            if (isset($dWTranslations[$localeId])) {
                $dWLabel = $dWTranslations[$localeId]['label'];
            } else {
                $dWLabel = '';
            }

            if($originalLabel != $dWLabel) {
                return true;
            }
        }

        return false;
    }


    /**
     * Peuple le cube de DW avec les données issues de Classification.
     *
     * @param Simulation_Model_Set $set
     */
    public function populateSetDWCube(Simulation_Model_Set $set)
    {
        $this->populateDWCubeWithClassif($set->getDWCube());
    }

    /**
     * Peuple le cube de DW avec les données issues de Classification.
     *
     * @param DW_Model_Cube $dWCube
     */
    public function populateDWCubeWithClassif($dWCube)
    {
        $queryOrdered = new Core_Model_Query();
        $queryOrdered->order->addOrder(Indicator::QUERY_POSITION);
        foreach (Indicator::loadList($queryOrdered) as $classifIndicator) {
            /** @var Indicator $classifIndicator */
            $this->copyIndicatorFromClassifToDWCube($classifIndicator, $dWCube);
        }

        $queryRootAxes = new Core_Model_Query();
        $queryRootAxes->filter->addCondition(
            Axis::QUERY_NARROWER,
            null,
            Core_Model_Filter::OPERATOR_NULL
        );
        foreach (Axis::loadList($queryRootAxes) as $classifAxis) {
            /** @var Axis $classifAxis */
            $this->copyAxisAndMembersFromClassifToDW($classifAxis, $dWCube);
        }
    }

    /**
     * Copie un indicateur de Classification dans un cube de DW.
     *
     * @param Indicator $classifIndicator
     * @param DW_Model_Cube $dWCube
     */
    private function copyIndicatorFromClassifToDWCube($classifIndicator, $dWCube)
    {
        $dWIndicator = new DW_Model_Indicator($dWCube);
        $dWIndicator->setRef($classifIndicator->getRef());
        $dWIndicator->setUnit($classifIndicator->getUnit());
        $dWIndicator->setRatioUnit($classifIndicator->getRatioUnit());
        $this->translateEntity($classifIndicator, $dWIndicator);
    }

    /**
     * Copie un axe de Classification dans un cube DW.
     *
     * @param Axis $classifAxis
     * @param DW_Model_Cube $dwCube
     * @param array &$associationArray
     */
    private function copyAxisAndMembersFromClassifToDW($classifAxis, $dwCube, & $associationArray=array())
    {
        $dWAxis = new DW_Model_Axis($dwCube);
        $dWAxis->setRef($classifAxis->getRef());
        $this->translateEntity($classifAxis, $dWAxis);

        $associationArray['axes'][$classifAxis->getRef()] = $dWAxis;
        $narrowerAxis = $classifAxis->getDirectNarrower();
        if ($narrowerAxis !== null) {
            $dWAxis->setDirectNarrower($associationArray['axes'][$narrowerAxis->getRef()]);
        }

        foreach ($classifAxis->getMembers() as $classifMember) {
            $dWMember = new DW_Model_Member($dWAxis);
            $dWMember->setRef($classifMember->getRef());
            $dWMember->setPosition($classifMember->getPosition());
            $this->translateEntity($classifMember, $dWMember);

            $memberIdentifier = $classifMember->getAxis()->getRef().'_'.$classifMember->getRef();
            $associationArray['members'][$memberIdentifier] = $dWMember;
            foreach ($classifMember->getDirectChildren() as $narrowerClassifMember) {
                $narrowerIdentifier = $narrowerClassifMember->getAxis()->getRef().'_'.$narrowerClassifMember->getRef();
                $dWMember->addDirectChild($associationArray['members'][$narrowerIdentifier]);
            }
        }

        foreach ($classifAxis->getDirectBroaders() as $broaderClassifAxis) {
            $this->copyAxisAndMembersFromClassifToDW($broaderClassifAxis, $dwCube, $associationArray);
        }
    }

    /**
     * Indique si un cube de DW donné est à jour vis à vis de données de Classification.
     *
     * @param Simulation_Model_Set $set
     *
     * @return bool
     */
    public function isSetDWCubeUpToDate($set)
    {
        return $this->isDWCubeUpToDate($set->getDWCube());
    }


    /**
     * Indique les différences entre un cube de DW donné el les données de Classification.
     *
     * @param DW_Model_Cube $dWCube
     *
     * @return bool
     */
    private function isDWCubeUpToDate($dWCube)
    {
        return $this->areDWIndicatorsUpToDate($dWCube) && $this->areDWAxesUpToDate($dWCube);
    }

    /**
     * Compare les différences entre une liste d'indicateurs de DW et ceux de Classification.
     *
     * @param DW_Model_Cube $dWCube
     *
     * @return bool
     */
    private function areDWIndicatorsUpToDate($dWCube)
    {
        $classifIndicators = Indicator::loadList();
        $dWIndicators = $dWCube->getIndicators();

        foreach (Indicator::loadList() as $classifIndex => $classifIndicator) {
            /** @var Indicator $classifIndicator */
            foreach ($dWCube->getIndicators() as $dWIndex => $dWIndicator) {
                if (!($this->isDWIndicatorDifferentFromClassif($dWIndicator, $classifIndicator))) {
                    unset($classifIndicators[$classifIndex]);
                    unset($dWIndicators[$dWIndex]);
                }
            }
        }

        if ((count($classifIndicators) > 0) || (count($dWIndicators) > 0)) {
            return true;
        }

        return false;
    }

    /**
     * Compare les différences entre une liste d'indicateurs de DW et ceux de Classification.
     *
     * @param DW_Model_Indicator $dWIndicator
     * @param Indicator $classifIndicator
     *
     * @return bool
     */
    private function isDWIndicatorDifferentFromClassif($dWIndicator, $classifIndicator)
    {
        if (($classifIndicator->getRef() !== $dWIndicator->getRef())
            || ($classifIndicator->getUnit()->getRef() !== $dWIndicator->getUnit()->getRef())
            || ($classifIndicator->getRatioUnit()->getRef() !== $dWIndicator->getRatioUnit()->getRef())
            || ($this->areTranslationsDifferent($classifIndicator, $dWIndicator))
        ) {
            return true;
        }

        return false;
    }

    /**
     * Compare les différences entre une liste d'indicateurs de DW et ceux de Classification.
     *
     * @param DW_Model_Cube $dWCube
     *
     * @return bool
     */
    private function areDWAxesUpToDate($dWCube)
    {
        $queryClassifRootAxes = new Core_Model_Query();
        $queryClassifRootAxes->filter->addCondition(
            Axis::QUERY_NARROWER,
            null,
            Core_Model_Filter::OPERATOR_NULL
        );
        $classifRootAxes = Axis::loadList($queryClassifRootAxes);
        $dWRootAxes = $dWCube->getRootAxes();

        foreach ($dWCube->getRootAxes() as $dWIndex => $dWAxis) {
            if ($dWAxis->getRef() !== 'set') {
                foreach (Axis::loadList($queryClassifRootAxes) as $classifIndex => $classifAxis) {
                    if (!($this->isDWAxisDifferentFromClassif($dWAxis, $classifAxis))) {
                        unset($classifRootAxes[$classifIndex]);
                        unset($dWRootAxes[$dWIndex]);
                    }
                }
            }
        }

        if ((count($classifRootAxes) > 0) || (count($dWRootAxes) > 1)) {
            return true;
        }

        return false;
    }

    /**
     * Compare un axe de DW et un de Classification.
     *
     * @param DW_Model_Axis $dWAxis
     * @param Axis $classifAxis
     *
     * @return bool
     */
    private function isDWAxisDifferentFromClassif($dWAxis, $classifAxis)
    {
        if (($classifAxis->getRef() !== $dWAxis->getRef())
            || ((($classifAxis->getDirectNarrower() !== null) || ($dWAxis->getDirectNarrower() !== null))
                && (($classifAxis->getDirectNarrower() === null) || ($dWAxis->getDirectNarrower() === null)
                    || ($classifAxis->getDirectNarrower()->getRef() !== $dWAxis->getDirectNarrower()->getRef())))
            || ($this->areTranslationsDifferent($classifAxis, $dWAxis))
            || ($this->areDWMembersDifferentFromClassif($dWAxis, $classifAxis))
        ) {
            return true;
        } else {
            $classifAxisBroaders = $classifAxis->getDirectBroaders();
            $dWAxisBroaders = $dWAxis->getDirectBroaders();

            foreach ($dWAxis->getDirectBroaders() as $dWIndex => $dWBroaderAxis) {
                foreach ($classifAxis->getDirectBroaders() as $classifIndex => $classifBroaderAxis) {
                    if (!($this->isDWAxisDifferentFromClassif($dWBroaderAxis, $classifBroaderAxis))) {
                        unset($classifAxisBroaders[$classifIndex]);
                        unset($dWAxisBroaders[$dWIndex]);
                    }
                }
            }

            if ((count($classifAxisBroaders) > 0) || (count($dWAxisBroaders) > 0)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Compare un membre de DW et un de Classification.
     *
     * @param DW_Model_Axis $dWAxis
     * @param Axis $classifAxis
     *
     * @return bool
     */
    private function areDWMembersDifferentFromClassif($dWAxis, $classifAxis)
    {
        $classifMembers = $classifAxis->getMembers();
        $dWMembers = $dWAxis->getMembers();

        foreach ($dWAxis->getMembers() as $dWIndex => $dWMember) {
            foreach ($classifAxis->getMembers() as $classifIndex => $classifMember) {
                if (!($this->isDWMemberDifferentFromClassif($dWMember, $classifMember))) {
                    unset($classifMembers[$classifIndex]);
                    unset($dWMembers[$dWIndex]);
                }
            }
        }

        if ((count($classifMembers) > 0) || (count($dWMembers) > 0)) {
            return true;
        }

        return false;
    }

    /**
     * Compare un membre de DW et un de Classification.
     *
     * @param DW_Model_Member $dWMember
     * @param AxisMember $classifMember
     *
     * @return bool
     */
    private function isDWMemberDifferentFromClassif($dWMember, $classifMember)
    {
        if (($classifMember->getRef() !== $dWMember->getRef())
            || ($this->areTranslationsDifferent($classifMember, $dWMember))
        ) {
            return true;
        } else {
            $classifMemberParents = $classifMember->getDirectParents();
            $dWMemberParents = $dWMember->getDirectParents();

            foreach ($dWMember->getDirectParents() as $dWIndex => $dWParentMember) {
                foreach ($classifMember->getDirectParents() as $classifIndex => $classifParentMember) {
                    if ($classifParentMember->getRef() === $dWParentMember->getRef()) {
                        unset($classifMemberParents[$classifIndex]);
                        unset($dWMemberParents[$dWIndex]);
                    }
                }
            }

            if ((count($classifMemberParents) > 0) || (count($dWMemberParents) > 0)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Réinitialise le cube de DW d'un Set donné.
     *
     * @param Simulation_Model_Set $set
     */
    public function resetSetDWCube($set)
    {
        $scenarios = $set->getScenarios();

        foreach ($scenarios as $scenario) {
            $this->etlDataService->clearDWResultsFromScenario($scenario);
        }

        $this->resetDWCube($set->getDWCube());

        foreach ($scenarios as $scenario) {
            $this->etlDataService->populateDWResultsFromScenario($scenario);
        }
    }

    /**
     * Réinitialise un cube de DW donné.
     *
     * @param DW_Model_Cube $dWCube
     */
    private function resetDWCube($dWCube)
    {
        set_time_limit(0);

        $queryCube = new Core_Model_Query();
        $queryCube->filter->addCondition(DW_Model_Report::QUERY_CUBE, $dWCube);
        // Suppression des résultats.
        foreach (DW_Model_Result::loadList($queryCube) as $dWResult) {
            $dWResult->delete();
        }

        // Préparation à la copie des Reports.
        $dWReportsAsString = array();
        foreach (DW_Model_Report::loadList($queryCube) as $dWReport) {
            /** @var DW_Model_Report $dWReport */
            $dWReportsAsString[] = $dWReport->getAsString();
            $emptyDWReportString = '{'.
                '"id":'.$dWReport->getKey()['id'].',"idCube":'.$dWReport->getCube()->getId().',"label":"",'.
                '"refNumerator":null,"refNumeratorAxis1":null,"refNumeratorAxis2":null,'.
                '"refDenominator":null,"refDenominatorAxis1":null,"refDenominatorAxis2":null,'.
                '"chartType":null,"sortType":"orderResultByDecreasingValue","withUncertainty":false,'.
                '"filters":[]'.
                '}';
            $dWReportReset = DW_Model_Report::getFromString($emptyDWReportString);
            $dWReportReset->save();
        }

        // Suppression des axes et indicateurs.
        foreach ($dWCube->getIndicators() as $dWIndicator) {
            $dWIndicator->delete();
        }
        foreach ($dWCube->getRootAxes() as $dWRootAxis) {
            if ($dWRootAxis->getRef() !== 'set') {
                $dWRootAxis->delete();
            }
        }
        // Suppression des données du cube et vidage des Report.
        $this->entityManager->flush();

        $this->populateDWCubeWithClassif($dWCube);
        $dWCube->save();

        // Peuplement du cube effectif.
        $this->entityManager->flush();

        // Copie des Reports.
        foreach ($dWReportsAsString as $dWReportString) {
            try {
                $newReport = DW_Model_Report::getFromString($dWReportString);
                $newReport->save();
            } catch (Core_Exception_NotFound $e) {
                // Le rapport n'est pas compatible avec la nouvelle version du cube.
            }
        }

        // Copie des rapports.
        $this->entityManager->flush();
    }

}
