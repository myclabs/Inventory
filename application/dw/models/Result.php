<?php
/**
 * Classe DW_Model_Result
 * @author valentin.claras
 * @author cyril.perraud
 * @package    DW
 * @subpackage Model
 */

/**
 * Definit une cellule organisationnelle.
 * @package    DW
 * @subpackage Model
 */
class DW_Model_Result extends Core_Model_Entity
{
    // Constantes de tris et de filtres.
    const QUERY_CUBE = 'cube';
    const QUERY_INDICATOR = 'indicator';


    /**
     * Identifiant uniqu de le Result.
     *
     * @var int
     */
    protected $id;

    /**
     * Cube à laquelle appartient le Result.
     *
     * @var DW_Model_Cube
     */
    protected $cube;

    /**
     * Indicator utilisé par le Result.
     *
     * @var DW_Model_Indicator
     */
    protected $indicator;

    /**
     * Collection des Member indexant le Result.
     *
     * @var Doctrine\Common\Collections\ArrayCollection
     */
    protected $members = array();

    /**
     * Calc_Value du Result.
     *
     * @var Calc_Value
     */
    protected $value = null;


    /**
     * Constructeur de la classe Result.
     */
    public function __construct(DW_Model_Indicator $indicator)
    {
        $this->members = new Doctrine\Common\Collections\ArrayCollection();

        $this->cube = $indicator->getCube();
        $this->indicator = $indicator;
    }

    /**
     * Charge une Result en fonction de sa Cube et de ses Member.
     *
     * @param DW_Model_Cube $cube
     * @param DW_Model_Members[] $listMembers
     *
     * @return DW_Model_Result
     */
    public static function loadByCubeAndListMembers($cube, $listMembers)
    {
        $listArrayMembers = array();
        foreach($listMembers as $member) {
            $listArrayMembers[] = array($member);
        }
        $members = array(
            'cube' => $cube,
            'members'     => $listArrayMembers
        );

        return self::getEntityRepository()->loadOneByMembers($members);
    }

    /**
     * Renvoie la Cube de le Result.
     *
     * @return DW_Model_Cube
     */
    public function getCube()
    {
        return $this->cube;
    }

    /**
     * Ajoute un Member à ceux indexant le Result.
     *
     * @param DW_Model_Member $member
     */
    public function addMember(DW_Model_Member $member)
    {
        if (!($this->hasMember($member))) {
            $this->members->add($member);
        }
    }

    /**
     * Vérifie si le Member donné indexe le Result.
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
     * Supprime le Member donné de ceux indexant le Result.
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
     * Vérifie que le Result possède au moir un Member.
     *
     * @return bool
     */
    public function hasMembers()
    {
        return !$this->members->isEmpty();
    }

    /**
     * Renvoi tous les Member indexant le Result.
     *
     * @return DW_Model_Member[]
     */
    public function getMembers()
    {
        return $this->members->toArray();
    }

    /**
     * Renvoie le Member possédé par l'Axis donné.
     *
     * @param DW_Model_Axis $axis
     *
     * @return DW_Model_Member
     */
    public function getMemberForAxis($axis)
    {
        foreach ($this->members as $member) {
            if ($member->getAxis() === $axis) {
                return $member;
            }
        }
        return null;
    }

    /**
     * Renvoie la Indicator de le Result.
     *
     * @return DW_Model_Indicator
     */
    public function getIndicator()
    {
        return $this->indicator;
    }

    /**
     * Définit la valeur du Result.
     *
     * @param Calc_Value $value
     */
    public function setValue(Calc_Value $value)
    {
        $this->value = $value;
    }

    /**
     * Renvoie la Indicator de le Result.
     *
     * @return Calc_Value
     */
    public function getValue()
    {
        return $this->value;
    }
    
}