<?php
/**
 * @package Simulation
 * @subpackage ModelProvider
 */
/**
 * Classe gerant l'association entre une saisie de simulation et un set de simulaitons.
 * @author valentin.claras
 * @package Simulation
 * @subpackage ModelProvider
 */
class Simulation_Model_Scenario extends Core_Model_Entity
{
    // Constantes de tri et de filtre.
    const QUERY_SET = 'set';

    /**
     * Identifiant unique de la Simulation.
     *
     * @var int
     */
    protected $id = null;

    /**
     * Label du Scenario.
     *
     * @var string
     */
    protected $label = '';

    /**
     * Identifiant du jeu de simulations.
     *
     * @var Simulation_Model_Set
     */
    protected $set = null;

    /**
     * Member de DW correspondant à la simulation.
     *
     * @var DW_Model_Member
     */
    protected $dWMember = null;

    /**
     * Identifiant unique de la saisie.
     *
     * @var AF_Model_InputSet_Primary
     */
    protected $aFInputSetPrimary = null;

    /**
     * Résultats créés par le primarySet.
     * @var Doctrine\Common\Collections\ArrayCollection
     */
    protected $dWResults = array();


    /**
     * Charge un scenario à partir du primarySet.
     *
     * @param AF_Model_AF $aFInputSetPrimary
     *
     * @return Simulation_Model_Scenario
     */
    public static function loadByAFInputSetPrimary($aFInputSetPrimary)
    {
        return self::getEntityRepository()->loadBy(array('aFInputSetPrimary' => $aFInputSetPrimary));
    }

    /**
     * Spécifie le label de la simulation.
     * @param string $label
     */
    public function setLabel($label)
    {
        if (!(is_string($label))) {
            throw new Core_Exception_InvalidArgument(
                'Le label d\'une Simulation doit être une chaîne. #captainObvious'
            );
        }
        $this->label = $label;
        if ($this->dWMember !== null) {
            $this->dWMember->setRef(Core_Tools::refactor($this->label));
            $this->dWMember->setLabel($this->label);
        }
    }

    /**
     * Renvoie le référant textuel de la simulation.
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Spécifie l'InputSetPrimary du scénario.
     *
     * @param Simulation_Model_Set $set
     */
    public function setSet(Simulation_Model_Set $set)
    {
        if ($this->set !== $set) {
            if ($this->set !== null) {
                throw new Core_Exception_Duplicate(
                    'Impossible de redéfinir le Set, il a déjà été défini.'
                );
            }
            $this->set = $set;
            if (!($set->hasScenario($this))) {
                $set->addScenario($this);
            }
            $this->createAssociatedDWMember();
        }
    }

    /**
     * Créer le membre de DW correspondant à la simulation.
     */
    protected function createAssociatedDWMember()
    {
        $this->dWMember = new DW_Model_Member();
        $this->dWMember->setRef(Core_Tools::refactor($this->label));
        $this->dWMember->setLabel($this->label);
        $this->dWMember->setAxis($this->getSet()->getDWAxis());
    }

    /**
     * Renvoie le Set associé au scénario.
     *
     * @return Simulation_Model_Set
     */
    public function getSet()
    {
        if ($this->set === null) {
            throw new Core_Exception_UndefinedAttribute(
                'Le Set n\'a pas été défini.'
            );
        }
        return $this->set;
    }

    /**
     * Spécifie l'InputSetPrimary du scenario.
     *
     * @param AF_Model_InputSet_Primary $aFInputSetPrimary
     */
    public function setAFInputSetPrimary(AF_Model_InputSet_Primary $aFInputSetPrimary)
    {
        if ($this->aFInputSetPrimary !== $aFInputSetPrimary) {
            if ($this->aFInputSetPrimary !== null) {
                throw new Core_Exception_Duplicate(
                    'Impossible de redéfinir l\'InputSetPrimary, il a déjà été défini.'
                );
            }
            $this->aFInputSetPrimary = $aFInputSetPrimary;
        }
    }

    /**
     * Renvoie l'InputSetPrimary associé au scenario.
     *
     * @return AF_Model_InputSet_Primary
     */
    public function getAFInputSetPrimary()
    {
        if ($this->aFInputSetPrimary === null) {
            throw new Core_Exception_UndefinedAttribute(
                'L\'InputSetPrimary n\'a pas été défini.'
            );
        }
        return $this->aFInputSetPrimary;
    }

    /**
     * (non-PHPdoc)
     * @see Simulation_ETLDataProvider::getDWCubesDestinationForETLData()
     */
    public function getDWCubesDestinationForETLData()
    {
        return array($this->getSet()->getDWCube());
    }

    /**
     * (non-PHPdoc)
     * @see Simulation_ETLDataProvider::getOrgaMembersIndexingETLData()
     */
    public function getCommonIndexingForETLData()
    {
        return array('set' => $this->getKey());
    }

    /**
     * (non-PHPdoc)
     * @see Simulation_ETLDataProvider::addETLDataSource()
     * @param AF_Model_InputSet_Primary $source
     */
    public function addETLDataSource(AF_Model_InputSet_Primary $source)
    {
        $this->setAFInputSetPrimary($source);
    }

    /**
     * (non-PHPdoc)
     * @see Simulation_ETLDataProvider::deleteETLDataSource()
     * @param AF_Model_InputSet_Primary $source
     */
    public function deleteETLDataSource(AF_Model_InputSet_Primary $source)
    {
        $this->aFInputSetPrimary = null;
    }

    /**
     * Créer les Result de DW à partir de l'InputSetPrimary du Scenario.
     *
     * @param AF_Model_Output_Element $output
     */
    public function createDWResults()
    {
        if (($this->aFInputSetPrimary === null) || ($this->aFInputSetPrimary->getOutputSet() === null)) {
            return;
        }

        foreach ($this->getAFInputSetPrimary()->getOutputSet()->getElements() as $outputElement) {
            $this->createDWResult($outputElement);
        }
    }

    /**
     * Créer un Result de DW et l'ajout à un cube à partir d'un Output d'AF.
     *
     * @param AF_Model_Output_Element $output
     */
    protected  function createDWResult(AF_Model_Output_Element $output)
    {
        $dWCube = $this->getSet()->getDWCube();
        $refClassifIndicator = $output->getContextIndicator()->getIndicator()->getRef();
        $dWIndicator = DW_Model_Indicator::loadByRefAndCube($refClassifIndicator, $dWCube);

        $dWResult = new DW_Model_Result();
        $dWResult->setCube($dWCube);
        $dWResult->setIndicator($dWIndicator);
        $dWResult->setValue($output->getValue());

        $dWResult->addMember($this->dWMember);

        foreach ($output->getIndexes() as $outputIndex) {
            $dWAxis = DW_Model_Axis::loadByRefAndCube($outputIndex->getRefAxis(), $dWCube);
            $dWMember = DW_Model_Member::loadByRefAndAxis($outputIndex->getRefMember(), $dWAxis);
            $dWResult->addMember($dWMember);

            foreach ($outputIndex->getMember()->getAllParents() as $classifParentMember) {
                $dWBroaderAxis = DW_Model_Axis::loadByRefAndCube($classifParentMember->getAxis()->getRef(), $dWCube);
                $dWParentMember = DW_Model_Member::loadByRefAndAxis($classifParentMember->getRef(), $dWBroaderAxis);
                $dWResult->addMember($dWParentMember);
            }
        }

        $this->dWResults->add($dWResult);
    }

    /**
     * Supprime l'ensemble des résultats du primarySet dans DW.
     */
    public function deleteDWResults()
    {
        $this->dWResults->clear();
    }
}