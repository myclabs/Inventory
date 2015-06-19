<?php
/**
 * @author valentin.claras
 */

namespace DW\Domain;

use Core\Translation\TranslatedString;
use Core_Event_ObservableTrait;
use Core_Exception_InvalidArgument;
use Core_Exception_TooMany;
use Core_Model_Entity;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Mnapoli\Translated\Translator;
use stdClass;
use UI_Chart_Bar;
use UI_Chart_Pie;
use UI_Chart_Serie;

/**
 * @package    DW
 * @subpackage Domain
 */
class Report extends Core_Model_Entity
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
     * @var int
     */
    protected $id;

    /**
     * @var TranslatedString
     */
    protected $label;

    /**
     * @var Cube
     */
    protected $cube;

    /**
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
     * @var null
     */
    protected $sortType = self::SORT_CONVENTIONAL;

    /**
     * @var bool
     */
    protected $withUncertainty = false;

    /**
     * @var Indicator
     */
    protected $numeratorIndicator;

    /**
     * Axis 1 utilisés au numérateur.
     *
     * @var Axis
     */
    protected $numeratorAxis1;

    /**
     * @var Axis
     */
    protected $numeratorAxis2;

    /**
     * @var Indicator
     */
    protected $denominatorIndicator;

    /**
     * @var Axis
     */
    protected $denominatorAxis1;

    /**
     * @var Axis
     */
    protected $denominatorAxis2;

    /**
     * @var Collection|Filter[]
     */
    protected $filters;

    /**
     * @var int
     */
    protected $lastModificationTimestamp = 1;


    public function __construct(Cube $cube)
    {
        $this->label = new TranslatedString();
        $this->filters = new ArrayCollection();

        $this->cube = $cube;
        $this->cube->addReport($this);
        $this->updateLastModification();
    }

    /**
     * @return Report
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
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param TranslatedString $label
     */
    public function setLabel(TranslatedString $label)
    {
        $this->label = $label;
    }

    /**
     * @return TranslatedString
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return Cube
     */
    public function getCube()
    {
        return $this->cube;
    }

    /**
     * @param string $chartType
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
     * @return string
     */
    public function getChartType()
    {
        return $this->chartType;
    }

    /**
     * @param string $sortType
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
     * @return string
     */
    public function getSortType()
    {
        return $this->sortType;
    }

    /**
     * @param bool $withUncertainty
     */
    public function setWithUncertainty($withUncertainty)
    {
        $this->withUncertainty = $withUncertainty;
    }

    /**
     * @return bool
     */
    public function getWithUncertainty()
    {
        return $this->withUncertainty;
    }

    /**
     * @param Indicator $indicator
     * @throws Core_Exception_InvalidArgument
     */
    public function setNumeratorIndicator(Indicator $indicator = null)
    {
        if (($indicator !== null) && ($indicator->getCube() !== $this->getCube())) {
            throw new Core_Exception_InvalidArgument(
                'A Report Numerator Indicator must comes from the same Cube as the Report.'
            );
        }

        $this->numeratorIndicator = $indicator;
    }

    /**
     * @return Indicator
     */
    public function getNumeratorIndicator()
    {
        return $this->numeratorIndicator;
    }

    /**
     * @param Axis $axis
     * @throws \Core_Exception_InvalidArgument
     */
    public function setNumeratorAxis1(Axis $axis = null)
    {
        if (($axis !== null) && ($axis->getCube() !== $this->getCube())) {
            throw new Core_Exception_InvalidArgument(
                'A Report Numerator Axis 1 must comes from the same Cube as the Report.'
            );
        }

        $this->numeratorAxis1 = $axis;
    }

    /**
     * @return Axis
     */
    public function getNumeratorAxis1()
    {
        return $this->numeratorAxis1;
    }

    /**
     * @param Axis $axis
     * @throws \Core_Exception_InvalidArgument
     */
    public function setNumeratorAxis2(Axis $axis = null)
    {
        if (($axis !== null) && ($axis->getCube() !== $this->getCube())) {
            throw new Core_Exception_InvalidArgument(
                'A Report Numerator Axis 2 must comes from the same Cube as the Report.'
            );
        }

        if (($axis !== null) && ($this->numeratorAxis1 === null)) {
            throw new Core_Exception_InvalidArgument('Axis 1 for numerator needs to be set first.');
        }
        $this->numeratorAxis2 = $axis;
    }

    /**
     * @return Axis
     */
    public function getNumeratorAxis2()
    {
        return $this->numeratorAxis2;
    }

    /**
     * @param Indicator $indicator
     * @throws Core_Exception_InvalidArgument
     */
    public function setDenominatorIndicator(Indicator $indicator = null)
    {
        if (($indicator!== null) && ($indicator->getCube() !== $this->getCube())) {
            throw new Core_Exception_InvalidArgument(
                'A Report Denominator Indicator must comes from the same Cube as the Report.'
            );
        }

        $this->denominatorIndicator = $indicator;
    }

    /**
     * @return Indicator
     */
    public function getDenominatorIndicator()
    {
        return $this->denominatorIndicator;
    }

    /**
     * @param Axis $axis
     * @throws Core_Exception_InvalidArgument
     */
    public function setDenominatorAxis1(Axis $axis = null)
    {
        if (($axis !== null) && ($axis->getCube() !== $this->getCube())) {
            throw new Core_Exception_InvalidArgument(
                'A Report Denominator Axis 1 must comes from the same Cube as the Report.'
            );
        }

        if (($axis !== null) && ($this->numeratorAxis1 === null)) {
            throw new Core_Exception_InvalidArgument('Axis 1 for numerator needs to be set first.');
        }
        $this->denominatorAxis1 = $axis;
    }

    /**
     * @return Axis
     */
    public function getDenominatorAxis1()
    {
        return $this->denominatorAxis1;
    }

    /**
     * @param Axis $axis
     * @throws Core_Exception_InvalidArgument
     */
    public function setDenominatorAxis2(Axis $axis = null)
    {
        if (($axis !== null) && ($axis->getCube() !== $this->getCube())) {
            throw new Core_Exception_InvalidArgument(
                'A Report Denominator Axis 2 must comes from the same Cube as the Report.'
            );
        }

        if (($axis !== null) && ($this->numeratorAxis2 === null)) {
            throw new Core_Exception_InvalidArgument('Axis 2 for numerator needs to be set first.');
        }
        $this->denominatorAxis2 = $axis;
    }

    /**
     * @return Axis
     */
    public function getDenominatorAxis2()
    {
        return $this->denominatorAxis2;
    }

    /**
     * @param Filter $filter
     */
    public function addFilter(Filter $filter)
    {
        if (!($this->hasFilter($filter))) {
            $this->filters->add($filter);
            $this->updateLastModification();
        }
    }

    /**
     * @param Filter $filter
     * @return boolean
     */
    public function hasFilter(Filter $filter)
    {
        return $this->filters->contains($filter);
    }

    /**
     * @param Filter $filter
     */
    public function removeFilter(Filter $filter)
    {
        if ($this->hasFilter($filter)) {
            $this->filters->removeElement($filter);
            $this->updateLastModification();
        }
    }

    /**
     * @return bool
     */
    public function hasFilters()
    {
        return !$this->filters->isEmpty();
    }

    /**
     * @return Filter[]
     */
    public function getFilters()
    {
        return $this->filters->toArray();
    }

    /**
     * @param Axis $axis
     * @throws Core_Exception_TooMany
     * @return Filter
     */
    public function getFilterForAxis(Axis $axis)
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq('axis', $axis));
        $filterArray = $this->filters->matching($criteria)->toArray();

        if (empty($filterArray)) {
            return null;
        } elseif (count($filterArray) > 1) {
            throw new Core_Exception_TooMany('Too many Filters found for Axis "' . $axis->getRef() . '".');
        }

        return array_shift($filterArray);
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return self::getEntityRepository()->getValuesForReport($this);
    }

    /**
     * @return TranslatedString
     */
    public function getValuesUnitSymbol()
    {
        if (($this->getDenominatorIndicator() !== null)) {
            return TranslatedString::join(
                [
                    $this->getNumeratorIndicator()->getRatioUnit()->getSymbol(),
                    ' / ',
                    $this->getDenominatorIndicator()->getRatioUnit()->getSymbol()
                ]
            );
        } else {
            return $this->getNumeratorIndicator()->getUnit()->getSymbol();
        }
    }

    /**
     * @return Report
     */
    public function reset()
    {
        $this->chartType = null;
        $this->sortType = self::SORT_VALUE_DECREASING;
        $this->withUncertainty = false;
        $this->numeratorIndicator = null;
        $this->numeratorAxis1 = null;
        $this->numeratorAxis2 = null;
        $this->denominatorIndicator = null;
        $this->denominatorAxis1 = null;
        $this->denominatorAxis2 = null;
        $this->filters->clear();
    }

}
