<?php
/**
 * Classe Orga_Model_Axis
 * @author valentin.claras
 * @author diana.dragusin
 * @package    Orga
 * @subpackage Model
 */
use Doctrine\Common\Collections\Collection;

/**
 * Objet métier définissant un axe organisationnel au sein d'un cube.
 * @package    Orga
 * @subpackage Model
 */
class Orga_Model_Axis extends Core_Model_Entity
{
    //@todo utiliser l'héritage dès qu'une version nouvelle de php (5.4.7 >) sera disponible.
//    use Core_Strategy_Ordered {
//        Core_Strategy_Ordered::setPositionInternal as setPositionInternalOrdered;
//    }
    use Core_Strategy_Ordered;
    use Core_Model_Entity_Translatable;

    // Constantes de tris et de filtres.
    const QUERY_REF = 'ref';
    const QUERY_LABEL = 'label';
    const QUERY_POSITION = 'position';
    const QUERY_GLOBALPOSITION = 'globalPosition';
    const QUERY_NARROWER = 'directNarrower';
    const QUERY_CUBE = 'cube';


    /**
     * Identifiant uniqe de l'axe.
     *
     * @var int
     */
    protected  $id = null;

    /**
     * Référence unique (au sein d'un cube) de l'axe.
     *
     * @var string
     */
    protected  $ref = null;

    /**
     * Label de l'axe.
     *
     * @var string
     */
    protected $label = null;

    /**
     * Cube contenant l'axe.
     *
     * @var Orga_Model_Cube
     */
    protected $cube = null;

    /**
     * Position générale de l'axe dans le cube.
     *
     * @var int
     */
    protected $globalPosition = null;

    /**
     * Axe narrower de l'axe courant.
     *
     * @var Orga_Model_Axis
     */
    protected $directNarrower = null;

    /**
     * Collection des Axis broader de l'axe courant.
     *
     * @var Collection|Orga_Model_Axis[]
     */
    protected $directBroaders = null;

    /**
     * Définit si l'Axis courant contextualise les Member.
     *
     * @var bool
     */
    protected $contextualizing = false;

    /**
     * Collection des Member de l'Axis courant.
     *
     * @var Collection|Orga_Model_Member[]
     */
    protected $members = null;

    /**
     * Collection des granularités utilisant l'axe.
     *
     * @var Collection|Orga_Model_Granularity[]
     */
    protected $granularities = null;


