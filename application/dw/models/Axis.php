<?php
/**
 * Classe DW_Model_Axis
 * @author valentin.claras
 * @author cyril.perraud
 * @package    DW
 * @subpackage Model
 */
use Doctrine\Common\Collections\Criteria;

/**
 * Objet métier définissant un axe organisationnel au sein d'un cube.
 * @package    DW
 * @subpackage Model
 */
class DW_Model_Axis extends Core_Model_Entity
{
    use Core_Strategy_Ordered;

    // Constantes de tris et de filtres.
    const QUERY_REF = 'ref';
    const QUERY_LABEL = 'label';
    const QUERY_POSITION = 'position';
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
     * @var DW_Model_Cube
     */
    protected $cube = null;

    /**
     * Axe narrower de l'axe courant.
     *
     * @var DW_Model_Axis
     */
    protected $directNarrower = null;

    /**
     * Collection des Axis broader de l'axe courant.
     *
     * @var Doctrine\Common\Collections\ArrayCollection
     */
    protected $directBroaders = null;

    /**
     * Collection des Member de l'Axis courant.
     *
     * @var Doctrine\Common\Collections\ArrayCollection
     */
    protected $members = null;


    /**
     * Constructeur de la classe Axis.
     */
    public function __construct()
    {
        $this->directBroaders = new Doctrine\Common\Collections\ArrayCollection();
        $this->members = new Doctrine\Common\Collections\ArrayCollection();
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
     * @param DW_Model_Cube $cube
     *
     * @return DW_Model_Axis
     */
    public static function loadByRefAndCube($ref, $cube)
    {
        return self::getEntityRepository()->loadBy(array('ref' => $ref, 'cube' => $cube));
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
     * @param DW_Model_Cube $cube
     */
    public function setCube(DW_Model_Cube $cube=null)
    {
        if ($this->cube !== $cube) {
            if ($this->cube !== null) {
                throw new Core_Exception_TooMany('Cube already set, an axis cannot be move.');
            }
            $this->cube = $cube;
            $cube->addAxis($this);
        }
    }

    /**
     * Renvoie le cube de l'axe.
     *
     * @return DW_Model_Cube
     */
    public function getCube()
    {
        return $this->cube;
    }

    /**
     * Définit l'axe narrower de l'axe courant.
     *
     * @param DW_Model_Axis $narrowerAxis
     */
    public function setDirectNarrower(DW_Model_Axis $narrowerAxis=null)
    {
        if ($this->directNarrower !== $narrowerAxis) {
            if ($this->position !== null) {
                $this->deletePosition();
            }
            $this->directNarrower = $narrowerAxis;
            if ($this->directNarrower !== null) {
                $this->setPosition();
            }
            $narrowerAxis->addDirectBroader($this);
        }
    }

    /**
     * Renvoie l'axe narrower de l'axe courant.
     *
     * @return DW_Model_Axis
     */
    public function getDirectNarrower()
    {
        return $this->directNarrower;
    }

    /**
     * Ajoute un Axis broader à l'axe courant.
     *
     * @param DW_Model_Axis $broaderAxis
     */
    public function addDirectBroader(DW_Model_Axis $broaderAxis)
    {
        if (!($this->hasDirectBroader($broaderAxis))) {
            $this->directBroaders->add($broaderAxis);
            $broaderAxis->setDirectNarrower($this);
        }
    }

    /**
     * Vérifie si un Axis donné est un broader de l'axe courant.
     *
     * @param DW_Model_Axis $broaderAxis
     *
     * @return boolean
     */
    public function hasDirectBroader(DW_Model_Axis $broaderAxis)
    {
        return $this->directBroaders->contains($broaderAxis);
    }

    /**
     * Retire l'axe donnés .
     *
     * @param DW_Model_Axis $broaderAxis
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
     * @return DW_Model_Axis[]
     */
    public function getDirectBroaders()
    {
        return $this->directBroaders->toArray();
    }

    /**
     * Retourne récursivement, tous les broaders de l'Axis dans l'ordre de première exploration.
     *
     * @return DW_Model_Axis[]
     */
    public function getAllBroadersFirstOrdered()
    {
        $broaders = array();
        foreach ($this->directBroaders as $directBroader) {
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
     * @return DW_Model_Axis[]
     */
    public function getAllBroadersLastOrdered()
    {
        $broaders = array();
        foreach ($this->directBroaders as $directBroader) {
            foreach ($directBroader->getAllBroadersLastOrdered() as $recursiveBroader) {
                $broaders[] = $recursiveBroader;
            }
            $broaders[] = $directBroader;
        }
        return $broaders;
    }

    /**
     * Ajoute une Member à l'Axis.
     *
     * @param DW_Model_Member $member
     */
    public function addMember(DW_Model_Member $member)
    {
        if (!($this->hasMember($member))) {
            $this->members->add($member);
            $member->setAxis($this);
        }
    }

    /**
     * Vérifie si le Member passé fait partie de l'Axis.
     *
     * @param DW_Model_Member $member
     *
     * @return boolean
     */
    public function hasMember(DW_Model_Member $member)
    {
        return $this->members->contains($member);
    }

    /**
     * Supprime le Member donné de la collection de l'Axis.
     *
     * @param DW_Model_Member $member
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
     * @return DW_Model_Member[]
     */
    public function getMembers()
    {
        return $this->members->toArray();
    }

    /**
     * @param string $ref
     * @throws Core_Exception_NotFound
     * @return DW_Model_Axis
     */
    public function getMemberByRef($ref)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('ref', $ref));
        $results = $this->members->matching($criteria);
        if (count($results) > 0) {
            return $results->first();
        }
        throw new Core_Exception_NotFound("Le membre $ref est introuvable dans l'axe " . $this->ref);
    }

    /**
     * Vérifie si l'Axis courant est narrower de l'Axis donné.
     *
     * @param DW_Model_Axis $axis
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
     * @param DW_Model_Axis $axis
     *
     * @return bool
     */
    public function isBroaderThan($axis)
    {
        return $axis->isNarrowerThan($this);
    }

    /**
     * Vérifie si l'Axis courant n'est ni narrower ni broader de l'Axis donné.
     *
     * @param DW_Model_Axis $axis
     *
     * @return bool
     */
    public function isTransverseWith($axis)
    {
        if ($axis->isBroaderThan($this) ||  $this->isBroaderThan($axis) || ($axis === $this)) {
            return false;
        }
        return true;
    }

}