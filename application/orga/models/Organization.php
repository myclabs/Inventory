<?php
/**
 * Classe Orga_Model_Organization
 * @author     valentin.claras
 * @author     maxime.fourt
 * @package    Orga
 * @subpackage Model
 */
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Organization organisationnel.
 * @package    Orga
 * @subpackage Model
 */
class Orga_Model_Organization extends Core_Model_Entity
{

    use Core_Model_Entity_Translatable;

    /**
     * Identifiant unique du Organization.
     *
     * @var string
     */
    protected $id = null;

    /**
     * Label du Organization.
     *
     * @var string
     */
    protected $label = '';

    /**
     * Collection des Axis du Organization.
     *
     * @var Collection|Orga_Model_Axis[]
     */
    protected $axes = null;

    /**
     * Collection des Granularity du Organization.
     *
     * @var Collection|Orga_Model_Granularity[]
     */
    protected $granularities = null;

    /**
     * Granularity organisationnelle où est spécifiée le statut des inventaires.
     *
     * @var Orga_Model_Granularity
     */
    protected $granularityForInventoryStatus = null;


    /**
     * Constructeur de la classe Organization.
     */
    public function __construct()
    {
        $this->axes = new ArrayCollection();
        $this->granularities = new ArrayCollection();
    }

    /**
     * Renvoie l'id du Organization.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Spécifie le label du Organization.
     *
     * @param string $label
     *
     * @throws Core_Exception_InvalidArgument
     */
    public function setLabel($label)
    {
        if (!is_string($label)) {
            throw new Core_Exception_InvalidArgument("Le label d'un Organization doit être une chaîne de caractères");
        }
        $this->label = $label;
    }

    /**
     * Renvoie le label textuel du projet.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Ajoute un Axis à la collection du Organization.
     *
     * @param Orga_Model_Axis $axis
     *
     * @throws Core_Exception_InvalidArgument
     */
    public function addAxis(Orga_Model_Axis $axis)
    {
        if ($axis->getOrganization() !== $this) {
            throw new Core_Exception_InvalidArgument();
        }

        if (!$this->hasAxis($axis)) {
            $this->axes->add($axis);
            $this->orderGranularities();
        }
    }

    /**
     * Vérifie si l'Axis donné appartient à ceux du Organization.
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
     * Retourne un Axis du organization en fonction de la ref donnée.
     *
     * @param string $ref
     *
     * @throws Core_Exception_NotFound
     * @throws Core_Exception_TooMany
     *
     * @return Orga_Model_Axis
     */
    public function getAxisByRef($ref)
    {
        $criteria = Doctrine\Common\Collections\Criteria::create();
        $criteria->where($criteria->expr()->eq('ref', $ref));
        $axis = $this->axes->matching($criteria)->toArray();

        if (count($axis) === 0) {
            throw new Core_Exception_NotFound("No 'Orga_Model_Axis' matching " . $ref);
        } else if (count($axis) > 1) {
            throw new Core_Exception_TooMany("Too many 'Orga_Model_Axis' matching " . $ref);
        }

        return array_pop($axis);
    }

    /**
     * Retire un Axis de ceux du Organization.
     *
     * @param Orga_Model_Axis $axis
     */
    public function removeAxis(Orga_Model_Axis $axis)
    {
        if ($this->hasAxis($axis)) {
            $this->axes->removeElement($axis);
        }
    }

    /**
     * Vérifie si le Organization ossède au moins un Axis.
     *
     * @return bool
     */
    public function hasAxes()
    {
        return !$this->axes->isEmpty();
    }

    /**
     * Renvoie les Axis du Organization.
     *
     * @return Orga_Model_Axis[]
     */
    public function getAxes()
    {
        return $this->axes->toArray();
    }

    /**
     * Retourne un tableau contenant les Axis racines du Organization.
     *
     * @return Orga_Model_Axis[]
     */
    public function getRootAxes()
    {
        $criteria = Doctrine\Common\Collections\Criteria::create()->where(
            Doctrine\Common\Collections\Criteria::expr()->isNull('directNarrower')
        );
        $rootAxes = $this->axes->matching($criteria)->toArray();

        uasort(
            $rootAxes,
            function ($a, $b) { return $a->getPosition() - $b->getPosition(); }
        );

        return $rootAxes;
    }

