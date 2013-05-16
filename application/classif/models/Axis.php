<?php
/**
 * Classe Classif_Model_Axis
 * @author     valentin.claras
 * @author     simon.rieu
 * @package    Classif
 * @subpackage Model
 */

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Un axe contenant des membres.
 *
 * @package    Classif
 * @subpackage Model
 */
class Classif_Model_Axis extends Core_Model_Entity
{

    use Core_Strategy_Ordered;

    // Constantes de tris et de filtres.
    const QUERY_REF = 'ref';
    const QUERY_LABEL = 'label';
    const QUERY_POSITION = 'position';
    const QUERY_NARROWER = 'directNarrower';


    /**
     * Identifiant unique de l'Axis.
     *
     * @var int $id
     */
    protected $id;

    /**
     * Ref unique de l'Axis.
     *
     * @var int $ref
     */
    protected $ref;

    /**
     * Label de l'Axis.
     *
     * @var string
     */
    protected $label;

    /**
     * Axis narrower.
     *
     * @var Classif_Model_Axis
     */
    protected $directNarrower;

    /**
     * Collection des Axis broaders.
     *
     * @var Collection|Classif_Model_Axis[]
     */
    protected $directBroaders;

    /**
     * Collection des Member de l'Axis.
     *
     * @var Collection|Classif_Model_Member[]
     */
    protected $members;


    /**
     * Constructeur de la classe Axis.
     */
    public function __construct()
    {
        $this->directBroaders = new ArrayCollection();
        $this->members = new ArrayCollection();
    }

    /**
     * Renvoi les valeurs du contexte pour l'objet.
     * .
     * @return array
     */
    protected function getContext()
    {
        return array('directNarrower' => $this->directNarrower);
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
     * Permet de charger un Axis par son ref.
     *
     * @param string $ref
     *
     * @return Classif_Model_Axis $axis
     */
    public static function loadByRef($ref)
    {
        return self::getEntityRepository()->loadBy(array('ref' => $ref));
    }

    /**
     * Charge l'ensemble des axes dans l'ordre de parcours récursif dernière visite.
     *
     * @return Classif_Model_Axis[]
     */
    public static function loadListOrderedAsAscendantTree()
    {
        $axes = array();

        $queryRoots = new Core_Model_Query();
        $queryRoots->filter->addCondition(self::QUERY_NARROWER, null, Core_Model_Filter::OPERATOR_NULL);
        foreach (Classif_Model_Axis::loadList($queryRoots) as $rootAxis) {
            foreach ($rootAxis->getAllBroaders() as $recursiveBroader) {
                $axes[] = $recursiveBroader;
            }
            $axes[] = $rootAxis;
        }

        return $axes;
    }

    /**
     * Modifie la référence de l'Axis.
     *
     * @param String $ref
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
    }

    /**
     * Retourne la référence de l'Axis.
     *
     * @return String
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Modifie le label de l'Axis.
     *
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Retourne le label de l'Axis.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Modifie le narrower de l'Axis.
     *
     * @param Classif_Model_Axis|null $narrowerAxis
     */
    public function setDirectNarrower(Classif_Model_Axis $narrowerAxis = null)
    {
        if ($this->directNarrower !== $narrowerAxis) {
            if ($this->directNarrower !== null) {
                foreach ($this->members as $member) {
                    foreach ($member->getDirectChildren() as $childMember) {
                        if ($childMember->getAxis() === $this->directNarrower) {
                            $member->removeDirectChild($childMember);
                        }
                    }
                }
                $this->directNarrower->removeDirectBroader($this);
            }
            $this->deletePosition();
            $this->directNarrower = $narrowerAxis;
            $this->setPosition();
            if ($narrowerAxis !== null) {
                $narrowerAxis->addDirectBroader($this);
            }
        }
    }

    /**
     * Retourne le narrower de l'Axis.
     *
     * @return Classif_Model_Axis
     */
    public function getDirectNarrower()
    {
        return $this->directNarrower;
    }

    /**
     * Ajoute un Axis donné aux broaders directs de l'Axis.
     *
     * @param Classif_Model_Axis $broaderAxis
     */
    public function addDirectBroader(Classif_Model_Axis $broaderAxis)
    {
        if (!($this->hasDirectBroader($broaderAxis))) {
            $this->directBroaders->add($broaderAxis);
            $broaderAxis->setDirectNarrower($this);
        }
    }

    /**
     * Vérifie si l'Axis donné est bien broader direct de l'Axis.
     *
     * @param Classif_Model_Axis $broaderAxis
     *
     * @return boolean
     */
    public function hasDirectBroader(Classif_Model_Axis $broaderAxis)
    {
        return $this->directBroaders->contains($broaderAxis);
    }

    /**
     * Supprime l'Axis donné des broaders directs de l'Axis.
     *
     * @param Classif_Model_Axis $broaderAxis
     */
    public function removeDirectBroader(Classif_Model_Axis $broaderAxis)
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
        return (count($this->directBroaders) > 0) ? true : false;
    }

    /**
     * Retourne l'ensemble des broaders directs de l'Axis.
     *
     * @return Classif_Model_Axis[]
     */
    public function getDirectBroaders()
    {
        return $this->directBroaders->toArray();
    }

    /**
     * Retourne récursivement, tous les broaders de l'Axis.
     *
     * @return Classif_Model_Axis[]
     */
    public function getAllBroaders()
    {
        $broaders = array();
        foreach ($this->directBroaders as $directBroader) {
            foreach ($directBroader->getAllBroaders() as $recursiveBroader) {
                $broaders[] = $recursiveBroader;
            }
            $broaders[] = $directBroader;
        }
        return $broaders;
    }

    /**
     * Vérifie si l'Axis courant est narrower de l'Axis donné.
     *
     * @param Classif_Model_Axis $axis
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
     * @param Classif_Model_Axis $axis
     *
     * @return bool
     */
    public function isBroaderThan($axis)
    {
        return $axis->isNarrowerThan($this);
    }

    /**
     * Ajoute une Member à l'Axis.
     *
     * @param Classif_Model_Member $member
     */
    public function addMember(Classif_Model_Member $member)
    {
        if (!($this->hasMember($member))) {
            $this->members->add($member);
            $member->setAxis($this);
        }
    }

    /**
     * Vérifie si le Member passé fait partie de l'Axis.
     *
     * @param Classif_Model_Member $member
     *
     * @return boolean
     */
    public function hasMember(Classif_Model_Member $member)
    {
        return $this->members->contains($member);
    }

    /**
     * Supprime le Member passé de la collection de l'Axis.
     *
     * @param Classif_Model_Member $member
     */
    public function removeMember($member)
    {
        if ($this->hasMember($member)) {
            $this->members->removeElement($member);
            $member->setAxis(null);
        }
    }

    /**
     * Indique si l'Axis possède des Member.
     *
     * @return bool
     */
    public function hasMembers()
    {
        return (count($this->members) > 0) ? true : false;
    }

    /**
     * Retourne un tableau contenant les members de l'axe
     * @return Classif_Model_Member[]
     */
    public function getMembers()
    {
        return $this->members->toArray();
    }

    /**
     * Indique si l'Axis est racie, c'est à dire si il n'a pas de narrower.
     *
     * @return bool
     */
    public function isRoot()
    {
        return ($this->directNarrower === null) ? true : false;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

}
