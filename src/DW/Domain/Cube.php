<?php
/**
 * @author valentin.claras
 */

namespace DW\Domain;

use Core\Translation\TranslatedString;
use Core_Exception_InvalidArgument;
use Core_Exception_NotFound;
use Core_Exception_TooMany;
use Core_Model_Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;

/**
 * @package    DW
 * @subpackage Domain
 */
class Cube extends Core_Model_Entity
{
    /**
     * @var string
     */
    protected $id = null;

    /**
     * @var TranslatedString
     */
    protected $label;

    /**
     * @var Collection
     */
    protected $axes = null;

    /**
     * @var Collection
     */
    protected $indicators = null;

    /**
     * @var Collection
     */
    protected $reports = null;


    public function __construct()
    {
        $this->label = new TranslatedString();
        $this->axes = new ArrayCollection();
        $this->indicators = new ArrayCollection();
        $this->reports = new ArrayCollection();
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
     * @param Axis $axis
     * @throws Core_Exception_InvalidArgument
     */
    public function addAxis(Axis $axis)
    {
        if ($axis->getCube() !== $this) {
            throw new Core_Exception_InvalidArgument();
        }

        if (!$this->hasAxis($axis)) {
            $this->axes->add($axis);
        }
    }

    /**
     * @param Axis $axis
     * @return boolean
     */
    public function hasAxis(Axis $axis)
    {
        return $this->axes->contains($axis);
    }

    /**
     * @param string $ref
     * @throws Core_Exception_NotFound
     * @throws Core_Exception_TooMany
     * @return Axis
     */
    public function getAxisByRef($ref)
    {
        $criteria = Criteria::create();
        $criteria->where($criteria->expr()->eq('ref', $ref));
        $axis = $this->axes->matching($criteria)->toArray();

        if (count($axis) === 0) {
            throw new Core_Exception_NotFound('No "Axis" matching "' . $ref . '".');
        } elseif (count($axis) > 1) {
            throw new Core_Exception_TooMany('Too many "Axis" matching "' . $ref . '".');
        }

        return array_pop($axis);
    }

    /**
     * @param Axis $axis
     */
    public function removeAxis(Axis $axis)
    {
        if ($this->hasAxis($axis)) {
            $this->axes->removeElement($axis);
        }
    }

    /**
     * @return bool
     */
    public function hasAxes()
    {
        return !$this->axes->isEmpty();
    }

    /**
     * @return Axis[]
     */
    public function getAxes()
    {
        return $this->axes->toArray();
    }

    /**
     * @return Axis[]
     */
    public function getRootAxes()
    {
        $criteria = Criteria::create()->where(Criteria::expr()->isNull('directNarrower'));
        $rootAxes = array_values($this->axes->matching($criteria)->toArray());

        uasort(
            $rootAxes,
            function (Axis $a, Axis $b) {
                return $a->getPosition() - $b->getPosition();
            }
        );

        return $rootAxes;
    }

    /**
     * @return Axis[]
     */
    public function getFirstOrderedAxes()
    {
        $axes = array();
        foreach ($this->getRootAxes() as $rootAxis) {
            $axes[] = $rootAxis;
            foreach ($rootAxis->getAllBroadersFirstOrdered() as $recursiveBroader) {
                $axes[] = $recursiveBroader;
            }
        }
        return $axes;
    }

    /**
     * @return Axis[]
     */
    public function getLastOrderedAxes()
    {
        $axes = array();
        foreach ($this->getRootAxes() as $rootAxis) {
            foreach ($rootAxis->getAllBroadersLastOrdered() as $recursiveBroader) {
                $axes[] = $recursiveBroader;
            }
            $axes[] = $rootAxis;
        }
        return $axes;
    }

    /**
     * @param Indicator $indicator
     */
    public function addIndicator(Indicator $indicator)
    {
        if (!($this->hasIndicator($indicator))) {
            $this->indicators->add($indicator);
        }
    }

    /**
     * @param Indicator $indicator
     * @return boolean
     */
    public function hasIndicator(Indicator $indicator)
    {
        return $this->indicators->contains($indicator);
    }

    /**
     * @param Indicator $indicator
     */
    public function removeIndicator($indicator)
    {
        if ($this->hasIndicator($indicator)) {
            $this->indicators->removeElement($indicator);
        }
    }

    /**
     * @return bool
     */
    public function hasIndicators()
    {
        return !$this->indicators->isEmpty();
    }

    /**
     * @return Indicator[]
     */
    public function getIndicators()
    {
        return $this->indicators->toArray();
    }

    /**
     * @param string $ref
     * @throws Core_Exception_NotFound
     * @throws Core_Exception_TooMany
     * @return Indicator
     */
    public function getIndicatorByRef($ref)
    {
        $criteria = Criteria::create();
        $criteria->where($criteria->expr()->eq('ref', $ref));
        $indicator = $this->indicators->matching($criteria)->toArray();

        if (count($indicator) === 0) {
            throw new Core_Exception_NotFound('No "Indicator" matching "' . $ref . '".');
        } elseif (count($indicator) > 1) {
            throw new Core_Exception_TooMany('Too many "Indicator" matching "' . $ref . '".');
        }

        return array_pop($indicator);
    }

    /**
     * @param Report $report
     */
    public function addReport(Report $report)
    {
        if (!($this->hasReport($report))) {
            $this->reports->add($report);
        }
    }

    /**
     * @param Report $report
     * @return boolean
     */
    public function hasReport(Report $report)
    {
        return $this->reports->contains($report);
    }

    /**
     * @param Report $report
     */
    public function removeReport(Report $report)
    {
        if ($this->hasReport($report)) {
            $this->reports->removeElement($report);
        }
    }

    /**
     * @return bool
     */
    public function hasReports()
    {
        return !$this->reports->isEmpty();
    }

    /**
     * @return Report[]
     */
    public function getReports()
    {
        return $this->reports->toArray();
    }
}
