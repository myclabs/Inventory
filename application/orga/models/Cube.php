<?php
/**
 * Classe Orga_Model_Cube
 * @author     valentin.claras
 * @author     maxime.fourt
 * @package    Orga
 * @subpackage Model
 */

/**
 * Cube organisationnel.
 * @package    Orga
 * @subpackage Model
 */
class Orga_Model_Cube extends Core_Model_Entity
{

    /**
     * Identifiant unique du Cube.
     *
     * @var string
     */
    protected $id = null;

    /**
     * Collection des Axis du Cube.
     *
     * @var Doctrine\Common\Collections\ArrayCollection
     */
    protected $axes = null;

    /**
     * Collection des Granularity du Cube.
     *
     * @var Doctrine\Common\Collections\ArrayCollection
     */
    protected $granularities = null;


    /**
     * Constructeur de la classe Cube.
     */
    public function __construct()
    {
        $this->axes = new Doctrine\Common\Collections\ArrayCollection();
        $this->granularities = new Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Ajoute un Axis à la collction du Cube.
     *
     * @param Orga_Model_Axis $axis
     */
    public function addAxis(Orga_Model_Axis $axis)
    {
        if (!($this->hasAxis($axis))) {
            $this->axes->add($axis);
            $axis->setCube($this);
            $this->orderAxes();
            $this->orderGranularities();
        }
    }

    /**
     * Vérifie si l'Axis donné appartient à ceux du Cube.
     *
     * @param Orga_Model_Axis $axis
     *
     * @return boolean
     */
    public function hasAxis(Orga_Model_Axis $axis)
    {
        return $this->axes->contains($axis);
    }

    /**
     * Retire un Axis de ceux du Cube.
     *
     * @param Orga_Model_Axis $axis
     */
    public function removeAxis($axis)
    {
        if ($this->hasAxis($axis)) {
            $this->axes->removeElement($axis);
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
     * @return Orga_Model_Axis[]
     */
    public function getAxes()
    {
        return $this->axes->toArray();
    }

    /**
     * Retourne un tableau contenant les Axis racines du Cube.
     *
     * @return Orga_Model_Axis[]
     */
    public function getRootAxes()
    {
        $criteria = Doctrine\Common\Collections\Criteria::create()->where(
            Doctrine\Common\Collections\Criteria::expr()->isNull('directNarrower')
        );
        $rootAxes = $this->axes->matching($criteria)->toArray();

        @uasort(
            $rootAxes,
            function ($a, $b) {
                return $a->getPosition() - $b->getPosition();
            }
        );

        return $rootAxes;
    }

    /**
     * Retourne un tableau contenant les Axis du Cube ordonnés par première exploration.
     *
     * @return Orga_Model_Axis[]
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
     * @return Orga_Model_Axis[]
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
     * Ordonne les Axis du Cube de manière globale.
     *
     * @return array
     */
    public function orderAxes()
    {
        $globalPosition = 1;

        foreach ($this->getFirstOrderedAxes() as $axis) {
            $axis->setGlobalPosition($globalPosition);
            $globalPosition++;
        }
    }

    /**
     * Ajoute une Granularity à celle possédées par le Cube
     *
     * @param Orga_Model_Granularity $granularity
     */
    public function addGranularity(Orga_Model_Granularity $granularity)
    {
        if (!($this->hasGranularity($granularity))) {
            $this->granularities->add($granularity);
            $granularity->setCube($this);
        }
    }

    /**
     * Vérifie que la Granularity donnée appartient à celles du Cube.
     *
     * @param Orga_Model_Granularity $granularity
     *
     * @return boolean
     */
    public function hasGranularity(Orga_Model_Granularity $granularity)
    {
        return $this->granularities->contains($granularity);
    }

    /**
     * Retire la Granularity donnée de celles du Cube.
     *
     * @param Orga_Model_Granularity $granularity
     */
    public function removeGranularity($granularity)
    {
        if ($this->hasGranularity($granularity)) {
            $this->granularities->removeElement($granularity);
            $granularity->delete();
        }
    }

    /**
     * Vérifie que le Cube possède au moins une Granularity.
     *
     * @return bool
     */
    public function hasGranularities()
    {
        return !$this->granularities->isEmpty();
    }

    /**
     * Renvoie un tableau des Granularity du Cube.
     *
     * @return Orga_Model_Granularity[]
     */
    public function getGranularities()
    {
        return $this->granularities->toArray();
    }

    /**
     * Ordonne les Granularity dans le Cube.
     *
     * @return array
     */
    public function orderGranularities()
    {
        $granularities = array();
        foreach ($this->getGranularities() as $granularity) {
            $granularities[spl_object_hash($granularity)] = array(
                'granularity' => $granularity,
                'position'    => ''
            );
        }

        if (count($granularities) > 1) {
            foreach ($this->getFirstOrderedAxes() as $index => $axis) {
                foreach ($this->getGranularities() as $granularity) {
                    if (!$axis->hasGranularity($granularity)) {
                        $granularities[spl_object_hash($granularity)]['position'] .= '1';
                    } else {
                        $granularities[spl_object_hash($granularity)]['position'] .= '0';
                    }
                }
            }
        }

        $orderedGranularities = array();
        foreach ($granularities as $granularity) {
            $orderedGranularities[$granularity['position']] = $granularity['granularity'];
        }
        ksort($orderedGranularities);

        foreach ($orderedGranularities as $position => $orderedGranularity) {
            $orderedGranularity->setPosition();
            $orderedGranularity->setPosition(1);
        }
    }

}