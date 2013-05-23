<?php
/**
 * @author  matthieu.napoli
 * @author  thibaud.rolland
 * @author  hugo.charbonnier
 * @author  yoann.croizer
 * @package AF
 */

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Model de AF.
 * @package AF
 */
class AF_Model_AF extends Core_Model_Entity
{

    use Core_Strategy_Ordered;
    use Core_Model_Entity_Translatable;

    const ALGO_MAIN_REF = 'main';

    const QUERY_REF = 'ref';
    const QUERY_LABEL = 'label';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $ref;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $documentation;

    /**
     * @var AF_Model_Category|null
     */
    protected $category;

    /**
     * @var AF_Model_Component_Group
     */
    protected $rootGroup;

    /**
     * Collection des algos de l'AF
     * @var Algo_Model_Set
     */
    protected $algoSet;

    /**
     * Algo principal qui va sélectionner les sous-algos à exécuter pour cet AF
     * @var Algo_Model_Selection_Main
     */
    protected $mainAlgo;

    /**
     * @var AF_Model_Component[]|Collection
     */
    protected $components;

    /**
     * @var AF_Model_Condition[]|Collection
     */
    protected $conditions;


    /**
     * @param string $ref Identifiant
     * @throws Core_Exception_User Ref invalide
     */
    public function __construct($ref)
    {
        $this->components = new ArrayCollection();
        $this->conditions = new ArrayCollection();
        $this->setRef($ref);

        // Crée un nouveau set d'algos
        $this->algoSet = new Algo_Model_Set();

        // Crée un nouvel algo principal
        $this->mainAlgo = new Algo_Model_Selection_Main();
        $this->mainAlgo->setRef(self::ALGO_MAIN_REF);
        $this->mainAlgo->setSet($this->algoSet);
        $this->algoSet->addAlgo($this->mainAlgo);

        // Crée le rootGroup des composants
        $this->rootGroup = new AF_Model_Component_Group();
        $this->rootGroup->setRef(AF_Model_Component_Group::ROOT_GROUP_REF);
        $this->rootGroup->setAf($this);
    }

    /**
     * Retourne les algos de l'AF
     * @return Algo_Model_Algo[]
     */
    public function getAlgos()
    {
        return $this->algoSet->getAlgos();
    }

    /**
     * Retourne un algo de l'AF par son ref
     * @param  string $ref
     * @return Algo_Model_Algo
     * @throws Core_Exception_NotFound
     */
    public function getAlgoByRef($ref)
    {
        return $this->algoSet->getAlgoByRef($ref);
    }

    /**
     * @param Algo_Model_Algo $algo
     */
    public function addAlgo(Algo_Model_Algo $algo)
    {
        $this->algoSet->addAlgo($algo);
        $algo->setSet($this->algoSet);
    }

    /**
     * @param Algo_Model_Algo $algo
     */
    public function removeAlgo(Algo_Model_Algo $algo)
    {
        $this->algoSet->removeAlgo($algo);
        $algo->setSet(null);
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = (string) $label;
    }

    /**
     * @return string label
     */
    public function getLabel()
    {
        return ($this->label);
    }

    /**
     * @param string $documentation
     */
    public function setDocumentation($documentation)
    {
        $this->documentation = (string) $documentation;
    }

    /**
     * @return string documentation
     */
    public function getDocumentation()
    {
        return $this->documentation;
    }

    /**
     * @param string $ref
     * @throws Core_Exception_User Ref invalide
     */
    public function setRef($ref)
    {
        Core_Tools::checkRef($ref);
        $this->ref = (string) $ref;
    }

    /**
     * @param AF_Model_Component_Group $rootGroup
     */
    public function setRootGroup(AF_Model_Component_Group $rootGroup)
    {
        if ($this->rootGroup !== $rootGroup) {
            $this->rootGroup = $rootGroup;
            $rootGroup->setAf($this);
        }
    }

    /**
     * @return AF_Model_Component_Group
     */
    public function getRootGroup()
    {
        return $this->rootGroup;
    }

