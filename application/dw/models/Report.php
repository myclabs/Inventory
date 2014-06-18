<?php
/**
 * @author valentin.claras
 * @author cyril.perraud
 * @package DW
 * @subpackage Model
 */

use Core\Translation\TranslatedString;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Mnapoli\Translated\Translator;

/**
 * Permet de gérer un report
 *
 * @package DW
 * @subpackage Model
 */
class DW_Model_Report extends Core_Model_Entity
{
    use Core_Event_ObservableTrait;

    // Constantes de tris et de filtres.
    const QUERY_CUBE = 'cube';
    const QUERY_LABEL = 'label';
    // Constantes de tri pour les résultats.
    const SORT_VALUE_INCREASING = 'orderResultByIncreasingValue';
    const SORT_VALUE_DECREASING = 'orderResultByDecreasingValue';
    const SORT_CONVENTIONAL = 'orderResultByMembers';
    // Constantes de tris et de filtres.
    const CHART_PIE = 'pie_chart';
    const CHART_HORIZONTAL = 'horizontal_chart';
    const CHART_VERTICAL = 'vertical_chart';
    const CHART_HORIZONTAL_STACKED = 'horizontally_stacked_chart';
    const CHART_VERTICAL_STACKED = 'vertically_stacked_chart';
    const CHART_HORIZONTAL_GROUPED = 'horizontally_grouped_chart';
    const CHART_VERTICAL_GROUPED = 'vertically_grouped_chart';
    const CHART_HORIZONTAL_STACKEDGROUPED = 'horizontally_stacked_and_grouped_chart';
    const CHART_VERTICAL_STACKEDGROUPED = 'vertically_stacked_and_grouped_chart';
    // Constantes d'événement.
    const EVENT_SAVE = 'dWReportSave';
    const EVENT_UPDATED = 'dWReportUpdated';
    const EVENT_DELETE = 'dWReportDelete';

    /**
     * Identifiant unique du Report.
     *
     * @var int
     */
    protected $id;

    /**
     * Label de l'Indicator.
     *
     * @var TranslatedString
     */
    protected $label;

    /**
     * Cube contenant l'Indicator.
     *
     * @var DW_Model_Cube
     */
    protected $cube;

    /**
     * Type de UI_Chart utilisé dans ce Report.
     *
     * @var string
     */
    protected $chartType;
    protected $chartTypeArray = [
        self::CHART_PIE,
        self::CHART_HORIZONTAL,
        self::CHART_VERTICAL,
        self::CHART_HORIZONTAL_GROUPED,
        self::CHART_VERTICAL_GROUPED,
        self::CHART_HORIZONTAL_STACKED,
        self::CHART_VERTICAL_STACKED,
        self::CHART_HORIZONTAL_STACKEDGROUPED,
        self::CHART_VERTICAL_STACKEDGROUPED,
    ];

    /**
     * Ordre des résultats.
     *
     * @var null
     */
    protected $sortType = self::SORT_VALUE_DECREASING;

    /**
     * Indique si le rapport prend en compte les incertitudes.
     *
     * @var bool
     */
    protected $withUncertainty = false;

    /**
     * Indicator numérateur.
     *
     * @var DW_Model_Indicator
     */
    protected $numerator;

    /**
     * Axis 1 utilisés au numérateur.
     *
     * @var DW_Model_Axis
     */
    protected $numeratorAxis1;

    /**
     * Axis 2 utilisés au numérateur.
     *
     * @var DW_Model_Axis
     */
    protected $numeratorAxis2;

    /**
     * Indicator dénominateur.
     *
     * @var DW_Model_Indicator
     */
    protected $denominator;

    /**
     * Axis 1 utilisés au dénominateur.
     *
     * @var DW_Model_Axis
     */
    protected $denominatorAxis1;

    /**
     * Axis 2 utilisés au dénominateur.
     *
     * @var DW_Model_Axis
     */
    protected $denominatorAxis2;

    /**
     * Ensemble des Filter utilisés sur le Report.
     *
     * @var Collection|DW_Model_Filter[]
     */
    protected $filters;

    /**
     * Timestamp de dernière modification du rapport.
     *
     * @var int
     */
    protected $lastModificationTimestamp = 1;


    public function __construct(DW_Model_Cube $cube)
    {
        $this->label = new TranslatedString();
        $this->filters = new ArrayCollection();

        $this->cube = $cube;
        $this->cube->addReport($this);
        $this->updateLastModification();
    }

