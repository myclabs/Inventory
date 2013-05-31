<?php
/**
 * Classe Orga_Model_Axis
 * @author valentin.claras
 * @author diana.dragusin
 * @package    Orga
 * @subpackage Model
 */
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Objet métier définissant un axe organisationnel au sein d'un project.
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
    const QUERY_NARROWER = 'directNarrower';
    const QUERY_PROJECT = 'project';


    /**
     * Identifiant uniqe de l'axe.
     *
     * @var int
     */
    protected  $id = null;

    /**
     * Référence unique (au sein d'un project) de l'axe.
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
     * Project contenant l'axe.
     *
     * @var Orga_Model_Project
     */
    protected $project = null;

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
     *
     * @param Orga_Model_Project $project
     */
    public function __construct(Orga_Model_Project $project)
    {
        $this->directBroaders = new ArrayCollection();
        $this->members = new ArrayCollection();
        $this->granularities = new ArrayCollection();

        $this->project = $project;
        $this->setPosition();
        $project->addAxis($this);
    }

    /**
     * Renvoi les valeurs du contexte pour l'objet.
     * .
     * @return array
     */
    protected function getContext()
    {
        return array('project' => $this->project, 'directNarrower' => $this->directNarrower);
    }

    /**
     * Fonction appelé avant un delete de l'objet (défini dans le mapper).
     */
    public function preDelete()
    {
        $this->deletePosition();
    }

    /**
     * Fonction appelé après un load de l'objet (défini dans le mapper).
     */
    public function postLoad()
    {
        $this->updateCachePosition();
    }

    /**
     * Charge un Axis en fonction de sa référence et son project.
     *
     * @param string $ref
     * @param Orga_Model_Project $project
     *
     * @return Orga_Model_Axis
     */
    public static function loadByRefAndProject($ref, $project)
    {
        return $project->getAxisByRef($ref);
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
     * Renvoie le project de l'axe.
     *
     * @return Orga_Model_Project
     */
    public function getProject()
    {
        return $this->project;
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

        $this->getProject()->orderGranularities();
    }

    /**
     * Permet une surcharge facile pour lancer des évents après qu'un objet ait été déplacé.
     */
    protected function hasMove()
    {
        $this->updateMembersAndCellsHashKey();
    }

    /**
     * Renvoie la position globale de l'axe.
     *
     * @return int
     */
    public function getGlobalPosition()
    {
        return $this->getProject()->getAxisGlobalPosition($this);
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
            $broaderAxis->setDirectNarrower();
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

        uasort(
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
     *
     * @throws Core_Exception_InvalidArgument
     */
    public function addMember(Orga_Model_Member $member)
    {
        if ($member->getAxis() !== $this) {
            throw new Core_Exception_InvalidArgument();
        }

        if (!$this->hasMember($member)) {
            $this->members->add($member);
            foreach ($this->getGranularities() as $granularity) {
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
     * Retourne un tableau contenant les members de l'Axis.
     *
     * @param string $completeRef
     *
     * @throws Core_Exception_NotFound
     * @throws Core_Exception_TooMany
     *
     * @return Orga_Model_Member[]
     */
    public function getMemberByCompleteRef($completeRef)
    {
        $criteria = \Doctrine\Common\Collections\Criteria::create();
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
     * Supprime le Member donné de la collection de l'Axis.
     *
     * @param Orga_Model_Member $member
     */
    public function removeMember(Orga_Model_Member $member)
    {
        if ($this->hasMember($member)) {
            $this->members->removeElement($member);
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
     *
     * @throws Core_Exception_InvalidArgument
     */
    public function addGranularity(Orga_Model_Granularity $granularity)
    {
        if (!$granularity->hasAxis($this)) {
            throw new Core_Exception_InvalidArgument();
        }

        if (!$this->hasGranularity($granularity)) {
            $this->granularities->add($granularity);
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

    /**
     * @return string Représentation textuelle de l'Axis
     */
    public function __toString()
    {
        return $this->getRef();
    }

}