    /**
     * Generate a UI_Form to render it in a view
     * @param AF_Model_InputSet_Primary|null $inputSet
     * @param string                         $mode read, write or test (default is write)
     * @return UI_Form
     */
    public function generateForm(AF_Model_InputSet_Primary $inputSet = null,
                                 $mode = AF_ViewConfiguration::MODE_WRITE
    ) {
        $form = new UI_Form($this->ref);

        $generationHelper = new AF_GenerationHelper($inputSet, $mode);

        // Ajout du groupe principal constituant le formulaire.
        $form->addElement($this->rootGroup->getUIElement($generationHelper));
        return $form;
    }

    /**
     * Génère un composant UI_Form de cet AF en tant que sous-AF
     * @param AF_GenerationHelper    $generationHelper
     * @param AF_Model_InputSet|null $inputSet
     * @return UI_Form_Element_Group
     */
    public function generateSubForm(AF_GenerationHelper $generationHelper, AF_Model_InputSet $inputSet = null)
    {
        $generationHelper = new AF_GenerationHelper($inputSet, $generationHelper->getMode());
        return $this->rootGroup->getUIElement($generationHelper);
    }

    /**
     * Get sets of the AF.
     * @return AF_Model_InputSet_Primary[]
     */
    public function getInputSets()
    {
        $query = new Core_Model_Query;
        $query->filter->addCondition(AF_Model_InputSet_Primary::QUERY_AF, $this);
        return AF_Model_InputSet_Primary::loadList($query);
    }

    /**
     * Execute the calculation
     * @param AF_Model_InputSet $inputSet
     * @return AF_Model_Output_OutputSet
     */
    public function execute(AF_Model_InputSet $inputSet)
    {
        /** @var $mainAlgo Algo_Model_Selection_Main */
        $mainAlgo = $this->getAlgoByRef('Main');

        // Execution de l'algo principal
        $results = $mainAlgo->execute($inputSet);

        // On crée les résultats
        $outputSet = new AF_Model_Output_OutputSet();
        $outputSet->addAlgoOutputs($inputSet, $results);

        // Si on est dans l'inputset primary, on enregistre les résultats
        if ($inputSet instanceof AF_Model_InputSet_Primary) {
            $inputSet->setOutputSet($outputSet);
            $outputSet->setInputSet($inputSet);
        }

        // Exécution des sous-af
        foreach ($inputSet->getInputs() as $input) {
            if ($input->isHidden()) {
                continue;
            }
            if ($input instanceof AF_Model_Input_SubAF_NotRepeated) {
                $subInputSet = $input->getValue();
                /** @var $subAf AF_Model_Component_SubAF_NotRepeated */
                $subAf = $input->getComponent();
                $subOutputSet = $subAf->getCalledAF()->execute($subInputSet);
                // Enregistre les résultats
                $outputSet->mergeOutputSet($subOutputSet);
            }
            if ($input instanceof AF_Model_Input_SubAF_Repeated) {
                $subInputSets = $input->getValue();
                /** @var $subAf AF_Model_Component_SubAF_Repeated */
                $subAf = $input->getComponent();
                foreach ($subInputSets as $subInputSet) {
                    $subOutputSet = $subAf->getCalledAF()->execute($subInputSet);
                    // Enregistre les résultats
                    $outputSet->mergeOutputSet($subOutputSet);
                }
            }
        }

        return $outputSet;
    }

    /**
     * Cette méthode permet de récupérer tout les éléments d'un module en fonction de leur type.
     * Elle est utilisée pour l'édition des formulaires.
     *
     * @param string $type Le type d'éléments que l'on souhaite récupérer
     *
     * @return AF_Model_Component[]
     */
    public function getElementsByType($type)
    {
        $allComponents = $this->rootGroup->getSubComponentsRecursive();

        $returnedComponents = [];

        foreach ($allComponents as $component) {
            if ($component instanceof $type) {
                $returnedComponents[] = $component;
            }
        }

        return $returnedComponents;
    }

