<?php
use User\Domain\User;

/**
 * @package Simulation
 * @subpackage Model
 */
/**
 * Classe regroupant l'ensemble des simulation d'un utilisateur pour AF donné.
 * @author valentin.claras
 * @package Simulation
 * @subpackage ModelProvider
 */
class Simulation_Model_Set extends Core_Model_Entity
{
    /**
     * Indique un filtre sur l'utilisateur du Set.
     *
     * @var const
     */
    const QUERY_USER = 'user';


    /**
     * Identifiant unique du jeu de Simulations.
     *
     * @var int
     */
    protected $id = null;

    /**
     * Label du jeu de Simulations.
     *
     * @var string
     */
    protected $label = '';

    /**
     * Formulaire sur lequel porte les Simulations.
     *
     * @var AF_Model_AF
     */
    protected $aF = null;

    /**
     * Collection des Simulations du Set.
     *
     * @var Doctrine\Common\Collections\ArrayCollection
     */
    protected $scenarios = null;

    /**
     * Cube peuplé par les Simulations.
     *
     * @var DW_Model_Cube
     */
    protected $dWCube = null;

    /**
     * Axis de DW correspondant au Set.
     *
     * @var DW_Model_Axis
     */
    protected $dWAxis = null;

    /**
     * Utilisateur effectuant les Simulations.
     *
     * @var User
     */
    protected $user = null;


    /**
     * Constructeur de la classe Set.
     */
    public function __construct()
    {
        $this->scenarios = new  Doctrine\Common\Collections\ArrayCollection();

        $this->createDWCube();
    }

    /**
     * Charge le Set correspondant à un Cube de DW.
     *
     * @param DW_Model_Cube $dWCube
     *
     * @return Simulation_Model_Set
     */
    public static function loadByDWCube($dWCube)
    {
        return self::getEntityRepository()->loadBy(array('dWCube' => $dWCube));
    }

    /**
     * Créé le Cube pour le set.
     */
    protected function createDWCube()
    {
        $this->dWCube = new DW_Model_Cube();
        $this->dWCube->setLabel($this->label);
        $this->dWAxis = new DW_Model_Axis($this->dWCube);
        $this->dWAxis->setRef('set');
        $this->dWAxis->setLabel(__('Simulation', 'name', 'scenario'));

        $container = \Core\ContainerSingleton::getContainer();
        /** @var Simulation_Service_ETLStructure $etlStructureService */
        $etlStructureService = $container->get(Simulation_Service_ETLStructure::class);

        $etlStructureService->populateSetDWCube($this);
    }

    /**
     * Renvoie l'instance du Cube remplis par le jeu de simulations.
     *
     * @return DW_Model_Cube
     */
    public function getDWCube()
    {
        return $this->dWCube;
    }

    /**
     * Renvoie l'instance de l'Axis correspondant au jeu de simulations.
     *
     * @return DW_Model_Axis
     */
    public function getDWAxis()
    {
        return $this->dWAxis;
    }

    /**
     * Spécifie le label du set.
     *
     * @param string $label
     */
    public function setLabel($label)
    {
        if (!(is_string($label))) {
            throw new Core_Exception_InvalidArgument(
                'Le label d\'un Set doit être une chaîne.'
            );
        }
        $this->label = $label;
        if ($this->dWCube !== null) {
            $this->dWCube->setLabel($this->label);
        }
    }

    /**
     * Renvoie le label textuel du set.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Spécifie le PrimarySet du Set.
     *
     * @param AF_Model_AF $aF
     */
    public function setAF(AF_Model_AF $aF)
    {
        if ($this->aF !== $aF) {
            if ($this->aF !== null) {
                throw new Core_Exception_Duplicate('Impossible de redéfinir l\'AF, il a déjà été défini.');
            }
            $this->aF = $aF;
        }
    }

    /**
     * Renvoie l'AF concerné par le jeu de simulations.
     *
     * @return AF_Model_AF
     */
    public function getAF()
    {
        if ($this->aF === null) {
            throw new Core_Exception_UndefinedAttribute('L\'AF n\'a pas été défini.');
        }
        return $this->aF;
    }

    /**
     * Spécifie le PrimarySet du jeu de simulation.
     * @param User $user
     */
    public function setUser(User $user)
    {
        if ($this->user !== $user) {
            if ($this->user !== null) {
                throw new Core_Exception_Duplicate('Impossible de redéfinir le User, il a déjà été défini.');
            }
            $this->user = $user;
        }
    }

    /**
     * Renvoie l'User du Set.
     * @return User
     */
    public function getUser()
    {
        if ($this->user === null) {
            throw new Core_Exception_UndefinedAttribute('Le User n\'a pas été défini.');
        }
        return $this->user;
    }

    /**
     * Vérifie si le Set possède le Scenario donné.
     *
     * @param Simulation_Model_Scenario $scenario
     * @return bool
     */
    public function hasScenario(Simulation_Model_Scenario $scenario)
    {
        return $this->scenarios->contains($scenario);
    }

    /**
     * Ajoute un Scenario au Set.
     *
     * @param Simulation_Model_Scenario $scenario
     */
    public function addScenario(Simulation_Model_Scenario $scenario)
    {
        if (!$this->hasScenario($scenario)) {
            $this->scenarios->add($scenario);
            try {
                $set = $scenario->getSet();
            } catch (Core_Exception_UndefinedAttribute $e) {
                $scenario->setSet($this);
            }
        }
    }

    /**
     * Retire un scenario du Set.
     *
     * @param Simulation_Model_Scenario $scenario
     */
    public function deleteScenario(Simulation_Model_Scenario $scenario)
    {
        if ($this->hasScenario($scenario)) {
            $this->scenarios->removeElement($scenario);
            $scenario->delete();
        }
    }

    /**
     * Renvoi l'ensemble des Scenarios du Set.
     *
     * @return Simulation_Model_Scenario[]
     */
    public function getScenarios()
    {
        return $this->scenarios->toArray();
    }
}