    /**
     * Constructeur de la classe Axis.
     */
    public function __construct()
    {
        $this->directBroaders = new Doctrine\Common\Collections\ArrayCollection();
        $this->members = new Doctrine\Common\Collections\ArrayCollection();
        $this->granularities = new Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Renvoi les valeurs du contexte pour l'objet.
     * .
     * @return array
     */
    protected function getContext()
    {
        return array('directNarrower' => $this->directNarrower, 'cube' => $this->cube);
    }

    /**
     * Fonction appelé avant un persist de l'objet (défini dans le mapper).
     */
    public function preSave()
    {
        try {
            $this->checkHasPosition();
        } catch (Core_Exception_UndefinedAttribute $e) {
            $this->setPosition();
        }
    }

    /**
     * Fonction appelé avant un update de l'objet (défini dans le mapper).
     */
    public function preUpdate()
    {
        $this->checkHasPosition();
    }

    /**
     * Fonction appelé avant un delete de l'objet (défini dans le mapper).
     */
    public function preDelete()
    {
        $this->deletePosition();
        $this->getCube()->removeAxis($this);
    }

    /**
     * Fonction appelé après un load de l'objet (défini dans le mapper).
     */
    public function postLoad()
    {
        $this->updateCachePosition();
    }

    /**
     * Charge un Axis en fonction de sa référence et son cube.
     *
     * @param string $ref
     * @param Orga_Model_Cube $cube
     *
     * @return Orga_Model_Axis
     */
    public static function loadByRefAndCube($ref, $cube)
    {
        return self::getEntityRepository()->loadBy(array('ref' => $ref, 'cube' => $cube));
    }

    /**
     * Met à jour les hashKey des membres et des cellules.
     */
    protected function updateMembersAndCellsHashKey()
    {
        foreach ($this->getMembers() as $member) {
            $member->updateParentMembersHashKey();
        }

        foreach ($this->getGranularities() as $granularity) {
            foreach ($granularity->getCells() as $cell) {
                $cell->updateMembersHashKey();
            }
        }
    }

    /**
     * Définit la référence de l'axe. Ne peut pas être "global".
     *
     * @param string $ref
     *
     * @throws Core_Exception_InvalidArgument
     */
    public function setRef($ref)
    {
        if ($ref === 'global') {
            throw new Core_Exception_InvalidArgument('An Axis ref cannot be "global".');
        } else {
            $this->ref = $ref;
            foreach ($this->granularities as $granularity) {
                $granularity->updateRef();
            }
        }
    }

    /**
     * Renvoie la référence de l'axe.
     *
     * @return String
     */
    public function getRef ()
    {
        return $this->ref;
    }

    /**
     * Définit le label de l'axe.
     *
     * @param String $label
     */
    public function setLabel ($label)
    {
        $this->label = $label;
    }

    /**
     * Renvoie le label de l'axe.
     *
     * @return String
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Définit le cube de l'axe.
     *
     * @param Orga_Model_Cube $cube
     */
    public function setCube(Orga_Model_Cube $cube=null)
    {
        if ($this->cube !== $cube) {
            if ($this->cube !== null) {
                throw new Core_Exception_TooMany('Cube already set, an axis cannot be move.');
            }
            $this->cube = $cube;
            if ($cube !== null) {
                $cube->addAxis($this);
            }
        }
    }

    /**
     * Renvoie le cube de l'axe.
     *
     * @return Orga_Model_Cube
     */
    public function getCube()
    {
        return $this->cube;
    }

    /**
     * Définit la position de l'objet et renvoi sa nouvelle position.
     *
     * @param int $position
     *
     * @return int Nouvelle position
     *
     * @throws Core_Exception_InvalidArgument Position invalide
     * @throws Core_Exception_UndefinedAttribute La position n'est pas déjà définie
     */
    public function setPositionInternal($position=null)
    {
        if (($this->position === null) && ($position === null)) {
            $this->addPosition();
        } else if ($position !== null) {
            $this->checkHasPosition();

            // Vérification que la position ne soit pas inférieure à la première et supérieure à la dernière.
            if (($position < 1) || ($position > self::getLastPositionByContext($this->getContext()))) {
                throw new Core_Exception_InvalidArgument("The position '$position' is out of range.");
            }

            // Tant que la position n'est pas celle souhaitée on la modifie.
            while ($this->position != $position) {
                if ($this->position < $position) {
                    $this->swapWithNext();
                } else if ($this->position > $position) {
                    $this->swapWithPrevious();
                }
            }
        }
//        $this->setPositionInternalOrdered($position);

        $this->getCube()->orderAxes();
        $this->getCube()->orderGranularities();

        $this->updateMembersAndCellsHashKey();
    }

    /**
     * Définit la position globale de l'axe.
     *
     * @param int $globalPosition
     */
    public function setGlobalPosition($globalPosition)
    {
        $this->globalPosition = $globalPosition;
    }

    /**
     * Renvoie la position globale de l'axe.
     *
     * @return int
     */
    public function getGlobalPosition()
    {
        return $this->globalPosition;
    }

    /**
     * Définit l'axe narrower de l'axe courant.
     *
     * @param Orga_Model_Axis $narrowerAxis
     */
    public function setDirectNarrower(Orga_Model_Axis $narrowerAxis=null)
    {
        if ($this->directNarrower !== $narrowerAxis) {
            if ($this->directNarrower !== null) {
                $this->directNarrower->removeDirectBroader($this);
            }
            $this->deletePosition();
            $this->directNarrower = $narrowerAxis;
            if ($narrowerAxis !== null) {
                $narrowerAxis->addDirectBroader($this);
            }
            $this->setPosition();
        }
    }

    /**
     * Renvoie l'axe narrower de l'axe courant.
     *
     * @return Orga_Model_Axis
     */
    public function getDirectNarrower()
    {
        return $this->directNarrower;
    }

    /**
     * Ajoute un Axis broader à l'axe courant.
     *
     * @param Orga_Model_Axis $broaderAxis
     */
    public function addDirectBroader(Orga_Model_Axis $broaderAxis)
    {
        if (!($this->hasDirectBroader($broaderAxis))) {
            $this->directBroaders->add($broaderAxis);
            $broaderAxis->setDirectNarrower($this);
        }
    }

    /**
     * Vérifie si un Axis donné est un broader de l'axe courant.
     *
     * @param Orga_Model_Axis $broaderAxis
     *
     * @return boolean
     */
    public function hasDirectBroader(Orga_Model_Axis $broaderAxis)
    {
        return $this->directBroaders->contains($broaderAxis);
    }

    /**
     * Retire l'axe donnés .
     *
     * @param Orga_Model_Axis $broaderAxis
     */
    public function removeDirectBroader($broaderAxis)
    {
        if ($this->hasDirectBroader($broaderAxis)) {
            $this->directBroaders->removeElement($broaderAxis);
            $broaderAxis->setDirectNarrower(null);
        }
    }

    /**
     * Indique si l'Axis possède des broaders directs.
     *
     * @return bool
     */
    public function hasDirectBroaders()
    {
        return !$this->directBroaders->isEmpty();
    }

    /**
     * Retourne l'ensemble des broaders directs de l'Axis.
     *
     * @return Orga_Model_Axis[]
     */
    public function getDirectBroaders()
    {
        $directBroaders = $this->directBroaders->toArray();

        @uasort(
            $directBroaders,
            function ($a, $b) { return $a->getPosition() - $b->getPosition(); }
        );

        return $directBroaders;
    }

    /**
     * Retourne récursivement, tous les broaders de l'Axis dans l'ordre de première exploration.
     *
     * @return Orga_Model_Axis[]
     */
    public function getAllBroadersFirstOrdered()
    {
        $broaders = array();
        foreach ($this->getDirectBroaders() as $directBroader) {
            $broaders[] = $directBroader;
            foreach ($directBroader->getAllBroadersFirstOrdered() as $recursiveBroader) {
                $broaders[] = $recursiveBroader;
            }
        }
        return $broaders;
    }

    /**
     * Retourne récursivement, tous les broaders de l'Axis dans l'ordre de dernière exploration.
     *
     * @return Orga_Model_Axis[]
     */
    public function getAllBroadersLastOrdered()
    {
        $broaders = array();
        foreach ($this->getDirectBroaders() as $directBroader) {
            foreach ($directBroader->getAllBroadersLastOrdered() as $recursiveBroader) {
                $broaders[] = $recursiveBroader;
            }
            $broaders[] = $directBroader;
        }
        return $broaders;
    }

    /**
     * Définit si l'Axis.
     *
     * @param bool $contextualizing
     */
    public function setContextualize($contextualizing)
    {
        if ($this->contextualizing !== $contextualizing) {
            $this->contextualizing = $contextualizing;

            $this->updateMembersAndCellsHashKey();
        }
    }

    /**
     * Indique si l'axe contextualise les Member.
     *
     * @return bool
     */
    public function isContextualizing()
    {
        return $this->contextualizing;
    }

    /**
     * Ajoute une Member à l'Axis.
     *
     * @param Orga_Model_Member $member
     */
    public function addMember(Orga_Model_Member $member)
    {
        if (!($this->hasMember($member))) {
            $this->members->add($member);
            $member->setAxis($this);
            foreach ($this->granularities as $granularity) {
                $granularity->generateCellsFromNewMember($member);
            }
        }
    }

    /**
     * Vérifie si le Member passé fait partie de l'Axis.
     *
     * @param Orga_Model_Member $member
     *
     * @return boolean
     */
    public function hasMember(Orga_Model_Member $member)
    {
        return $this->members->contains($member);
    }

    /**
     * Supprime le Member donné de la collection de l'Axis.
     *
     * @param Orga_Model_Member $member
     */
    public function removeMember($member)
    {
        if ($this->hasMember($member)) {
            $this->members->removeElement($member);
            $member->setAxis(null);
        }
    }

    /**
     * Vérifie si l'Axis possède des Member.
     *
     * @return bool
     */
    public function hasMembers()
    {
        return !$this->members->isEmpty();
    }

    /**
     * Retourne un tableau contenant les members de l'Axis.
     *
     * @param string $completeRef
     * @return Orga_Model_Member[]
     */
    public function getMemberByCompleteRef($completeRef)
    {
        $criteria = Doctrine\Common\Collections\Criteria::create();
        $criteria->where($criteria->expr()->eq('ref', explode('#', $completeRef)[0]));
        $criteria->andWhere($criteria->expr()->eq('parentMembersHashKey', explode('#', $completeRef)[1]));
        $member = $this->members->matching($criteria)->toArray();

        if (empty($member)) {
            throw new Core_Exception_NotFound("No 'Orga_Model_Member' matching " . $completeRef);
        } else {
            if (count($member) > 1) {
                throw new Core_Exception_TooMany("Too many 'Orga_Model_Member' matching " . $completeRef);
            }
        }

        return array_pop($member);
    }

    /**
     * Retourne un tableau contenant les members de l'Axis.
     *
     * @return Orga_Model_Member[]
     */
    public function getMembers()
    {
        return $this->members->toArray();
    }

    /**
     * Ajoute une Granularity à l'Axis ourrant.
     *
     * @param Orga_Model_Granularity $granularity
     */
    public function addGranularity(Orga_Model_Granularity $granularity)
    {
        if (!($this->hasGranularity($granularity))) {
            $this->granularities->add($granularity);
            $granularity->addAxis($this);
        }
    }

    /**
     * Vérifie si une Granularity donnée fait partit de la collection de celles utilisant l'Axis.
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
     * Supprime une Granularity de la collection de celle utilisant l'Axis.
     *
     * @param Orga_Model_Granularity $granularity
     */
    public function removeGranularity($granularity)
    {
        if ($this->hasGranularity($granularity)) {
            $this->granularities->removeElement($granularity);
            $granularity->removeAxis($this);
        }
    }

    /**
     * Vérifie que l'Axis possède au moins une Granularity.
     *
     * @return bool
     */
    public function hasGranularities()
    {
        return !$this->granularities->isEmpty();
    }

    /**
     * Renvoie toute les Granularity utilisant l'Axis courant.
     *
     * @return Orga_Model_Granularity[]
     */
    public function getGranularities()
    {
        return $this->granularities->toArray();
    }

    /**
     * Vérifie si l'Axis courant est narrower de l'Axis donné.
     *
     * @param Orga_Model_Axis $axis
     *
     * @return bool
     */
    public function isNarrowerThan($axis)
    {
        $directNarrower = $axis->getDirectNarrower();
        return (($this == $directNarrower) || ((null !== $directNarrower) && $this->isNarrowerThan($directNarrower)));
    }

    /**
     * Vérifie si l'Axis courant est broader de l'Axis donné.
     *
     * @param Orga_Model_Axis $axis
     *
     * @return bool
     */
    public function isBroaderThan($axis)
    {
        return $axis->isNarrowerThan($this);
    }

    /**
     * Vérifie si l'Axis courant n'est ni narrower ni broader d'un des Axis donnés.
     *
     * @param Orga_Model_Axis[] $axes
     *
     * @return bool
     */
    public function isTransverse($axes)
    {
        foreach ($axes as $axis) {
            if ($axis->isBroaderThan($this) ||  $this->isBroaderThan($axis) || ($this === $axis)) {
                return false;
            }
        }
        return true;
    }

}