    /**
     * Retourne un tableau contenant les Axis du Organization ordonnés par première exploration.
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
     * Retourne un tableau contenant les Axis du Organization ordonnés par dernière exploration.
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
     * Indique la position globale d'un Axis donnés dans le Organization.
     *
     * @param Orga_Model_Axis $askingAxis
     *
     * @return int
     */
    public function getAxisGlobalPosition(Orga_Model_Axis $askingAxis)
    {
        $globalPosition = 1;

        foreach ($this->getFirstOrderedAxes() as $axis) {
            if ($askingAxis->getRef() === $axis->getRef()) {
                return $globalPosition;
            }
            $globalPosition++;
        }
    }

    /**
     * Ajoute une Granularity au Organization
     *
     * @param Orga_Model_Granularity $granularity
     *
     * @throws Core_Exception_InvalidArgument
     */
    public function addGranularity(Orga_Model_Granularity $granularity)
    {
        if ($granularity->getOrganization() !== $this) {
            throw new Core_Exception_InvalidArgument();
        }

        if (!$this->hasGranularity($granularity)) {
            $this->granularities->add($granularity);
            $granularity->setPosition();
            $this->orderGranularities();
        }
    }

    /**
     * Vérifie que la Granularity donnée appartient à celles du Organization.
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
     * Retourne une Granularity du organization en fonction de la ref donnée.
     *
     * @param string $ref
     *
     * @throws Core_Exception_NotFound
     * @throws Core_Exception_TooMany
     *
     * @return Orga_Model_Granularity
     */
    public function getGranularityByRef($ref)
    {
        $criteria = Doctrine\Common\Collections\Criteria::create();
        $criteria->where($criteria->expr()->eq('ref', $ref));
        $granularity = $this->granularities->matching($criteria)->toArray();

        if (empty($granularity)) {
            throw new Core_Exception_NotFound("No 'Orga_Model_Granularity' matching " . $ref);
        } else {
            if (count($granularity) > 1) {
                throw new Core_Exception_TooMany("Too many 'Orga_Model_Granularity' matching " . $ref);
            }
        }

        return array_pop($granularity);
    }

    /**
     * Retire la Granularity donnée de celles du Organization.
     *
     * @param Orga_Model_Granularity $granularity
     */
    public function removeGranularity(Orga_Model_Granularity $granularity)
    {
        if ($this->hasGranularity($granularity)) {
            $this->granularities->removeElement($granularity);
        }
    }

    /**
     * Vérifie que le Organization possède au moins une Granularity.
     *
     * @return bool
     */
    public function hasGranularities()
    {
        return !$this->granularities->isEmpty();
    }

    /**
     * Renvoie un tableau des Granularity du Organization.
     *
     * @return Orga_Model_Granularity[]
     */
    public function getGranularities()
    {
        return $this->granularities->toArray();
    }

    /**
     * Ordonne les Granularity dans le Organization.
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
            try {
                $orderedGranularity->setPosition(1);
            } catch (Core_Exception_UndefinedAttribute $e) {
                // La Granularity n'a pas de position, elle est donc en train d'être supprimée.
            }
        }
    }

    /**
     * Spécifie la Granularity où est spécifié le statut des inventaires.
     *
     * @param Orga_Model_Granularity $granularity
     *
     * @throws Core_Exception_InvalidArgument
     */
    public function setGranularityForInventoryStatus(Orga_Model_Granularity $granularity=null)
    {
        if ($this->granularityForInventoryStatus !== $granularity) {
            if ($this->granularityForInventoryStatus !== null) {
                foreach ($this->granularityForInventoryStatus->getCells() as $cell) {
                    $cell->setInventoryStatus(Orga_Model_Cell::STATUS_NOTLAUNCHED);
                }
            }
            $this->granularityForInventoryStatus = $granularity;
        }
    }

    /**
     * Renvoie l'instance de la Granularity où est spécifié le statut des inventaires.
     *
     * @throws Core_Exception_UndefinedAttribute
     *
     * @return Orga_Model_Granularity
     */
    public function getGranularityForInventoryStatus()
    {
        if ($this->granularityForInventoryStatus === null) {
            throw new Core_Exception_UndefinedAttribute(
                "Le niveau organisationnel des inventaires n'a pas été défini."
            );
        }
        return $this->granularityForInventoryStatus;
    }

    /**
     * Renvoie les Granularity de saisie.
     *
     * @return Orga_Model_Granularity[]
     */
    public function getInputGranularities()
    {
        //@todo Supprimer getGranularities quand il sera possible de filtrer isNotNull sur une collection non initialisée.
        //Update Un fix a été fait dans la 2.4, attendre une version stable.
        $this->getGranularities();
        $criteria = Doctrine\Common\Collections\Criteria::create()->where(
            Doctrine\Common\Collections\Criteria::expr()->neq('inputConfigGranularity', null)
        );
        return $this->granularities->matching($criteria)->toArray();
    }

}