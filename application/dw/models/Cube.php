<?php
/**
 * Classe DW_Model_Cube
 * @author valentin.claras
 * @author cyril.perraud
 * @package    DW
 * @subpackage Model
 */

use Core\Translation\TranslatedString;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;

/**
 * Cube de données.
 * @package    DW
 * @subpackage Model
 */
class DW_Model_Cube extends Core_Model_Entity
{
    /**
     * Identifiant unique du Cube.
     *
     * @var string
     */
    protected $id = null;

    /**
     * Label du Cube.
     *
     * @var TranslatedString
     */
    protected $label;

    /**
     * Collection des Axis du Cube.
     *
     * @var Collection
     */
    protected $axes = null;

    /**
     * Collection des Indicator du Cube.
     *
     * @var Collection
     */
    protected $indicators = null;

    /**
     * Collection des Reports du Cube.
     *
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
     * Renvoie l'id du Cube.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Définit le label du Cube.
     *
     * @param TranslatedString $label
     */
    public function setLabel(TranslatedString $label)
    {
        $this->label = $label;
    }

    /**
     * Renvoie le label du Cube.
     *
     * @return TranslatedString
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Ajoute un Axis à la collection du Cube.
     *
     * @param DW_Model_Axis $axis
     *
     * @throws Core_Exception_InvalidArgument
     */
    public function addAxis(DW_Model_Axis $axis)
    {
        if ($axis->getCube() !== $this) {
            throw new Core_Exception_InvalidArgument();
        }

        if (!$this->hasAxis($axis)) {
            $this->axes->add($axis);
        }
    }

    /**
     * Vérifie si l'Axis donné appartient à ceux du Cube.
     *
     * @param DW_Model_Axis $axis
     *
     * @return boolean
     */
    public function hasAxis(DW_Model_Axis $axis)
    {
        return $this->axes->contains($axis);
    }

    /**
     * Retourne un Axis du Cube en fonction de la ref donnée.
     *
     * @param string $ref
     *
     * @throws Core_Exception_NotFound
     * @throws Core_Exception_TooMany
     *
     * @return DW_Model_Axis
     */
    public function getAxisByRef($ref)
    {
        $criteria = Doctrine\Common\Collections\Criteria::create();
        $criteria->where($criteria->expr()->eq('ref', $ref));
        $axis = $this->axes->matching($criteria)->toArray();

        if (count($axis) === 0) {
            throw new Core_Exception_NotFound("No 'DW_Model_Axis' matching " . $ref);
        } elseif (count($axis) > 1) {
            throw new Core_Exception_TooMany("Too many 'DW_Model_Axis' matching " . $ref);
        }

        return array_pop($axis);
    }

    /**
     * Retire un Axis de ceux du Cube.
     *
     * @param DW_Model_Axis $axis
     */
    public function removeAxis(DW_Model_Axis $axis)
    {
        if ($this->hasAxis($axis)) {
            $this->axes->removeElement($axis);
        }
    }

    /**
     * Vérifie si le Cube possède au moins un Axis.
     *
     * @return bool
     */
    public function hasAxes()
    {
        return !$this->axes->isEmpty();
    }

    /**
     * Renvoie les Axis du Cube.
     *
     * @return DW_Model_Axis[]
     */
    public function getAxes()
    {
        return $this->axes->toArray();
    }

    /**
     * Retourne un tableau contenant les Axis racines du Cube.
     *
     * @return DW_Model_Axis[]
     */
    public function getRootAxes()
    {
        $criteria = Criteria::create()->where(Criteria::expr()->isNull('directNarrower'));
        $rootAxes = $this->axes->matching($criteria)->toArray();

        uasort(
            $rootAxes,
            function (DW_Model_Axis $a, DW_Model_Axis $b) {
                return $a->getPosition() - $b->getPosition();
            }
        );

        return $rootAxes;
    }

    /**
     * Retourne un tableau contenant les Axis du Cube ordonnés par première exploration.
     *
     * @return DW_Model_Axis[]
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
     * Retourne un tableau contenant les Axis du Cube ordonnés par dernière exploration.
     *
     * @return DW_Model_Axis[]
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
     * Ajoute une Indicator à celle possédées par le Cube
     *
     * @param DW_Model_Indicator $indicator
     */
    public function addIndicator(DW_Model_Indicator $indicator)
    {
        if (!($this->hasIndicator($indicator))) {
            $this->indicators->add($indicator);
        }
    }

    /**
     * Vérifie que la Indicator donnée appartient à celles du Cube.
     *
     * @param DW_Model_Indicator $indicator
     *
     * @return boolean
     */
    public function hasIndicator(DW_Model_Indicator $indicator)
    {
        return $this->indicators->contains($indicator);
    }

    /**
     * Retire la Indicator donnée de celles du Cube.
     *
     * @param DW_Model_Indicator $indicator
     */
    public function removeIndicator($indicator)
    {
        if ($this->hasIndicator($indicator)) {
            $this->indicators->removeElement($indicator);
        }
    }

    /**
     * Vérifie que le Cube possède au moins une Indicator.
     *
     * @return bool
     */
    public function hasIndicators()
    {
        return !$this->indicators->isEmpty();
    }

    /**
     * Renvoie un tableau des Indicator du Cube.
     *
     * @return DW_Model_Indicator[]
     */
    public function getIndicators()
    {
        return $this->indicators->toArray();
    }

    /**
     * @param string $ref
     * @throws Core_Exception_NotFound
     * @return DW_Model_Indicator
     */
    public function getIndicatorByRef($ref)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('ref', $ref));
        $results = $this->indicators->matching($criteria);
        if (count($results) > 0) {
            return $results->first();
        }
        throw new Core_Exception_NotFound("L'indicateur $ref est introuvable dans le cube");
    }

    /**
     * Ajoute un Report à la collection du Cube.
     *
     * @param DW_Model_Report $report
     */
    public function addReport(DW_Model_Report $report)
    {
        if (!($this->hasReport($report))) {
            $this->reports->add($report);
        }
    }

    /**
     * Vérifie si l'Report donné appartient à ceux du Cube.
     *
     * @param DW_Model_Report $report
     *
     * @return boolean
     */
    public function hasReport(DW_Model_Report $report)
    {
        return $this->reports->contains($report);
    }

    /**
     * Retire un Report de ceux du Cube.
     *
     * @param DW_Model_Report $report
     */
    public function removeReport(DW_Model_Report $report)
    {
        if ($this->hasReport($report)) {
            $this->reports->removeElement($report);
        }
    }

    /**
     * Vérifie si le Cube ossède au moins un Report.
     *
     * @return bool
     */
    public function hasReports()
    {
        return !$this->reports->isEmpty();
    }

    /**
     * Renvoie les Report du Cube.
     *
     * @return DW_Model_Report[]
     */
    public function getReports()
    {
        return $this->reports->toArray();
    }
}
