<?php
/**
 * Classe DW_Model_Axis
 * @author valentin.claras
 * @author cyril.perraud
 * @package    DW
 * @subpackage Model
 */

use Core\Translation\TranslatedString;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

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
     * @var TranslatedString
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
     * @var Collection|DW_Model_Axis[]
     */
    protected $directBroaders = null;

    /**
     * Collection des Member de l'Axis courant.
     *
     * @var Collection|DW_Model_Member[]
     */
    protected $members = null;


    /**
     * Constructeur de la classe Axis.
     */
    public function __construct(DW_Model_Cube $cube)
    {
        $this->label = new TranslatedString();
        $this->directBroaders = new ArrayCollection();
        $this->members = new ArrayCollection();

        $this->cube = $cube;
        $this->cube->addAxis($this);
        $this->setPosition();
    }

    /**
     * Renvoi les valeurs du contexte pour l'objet.
     * .
     * @return array
     */
    protected function getContext()
    {
        return array('cube' => $this->cube, 'directNarrower' => $this->directNarrower);
    }

    /**
     * Fonction appelée avant un update de l'objet (défini dans le mapper).
     */
    public function preUpdate()
    {
        $this->checkHasPosition();
    }

    /**
     * Fonction appelée avant un delete de l'objet (défini dans le mapper).
     */
    public function preDelete()
    {
        $this->deletePosition();
    }

    /**
     * Fonction appelée après un load de l'objet (défini dans le mapper).
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
    public static function loadByRefAndCube($ref, DW_Model_Cube $cube)
    {
        return $cube->getAxisByRef($ref);
    }

    /**
     * Renvoie l'id de l'Axis.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Définit la référence de l'axe..
     *
     * @param string $ref
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
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
     * @param TranslatedString $label
     */
    public function setLabel(TranslatedString $label)
    {
        $this->label = $label;
    }

    /**
     * Renvoie le label de l'axe.
     *
     * @return TranslatedString
     */
    public function getLabel()
    {
        return $this->label;
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
     * @return DW_Model_Axis
     */
    public function getDirectNarrower()
    {
        return $this->directNarrower;
    }

    /**
     * @return DW_Model_Axis[]
     */
    public function getAllNarrowers()
    {
        $narrowers = [];
        $axis = $this;
        while ($axis->getDirectNarrower() !== null) {
            $narrowers[] = $axis->getDirectNarrower();
            $axis = $axis->getDirectNarrower();
        }
        return $narrowers;
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
     * @return DW_Model_Axis[]
     */
    public function getAllBroadersFirstOrdered()
    {
        $broaders = [];
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
        $broaders = [];
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
     *
     * @throws Core_Exception_InvalidArgument
     */
    public function addMember(DW_Model_Member $member)
    {
        if ($member->getAxis() !== $this) {
            throw new Core_Exception_InvalidArgument();
        }

        if (!$this->hasMember($member)) {
            $this->members->add($member);
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
     * Retourne un tableau contenant les members de l'Axis.
     *
     * @param string $ref
     *
     * @throws Core_Exception_NotFound
     * @throws Core_Exception_TooMany
     *
     * @return DW_Model_Member
     */
    public function getMemberByRef($ref)
    {
        $criteria = \Doctrine\Common\Collections\Criteria::create();
        $criteria->where($criteria->expr()->eq('ref', $ref));
        $member = $this->members->matching($criteria)->toArray();

        if (empty($member)) {
            throw new Core_Exception_NotFound("No 'DW_Model_Member' matching " . $ref);
        } else {
            if (count($member) > 1) {
                throw new Core_Exception_TooMany("Too many 'DW_Model_Member' matching " . $ref);
            }
        }

        return array_pop($member);
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
