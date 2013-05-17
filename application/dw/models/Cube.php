<?php
/**
 * Classe DW_Model_Cube
 * @author valentin.claras
 * @author cyril.perraud
 * @package    DW
 * @subpackage Model
 */
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
     * @var string
     */
    protected $label = null;

    /**
     * Collection des Axis du Cube.
     *
     * @var Doctrine\Common\Collections\ArrayCollection
     */
    protected $axes = null;

    /**
     * Collection des Indicator du Cube.
     *
     * @var Doctrine\Common\Collections\ArrayCollection
     */
    protected $indicators = null;

    /**
     * Collection des Reports du Cube.
     *
     * @var Doctrine\Common\Collections\ArrayCollection
     */
    protected $reports = null;


    /**
     * Constructeur de la classe Cube.
     */
    public function __construct()
    {
        $this->axes = new Doctrine\Common\Collections\ArrayCollection();
        $this->indicators = new Doctrine\Common\Collections\ArrayCollection();
        $this->reports = new Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Définit le label du Cube.
     *
     * @param String $label
     */
    public function setLabel ($label)
    {
        $this->label = $label;
    }

    /**
     * Renvoie le label du Cube.
     *
     * @return String
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Ajoute un Axis à la collction du Cube.
     *
     * @param DW_Model_Axis $axis
     */
    public function addAxis(DW_Model_Axis $axis)
    {
        if (!($this->hasAxis($axis))) {
            $this->axes->add($axis);
            $axis->setCube($this);
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
     * Retire un Axis de ceux du Cube.
     *
     * @param DW_Model_Axis $axis
     */
    public function removeAxis($axis)
    {
        if ($this->hasAxis($axis)) {
            $this->axes->removeElement($axis);
            $axis->setCube(null);
        }
    }

    /**
     * Vérifie si le Cube ossède au moins un Axis.
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
     * @param string $ref
     * @throws Core_Exception_NotFound
     * @return DW_Model_Axis
     */
    public function getAxisByRef($ref)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('ref', $ref));
        $results = $this->axes->matching($criteria);
        if (count($results) > 0) {
            return $results->first();
        }
        throw new Core_Exception_NotFound("L'axe $ref est introuvable dans le cube");
    }

    /**
     * Retourne un tableau contenant les Axis racines du Cube.
     *
     * @return DW_Model_Axis[]
     */
    public function getRootAxes()
    {
        $criteria = Doctrine\Common\Collections\Criteria::create()->where(
            Doctrine\Common\Collections\Criteria::expr()->isNull('directNarrower')
        );
        return $this->axes->matching($criteria)->toArray();
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
            $indicator->setCube($this);
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
            $indicator->setCube(null);
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

}