    /**
     * Retourne un module par son référent textuel
     * @param string $ref
     * @return AF_Model_AF
     */
    public static function loadByRef($ref)
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(self::QUERY_REF, $ref);
        $afList = AF_Model_AF::loadList($query);
        if (count($afList) == 0) {
            throw new Core_Exception_NotFound("No AF matching ref='$ref' was found");
        }
        return current($afList);
    }

    /**
     * Cette méthode vérifie que les sous formulaire ne forment pas une boucle.
     * @param array $exploredSubAf
     * @return boolean
     */
    public function checkClosedLoop(array $exploredSubAf = [])
    {
        if (!in_array($this->ref, $exploredSubAf)) {
            $exploredSubAf[] = $this->ref;
        }
        foreach ($this->getSubAfList() as $subAf) {
            $af = $subAf->getCalledAF();
            if (!in_array($af->getRef(), $exploredSubAf)) {
                $exploredSubAf[] = $af->getRef();
                if ($af->checkClosedLoop($exploredSubAf)) {
                    return true;
                }
            } else {
                return true;
            }
        }
        return false;
    }

    /**
     * Récupère la liste des subAF d'un AF.
     * @return AF_Model_Component_SubAF[]
     */
    public function getSubAfList()
    {
        return $this->getElementsByType('AF_Model_Component_SubAF');
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * @return AF_Model_Category|null
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param AF_Model_Category $category
     */
    public function setCategory(AF_Model_Category $category)
    {
        if ($this->category !== $category) {
            $this->deletePosition();
            if ($this->category) {
                $this->category->removeAF($this);
            }

            $this->category = $category;

            $category->addAF($this);
            $this->setPosition();
        }
    }

    /**
     * @return Algo_Model_Selection_Main
     */
    public function getMainAlgo()
    {
        return $this->mainAlgo;
    }

    /**
     * @param Algo_Model_Selection_Main $mainAlgo
     */
    public function setMainAlgo(Algo_Model_Selection_Main $mainAlgo)
    {
        $this->mainAlgo = $mainAlgo;
    }

    /**
     * @return AF_Model_Condition[]
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * Retourne le nombre de champs requis à la saisie de l'AF
     * @param AF_Model_InputSet|null $inputSet
     * @return int
     */
    public function getNbRequiredFields(AF_Model_InputSet $inputSet = null)
    {
        return $this->getRootGroup()->getNbRequiredFields($inputSet);
    }

    /**
     * Ajoute un composant à l'AF
     *
     * Crée l'algo associé si nécessaire
     *
     * @param AF_Model_Component $component
     */
    public function addComponent(AF_Model_Component $component)
    {
        // S'il s'agit d'un champ numérique, on crée automatiquement l'algo correspondant
        if ($component instanceof AF_Model_Component_Numeric) {
            $algo = new Algo_Model_Numeric_Input();
            $algo->setRef($component->getRef());
            $algo->setLabel($component->getLabel());
            $algo->setInputRef($component->getRef());
            $algo->setUnit($component->getUnit());
            $algo->save();
            $this->addAlgo($algo);
        }
        // S'il s'agit d'un champ de sélection, on crée automatiquement l'algo correspondant
        if ($component instanceof AF_Model_Component_Select_Single) {
            $algo = new Algo_Model_Selection_TextKey_Input();
            $algo->setRef($component->getRef());
            $algo->setInputRef($component->getRef());
            $algo->save();
            $this->addAlgo($algo);
        }
    }

    /**
     * Retire un composant de l'AF
     *
     * Supprime l'algo associé si il existe
     *
     * @param AF_Model_Component $component
     */
    public function removeComponent(AF_Model_Component $component)
    {
        // S'il s'agit d'un champ numérique ou de sélection, on supprime automatiquement l'algo correspondant
        if ($component instanceof AF_Model_Component_Numeric
            || $component instanceof AF_Model_Component_Select_Single
        ) {
            try {
                $algo = $this->getAlgoByRef($component->getRef());
            } catch (Core_Exception_NotFound $e) {
                return;
            }
            $algo->delete();
        }
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
     * Renvoie les valeurs du contexte pour la position
     * @return array
     */
    protected function getContext()
    {
        return [
            'category' => $this->category,
        ];
    }

}