    /**
     * Clone le Report.
     *
     * @return DW_Model_Report
     */
    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
        }
    }

    /**
     * Fonction appelée avant un persist de l'objet (défini dans le mapper).
     */
    public function preSave()
    {
        $this->launchEvent(self::EVENT_SAVE);
    }

    /**
     * Mets à jour le timestamp de dernière modification
     */
    public function updateLastModification()
    {
        $this->lastModificationTimestamp = time();
    }

    /**
     * Fonction appelée après un update de l'objet (défini dans le mapper).
     */
    public function postUpdate()
    {
        $this->launchEvent(self::EVENT_UPDATED);
    }

    /**
     * Fonction appelée avant un delete de l'objet (défini dans le mapper).
     */
    public function preDelete()
    {
        $this->launchEvent(self::EVENT_DELETE);
    }

    /**
     * Renvoie l'id du Report.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Définit le label.
     *
     * @param TranslatedString $label
     */
    public function setLabel(TranslatedString $label)
    {
        $this->label = $label;
    }

    /**
     * Renvoie le label.
     *
     * @return TranslatedString
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Renvoie le cube du Report.
     *
     * @return DW_Model_Cube
     */
    public function getCube()
    {
        return $this->cube;
    }

    /**
     * Définit le type de UI_Chart utilisé.
     *
     * @param string $chartType
     *
     * @throws Core_Exception_InvalidArgument
     */
    public function setChartType($chartType)
    {
        if (!(in_array($chartType, $this->chartTypeArray))) {
            throw new Core_Exception_InvalidArgument('The chart type must be a class constant "CHART_" .');
        }
        $this->chartType = $chartType;
    }

    /**
     * Renvoie le type de UI_Chart utilisé.
     *
     * @return string
     */
    public function getChartType()
    {
        return $this->chartType;
    }

    /**
     * Définit si le Report prend en compte les incertitudes.
     *
     * @param string $sortType
     *
     * @throws Core_Exception_InvalidArgument
     */
    public function setSortType($sortType)
    {
        if (($sortType !== self::SORT_VALUE_INCREASING)
            && ($sortType !== self::SORT_VALUE_DECREASING)
            && ($sortType !== self::SORT_CONVENTIONAL)
        ) {
            throw new Core_Exception_InvalidArgument('The sort type must be a class constant "SORT_" .');
        }
        $this->sortType = $sortType;
    }

    /**
     * Renvoie le type de tri utilisé pour ordonner les résultats.
     *
     * @return string
     */
    public function getSortType()
    {
        return $this->sortType;
    }

    /**
     * Définit si le Report prend en compte les incertitudes.
     *
     * @param bool $withUncertainty
     */
    public function setWithUncertainty($withUncertainty)
    {
        $this->withUncertainty = $withUncertainty;
    }

    /**
     * Indique si le report utilise les incertitudes.
     *
     * @return bool
     */
    public function getWithUncertainty()
    {
        return $this->withUncertainty;
    }

    /**
     * Définit l'Indicator numérateur du Report.
     *
     * @param DW_Model_Indicator $indicator
     */
    public function setNumerator(DW_Model_Indicator $indicator = null)
    {
        $this->numerator = $indicator;
    }

    /**
     * Renvoie l'Indicator numérateur du Report.
     *
     * @return DW_Model_Indicator
     */
    public function getNumerator()
    {
        return $this->numerator;
    }

    /**
     * Définit l'Axis 1 au numérateur du Report.
     *
     * @param DW_Model_Axis $axis
     */
    public function setNumeratorAxis1(DW_Model_Axis $axis = null)
    {
        $this->numeratorAxis1 = $axis;
    }

    /**
     * Renvoie l'Axis 1 au numérateur du Report.
     *
     * @return DW_Model_Axis
     */
    public function getNumeratorAxis1()
    {
        return $this->numeratorAxis1;
    }

    /**
     * Définit l'Axis 2 au numérateur du Report.
     *
     * @param DW_Model_Axis $axis
     *
     * @throws Core_Exception_InvalidArgument
     */
    public function setNumeratorAxis2(DW_Model_Axis $axis = null)
    {
        if (($axis !== null) && ($this->numeratorAxis1 === null)) {
            throw new Core_Exception_InvalidArgument('Axis 1 for numerator need to be set first');
        }
        $this->numeratorAxis2 = $axis;
    }

    /**
     * Renvoie l'Axis 2 au numérateur du Report.
     *
     * @return DW_Model_Axis
     */
    public function getNumeratorAxis2()
    {
        return $this->numeratorAxis2;
    }

    /**
     * Définit l'Indicator dénominateur du Report.
     *
     * @param DW_Model_Indicator $indicator
     */
    public function setDenominator(DW_Model_Indicator $indicator = null)
    {
        $this->denominator = $indicator;
    }

    /**
     * Renvoie l'Indicator dénominateur du Report.
     *
     * @return DW_Model_Indicator
     */
    public function getDenominator()
    {
        return $this->denominator;
    }

    /**
     * Définit l'Axis 1 au dénominateur du Report.
     *
     * @param DW_Model_Axis $axis
     *
     * @throws Core_Exception_InvalidArgument
     */
    public function setDenominatorAxis1(DW_Model_Axis $axis = null)
    {
        if (($axis !== null) && ($this->numeratorAxis1 === null)) {
            throw new Core_Exception_InvalidArgument('Axis 1 for numerator need to be set first');
        }
        $this->denominatorAxis1 = $axis;
    }

    /**
     * Renvoie l'Axis 1 au dénominateur du Report.
     *
     * @return DW_Model_Axis
     */
    public function getDenominatorAxis1()
    {
        return $this->denominatorAxis1;
    }

    /**
     * Définit l'Axis 2 au dénominateur du Report.
     *
     * @param DW_Model_Axis $axis
     *
     * @throws Core_Exception_InvalidArgument
     */
    public function setDenominatorAxis2(DW_Model_Axis $axis = null)
    {
        if (($axis !== null) && ($this->numeratorAxis2 === null)) {
            throw new Core_Exception_InvalidArgument('Axis 2 for numerator need to be set first');
        }
        $this->denominatorAxis2 = $axis;
    }

    /**
     * Renvoie l'Axis 2 au dénominateur du Report.
     *
     * @return DW_Model_Axis
     */
    public function getDenominatorAxis2()
    {
        return $this->denominatorAxis2;
    }

    /**
     * Ajoute un Filter au Report.
     *
     * @param DW_Model_Filter $filter
     */
    public function addFilter(DW_Model_Filter $filter)
    {
        if (!($this->hasFilter($filter))) {
            $this->filters->add($filter);
            $this->updateLastModification();
        }
    }

    /**
     * Vérifie si le Report possède le Filter donné.
     *
     * @param DW_Model_Filter $filter
     *
     * @return boolean
     */
    public function hasFilter(DW_Model_Filter $filter)
    {
        return $this->filters->contains($filter);
    }

    /**
     * Retire un Filter de ceux utilisés par le Report.
     *
     * @param DW_Model_Filter $filter
     */
    public function removeFilter(DW_Model_Filter $filter)
    {
        if ($this->hasFilter($filter)) {
            $this->filters->removeElement($filter);
            $this->updateLastModification();
        }
    }

    /**
     * Vérifie si le Report possède au moins un Filter.
     *
     * @return bool
     */
    public function hasFilters()
    {
        return !$this->filters->isEmpty();
    }

    /**
     * Renvoie un tableau contenant tous les Filter du Report.
     *
     * @return DW_Model_Filter[]
     */
    public function getFilters()
    {
        return $this->filters->toArray();
    }

    /**
     * Renvoie les Filters correspondant à l'Axis donné.
     *
     * @param DW_Model_Axis $axis
     *
     * @throws Core_Exception_TooMany
     *
     * @return DW_Model_Filter
     */
    public function getFilterForAxis(DW_Model_Axis $axis)
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq('axis', $axis));
        $filterArray = $this->filters->matching($criteria)->toArray();

        if (empty($filterArray)) {
            return null;
        } elseif (count($filterArray) > 1) {
            throw new Core_Exception_TooMany('Too many Filters found for Axis "'.$axis->getRef().'"');
        }

        return array_shift($filterArray);
    }

    /**
     * Renvoi les valeurs du Report
     *
     * @return array
     */
    public function getValues()
    {
        return self::getEntityRepository()->getValuesForReport($this);
    }

    /**
     * Renvoie le symbol des unités associées aux valeurs du rapport.
     *
     * @return TranslatedString
     */
    public function getValuesUnitSymbol()
    {
        if (($this->getDenominator() !== null)) {
            return TranslatedString::join([
                $this->getNumerator()->getRatioUnit()->getSymbol(),
                ' / ',
                $this->getDenominator()->getRatioUnit()->getSymbol()
            ]);
        } else {
            return $this->getNumerator()->getUnit()->getSymbol();
        }
    }

    /**
     * Renvoi le chart généré par le report.
     *
     * @param string $idChart
     *
     * @throws Core_Exception_InvalidArgument
     *
     * @return UI_Chart_Bar|UI_Chart_Pie
     */
    public function getChart($idChart = 'reportChart')
    {
        if ($this->getNumeratorAxis1() === null) {
            throw new Core_Exception_InvalidArgument('At least one numerator axis is needed to drow a chart');
        }
        //@todo Trouver une meilleur solution !
        /** @var Translator $translator */
        $translator = \Core\ContainerSingleton::getContainer()->get(Translator::class);

        $chartType = $this->getChartType();
        if ($chartType === DW_Model_Report::CHART_PIE) {
            $chart = new UI_Chart_Pie($idChart);
            $chart->addAttribute('chartArea', '{width:"85%", height:"85%"}');

            foreach ($this->getValues() as $value) {
                $serie = new UI_Chart_Serie($translator->get($value['members'][0]->getLabel()));
                $serie->values[] = $value['value'];
                $chart->addSerie($serie);
            }
        } else {
            $chart = new UI_Chart_Bar($idChart);
            if ($this->getWithUncertainty() === true) {
                $chart->displayUncertainty = true;
            }

            if (($chartType === DW_Model_Report::CHART_HORIZONTAL_STACKED)
                || ($chartType === DW_Model_Report::CHART_VERTICAL_STACKED)
                || ($chartType === DW_Model_Report::CHART_HORIZONTAL_STACKEDGROUPED)
                || ($chartType === DW_Model_Report::CHART_VERTICAL_STACKEDGROUPED)) {
                $chart->stacked = true;
            }
            if (($chartType === DW_Model_Report::CHART_HORIZONTAL)
                || ($chartType === DW_Model_Report::CHART_HORIZONTAL_GROUPED)
                || ($chartType === DW_Model_Report::CHART_HORIZONTAL_STACKED)
                || ($chartType === DW_Model_Report::CHART_HORIZONTAL_STACKEDGROUPED)) {
                $chart->vertical = false;
                $chart->addAttribute('chartArea', '{top:"5%", left:"25%", width:"50%", height:"75%"}');
                $chart->addAttribute('hAxis', '{title: \''.
                    $translator->get($this->getValuesUnitSymbol()).'\',  titleTextStyle: {color: \'#9E0000\'}}');
            } else {
                $chart->addAttribute('chartArea', '{top:"5%", left:"15%", width:"50%", height:"65%"}');
                $chart->addAttribute('vAxis', '{title: \''.
                    $translator->get($this->getValuesUnitSymbol()).'\',  titleTextStyle: {color: \'#9E0000\'}}');
            }

            if ($this->numeratorAxis2 === null) {
                $chart->displaySeriesLabels = false;

                $serieAxis = new UI_Chart_Serie('axis');
                $serieAxis->type = 'string';
                $serieValues = new UI_Chart_Serie('');
                $numberValues = 0;
                foreach ($this->getValues() as $value) {
                    $serieAxis->values[] = $translator->get($value['members'][0]->getLabel());
                    $serieValues->values[] = $value['value'];
                    $serieValues->uncertainties[] = $value['uncertainty'];
                    $numberValues++;
                    if ($numberValues >= 15) {
                        break;
                    }
                }
                if ($numberValues > 8) {
                    $chart->slantedTextAngle = 90;
                }
                $chart->addSerie($serieAxis);
                $chart->addSerie($serieValues);
            } else {
                $numeratorAxis1MembersUsed = array();
                $numeratorAxis2MembersUsed = array();

                $serieAxis = new UI_Chart_Serie('axis');
                $serieAxis->type = 'string';
                $chart->addSerie($serieAxis);

                $seriesAxisLabel = array();
                $seriesValues = array();
                foreach ($this->getValues() as $value) {
                    $numeratorAxis1MembersUsed[$value['members'][0]->getId()] = $value['members'][0];
                    $numeratorAxis2MembersUsed[$value['members'][1]->getId()] = $value['members'][1];

                    $seriesAxisLabel[$value['members'][0]->getId()] = $translator->get($value['members'][0]->getLabel());

                    $serieValueId = 'serieValue'.$value['members'][1]->getId();
                    if (!isset($seriesValues[$serieValueId])) {
                        $seriesValues[$serieValueId] = array();
                    }
                    $seriesValues[$serieValueId][$value['members'][0]->getId()] = $value;
                }

                // Complément des paires de membres sans résultats et tris des valeurs.
                foreach ($numeratorAxis2MembersUsed as $member2Id => $member2) {
                    $serieValueId = 'serieValue'.$member2Id;

                    foreach ($numeratorAxis1MembersUsed as $member1Id => $member1) {
                        if (!(isset($seriesValues[$serieValueId][$member1Id]))) {
                            $seriesValues[$serieValueId][$member1Id] = array('value' => 0, 'uncertainty' => 0);
                        }
                    }
                    uksort($seriesValues[$serieValueId], function ($a, $b) {
                        $memberA = DW_Model_Member::load($a);
                        $memberB = DW_Model_Member::load($b);
                        return $memberA->getPosition() - $memberB->getPosition();
                    });
                }
                uksort($seriesAxisLabel, function ($a, $b) {
                    $memberA = DW_Model_Member::load($a);
                    $memberB = DW_Model_Member::load($b);
                    return $memberA->getPosition() - $memberB->getPosition();
                });

                // Ajout des séries et limitation de l'affichage.
                $numberAxes = 0;
                foreach ($seriesAxisLabel as $member1Id => $axisLabel) {
                    $serieAxis->values[$member1Id] = $axisLabel;

                    $numberAxes++;
                    if ($numberAxes >= 15) {
                        break;
                    }
                }
                if ($numberAxes > 8) {
                    $chart->slantedTextAngle = 90;
                }

                foreach ($seriesValues as $serieValueId => $values) {
                    $serie = new UI_Chart_Serie(
                        $translator->get($numeratorAxis2MembersUsed[explode('serieValue', $serieValueId)[1]]->getLabel())
                    );
                    $numberValues = 0;
                    foreach ($values as $memberIndex => $value) {
                        $serie->values[$memberIndex] = $value['value'];
                        $serie->uncertainties[$memberIndex] = $value['uncertainty'];

                        $numberValues++;
                        if ($numberValues >= 15) {
                            break;
                        }
                    }
                    $chart->addSerie($serie);
                }
            }
        }

        return $chart;
    }

    /**
     * Renvoi le rapport sous forme de chaine pour l'enregistrer en session.
     *
     * @return string
     */
    public function getAsString()
    {
        $stdReport = new stdClass();

        $stdReport->id = $this->id;

        // Cube.
        if ($this->cube !== null) {
            $stdReport->idCube = $this->cube->getId();
        } else {
            $stdReport->idCube = null;
        }

        // Label.
        $stdReport->label = new StdClass();
        foreach ($this->label->getAll() as $language => $translatedLabel) {
            $stdReport->label->$language = $translatedLabel;
        }

        // Numerator Indicator.
        if ($this->numerator !== null) {
            $stdReport->refNumerator = $this->numerator->getRef();
        } else {
            $stdReport->refNumerator = null;
        }

        // Numerator Axes.
        if ($this->numeratorAxis1 != null) {
            $stdReport->refNumeratorAxis1 = $this->numeratorAxis1->getRef();
        } else {
            $stdReport->refNumeratorAxis1 = null;
        }
        if ($this->numeratorAxis2 != null) {
            $stdReport->refNumeratorAxis2 = $this->numeratorAxis2->getRef();
        } else {
            $stdReport->refNumeratorAxis2 = null;
        }

        // Denominator Axes.
        if ($this->denominator !== null) {
            $stdReport->refDenominator = $this->denominator->getRef();
        } else {
            $stdReport->refDenominator = null;
        }

        // Denominator Axes.
        if ($this->denominatorAxis1 != null) {
            $stdReport->refDenominatorAxis1 = $this->denominatorAxis1->getRef();
        } else {
            $stdReport->refDenominatorAxis1 = null;
        }
        if ($this->denominatorAxis2 != null) {
            $stdReport->refDenominatorAxis2 = $this->denominatorAxis2->getRef();
        } else {
            $stdReport->refDenominatorAxis2 = null;
        }

        // Attributes.
        $stdReport->chartType = $this->chartType;
        $stdReport->sortType = $this->sortType;
        $stdReport->withUncertainty = $this->withUncertainty;

        // Filters.
        $stdReport->filters = array();
        foreach ($this->filters as $filter) {
            $stdFilter = new stdClass();
            $stdFilter->refAxis = $filter->getAxis()->getRef();
            $stdFilter->refMembers = array();
            foreach ($filter->getMembers() as $filterMember) {
                $stdFilter->refMembers[] = $filterMember->getRef();
            }
            $stdReport->filters[] = $stdFilter;
        }

        return json_encode($stdReport);
    }

    /**
     * Renvoi le rapport récupéré à partir de la chaine depuis l'enregistrer en session.
     *
     * @param string $string
     * @param DW_Model_Cube $cube
     *
     * @return DW_Model_Report
     */
    public static function getFromString($string, DW_Model_Cube $cube = null)
    {
        $stdReport = json_decode($string);

        if ($stdReport->id !== null) {
            $report = DW_Model_Report::load($stdReport->id);
        } else {
            if ($stdReport->idCube != null) {
                $cube = DW_Model_Cube::load($stdReport->idCube);
            }
            $report = new DW_Model_Report($cube);
        }

        // Label.
        $report->setLabel(TranslatedString::fromArray((array) $stdReport->label));

        // Numerator Indicator.
        if ($stdReport->refNumerator !== null) {
            $report->setNumerator(
                DW_Model_Indicator::loadByRefAndCube($stdReport->refNumerator, $report->getCube())
            );
        } else {
            $report->setNumerator();
        }

        // Numerator axes.
        if ($stdReport->refNumeratorAxis1 != null) {
            $report->setNumeratorAxis1(
                DW_Model_Axis::loadByRefAndCube($stdReport->refNumeratorAxis1, $report->getCube())
            );
        } else {
            $report->setNumeratorAxis1();
        }
        if ($stdReport->refNumeratorAxis2 != null) {
            $report->setNumeratorAxis2(
                DW_Model_Axis::loadByRefAndCube($stdReport->refNumeratorAxis2, $report->getCube())
            );
        } else {
            $report->setNumeratorAxis2();
        }

        // Denominator Indicator.
        if ($stdReport->refDenominator !== null) {
            $report->setDenominator(
                DW_Model_Indicator::loadByRefAndCube($stdReport->refDenominator, $report->getCube())
            );
        } else {
            $report->setDenominator();
        }

        // Denominator Axes.
        if ($stdReport->refDenominatorAxis1 != null) {
            $report->setDenominatorAxis1(
                DW_Model_Axis::loadByRefAndCube($stdReport->refDenominatorAxis1, $report->getCube())
            );
        } else {
            $report->setDenominatorAxis1();
        }
        if ($stdReport->refDenominatorAxis2 != null) {
            $report->setDenominatorAxis2(
                DW_Model_Axis::loadByRefAndCube($stdReport->refDenominatorAxis2, $report->getCube())
            );
        } else {
            $report->setDenominatorAxis2();
        }

        // Attributes.
        if ($stdReport->chartType !== null) {
            $report->setChartType($stdReport->chartType);
        }
        if ($stdReport->sortType !== null) {
            $report->setSortType($stdReport->sortType);
        }
        $report->setWithUncertainty($stdReport->withUncertainty);

        // Filters.
        foreach ($report->getFilters() as $reportFilter) {
            $report->removeFilter($reportFilter);
        }
        foreach ($stdReport->filters as $stdFilter) {
            $axis = DW_Model_Axis::loadByRefAndCube($stdFilter->refAxis, $report->getCube());
            $filter = new DW_Model_Filter($report, $axis);
            foreach ($stdFilter->refMembers as $filterRefMember) {
                $filter->addMember(
                    DW_Model_Member::loadByRefAndAxis($filterRefMember, $filter->getAxis())
                );
            }
        }

        return $report;
    }

    /**
     * Réinitialise le rapport.
     *
     * @return DW_Model_Report
     */
    public function reset()
    {
        $this->label = null;
        $this->chartType = null;
        $this->sortType = self::SORT_VALUE_DECREASING;
        $this->withUncertainty = false;
        $this->numerator = null;
        $this->numeratorAxis1 = null;
        $this->numeratorAxis2 = null;
        $this->denominator = null;
        $this->denominatorAxis1 = null;
        $this->denominatorAxis2 = null;
        $this->filters->clear();
    }

    /**
     * Copie le rapport dans un autre Cube.
     *
     * @param DW_Model_Cube $cube
     *
     * @return DW_Model_Report
     */
    public function copyToCube(DW_Model_Cube $cube)
    {
        $reportAsString = preg_replace(
            '#^(\{"id":)(null|[0-9]+)(,"idCube":)([0-9]+)(,.+\})$#',
            '${1}null${3}'.($cube->getId() ? : 'null').'${5}',
            $this->getAsString()
        );
        return DW_Model_Report::getFromString($reportAsString, $cube);
    }
}
