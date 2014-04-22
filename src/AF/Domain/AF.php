<?php

namespace AF\Domain;

use AF\Domain\Condition\Condition;
use AF\Domain\InputSet\InputSet;
use AF\Domain\Component\Component;
use AF\Domain\Component\Group;
use AF\Domain\Component\NumericField;
use AF\Domain\Component\Select\SelectSingle;
use AF\Domain\Component\SubAF;
use AF\Domain\Input\SubAF\NotRepeatedSubAFInput;
use AF\Domain\Input\SubAF\RepeatedSubAFInput;
use AF\Domain\InputSet\PrimaryInputSet;
use AF\Domain\Output\OutputSet;
use AF\Application\AFViewConfiguration;
use AF\Domain\Algorithm\Algo;
use AF\Domain\Algorithm\Numeric\NumericInputAlgo;
use AF\Domain\Algorithm\Selection\MainSelectionAlgo;
use AF\Domain\Algorithm\Selection\TextKey\InputSelectionAlgo;
use AF\Domain\Algorithm\AlgoSet;
use Core_Exception_NotFound;
use Core_Exception_UndefinedAttribute;
use Core_Model_Entity;
use Core_Model_Entity_Translatable;
use Core_Model_Query;
use Core_Strategy_Ordered;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use UI_Form;
use UI_Form_Element_Group;

/**
 * Accounting form.
 *
 * @author matthieu.napoli
 * @author thibaud.rolland
 * @author hugo.charbonnier
 * @author yoann.croizer
 */
class AF extends Core_Model_Entity
{
    use Core_Strategy_Ordered;
    use Core_Model_Entity_Translatable;

    const ALGO_MAIN_REF = 'main';

    const QUERY_LABEL = 'label';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var AFLibrary
     */
    protected $library;

    /**
     * @deprecated Sera supprimée dans la 3.1
     * @var string
     */
    private $ref;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $documentation;

    /**
     * @var Category|null
     */
    protected $category;

    /**
     * @var Group
     */
    protected $rootGroup;

    /**
     * Collection des algos de l'AF
     * @var AlgoSet
     */
    protected $algoSet;

    /**
     * Algo principal qui va sélectionner les sous-algos à exécuter pour cet AF
     * @var MainSelectionAlgo
     */
    protected $mainAlgo;

    /**
     * @var Component[]|Collection
     */
    protected $components;

    /**
     * @var Condition[]|Collection
     */
    protected $conditions;


    /**
     * @param AFLibrary $library
     * @param string    $label
     */
    public function __construct(AFLibrary $library, $label)
    {
        $this->components = new ArrayCollection();
        $this->conditions = new ArrayCollection();
        $this->library = $library;
        $this->label = $label;

        // Crée un nouveau set d'algos
        $this->algoSet = new AlgoSet();

        // Crée un nouvel algo principal
        $this->mainAlgo = new MainSelectionAlgo();
        $this->mainAlgo->setRef(self::ALGO_MAIN_REF);
        $this->mainAlgo->setSet($this->algoSet);
        $this->algoSet->addAlgo($this->mainAlgo);

        // Crée le rootGroup des composants
        $this->rootGroup = new Group();
        $this->rootGroup->setRef(Group::ROOT_GROUP_REF);
        $this->rootGroup->setAf($this);
    }

    /**
     * Get the ref attribute.
     * @deprecated Sera supprimée dans la 3.1
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Retourne les algos de l'AF
     * @return Algo[]
     */
    public function getAlgos()
    {
        return $this->algoSet->getAlgos();
    }

    /**
     * Retourne un algo de l'AF par son ref
     * @param  string $ref
     * @return Algo
     * @throws Core_Exception_NotFound
     */
    public function getAlgoByRef($ref)
    {
        return $this->algoSet->getAlgoByRef($ref);
    }

    /**
     * @param Algo $algo
     */
    public function addAlgo(Algo $algo)
    {
        $this->algoSet->addAlgo($algo);
        $algo->setSet($this->algoSet);
    }

    /**
     * @param Algo $algo
     */
    public function removeAlgo(Algo $algo)
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
     * @param Group $rootGroup
     */
    public function setRootGroup(Group $rootGroup)
    {
        if ($this->rootGroup !== $rootGroup) {
            $this->rootGroup = $rootGroup;
            $rootGroup->setAf($this);
        }
    }

    /**
     * @return Group
     * @todo Diminuer l'utilisation de cette méthode (elle casse l'encapsulation)
     */
    public function getRootGroup()
    {
        return $this->rootGroup;
    }

    /**
     * Generate a UI_Form to render it in a view
     * @param PrimaryInputSet|null $inputSet
     * @param string               $mode read, write or test (default is write)
     * @return UI_Form
     */
    public function generateForm(
        PrimaryInputSet $inputSet = null,
        $mode = AFViewConfiguration::MODE_WRITE
    ) {
        $form = new UI_Form('af' . $this->id);
        $form->addClass('af');

        $generationHelper = new AFGenerationHelper($inputSet, $mode);

        // Ajout du groupe principal constituant le formulaire.
        $form->addElement($this->rootGroup->getUIElement($generationHelper));
        return $form;
    }

    /**
     * Génère un composant UI_Form de cet AF en tant que sous-AF
     * @param AFGenerationHelper $generationHelper
     * @param InputSet|null      $inputSet
     * @return UI_Form_Element_Group
     */
    public function generateSubForm(AFGenerationHelper $generationHelper, InputSet $inputSet = null)
    {
        $generationHelper = new AFGenerationHelper($inputSet, $generationHelper->getMode());
        return $this->rootGroup->getUIElement($generationHelper);
    }

    /**
     * Get sets of the AF.
     * @return PrimaryInputSet[]
     */
    public function getInputSets()
    {
        $query = new Core_Model_Query;
        $query->filter->addCondition(PrimaryInputSet::QUERY_AF, $this);
        return PrimaryInputSet::loadList($query);
    }

    /**
     * Execute the calculation
     * @param InputSet $inputSet
     * @return OutputSet
     */
    public function execute(InputSet $inputSet)
    {
        /** @var $mainAlgo MainSelectionAlgo */
        $mainAlgo = $this->getAlgoByRef(self::ALGO_MAIN_REF);

        // Execution de l'algo principal
        $results = $mainAlgo->execute($inputSet);

        // On crée les résultats
        $outputSet = new OutputSet();
        $outputSet->addAlgoOutputs($inputSet, $results);

        // Si on est dans l'inputset primary, on enregistre les résultats
        if ($inputSet instanceof PrimaryInputSet) {
            $inputSet->setOutputSet($outputSet);
            $outputSet->setInputSet($inputSet);
        }

        // Exécution des sous-af
        foreach ($inputSet->getInputs() as $input) {
            if ($input->isHidden()) {
                continue;
            }
            if ($input instanceof NotRepeatedSubAFInput) {
                $subInputSet = $input->getValue();
                /** @var $subAf SubAF\NotRepeatedSubAF */
                $subAf = $input->getComponent();
                $subOutputSet = $subAf->getCalledAF()->execute($subInputSet);
                // Enregistre les résultats
                $outputSet->mergeOutputSet($subOutputSet);
            }
            if ($input instanceof RepeatedSubAFInput) {
                $subInputSets = $input->getValue();
                /** @var $subAf SubAF\RepeatedSubAF */
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
     * @return Component[]
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
     * Cette méthode vérifie que les sous formulaire ne forment pas une boucle.
     * @param array $exploredSubAf
     * @return boolean
     */
    public function checkClosedLoop(array $exploredSubAf = [])
    {
        if (!in_array($this->id, $exploredSubAf)) {
            $exploredSubAf[] = $this->id;
        }
        foreach ($this->getSubAfList() as $subAf) {
            $af = $subAf->getCalledAF();
            if (!in_array($af->getId(), $exploredSubAf)) {
                $exploredSubAf[] = $af->getId();
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
     * @return SubAF[]
     */
    public function getSubAfList()
    {
        return $this->getElementsByType(SubAF::class);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Category|null
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Category $category
     */
    public function setCategory(Category $category)
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
     * @return MainSelectionAlgo
     */
    public function getMainAlgo()
    {
        return $this->mainAlgo;
    }

    /**
     * @param MainSelectionAlgo $mainAlgo
     */
    public function setMainAlgo(MainSelectionAlgo $mainAlgo)
    {
        $this->mainAlgo = $mainAlgo;
    }

    /**
     * @return Condition[]
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @param Condition $condition
     */
    public function addCondition(Condition $condition)
    {
        if (!$this->conditions->contains($condition)) {
            $this->conditions->add($condition);
            $condition->setAf($this);
        }
    }

    /**
     * @param Condition $condition
     */
    public function removeCondition(Condition $condition)
    {
        if ($this->conditions->contains($condition)) {
            $this->conditions->removeElement($condition);
        }
    }

    /**
     * Retourne le nombre de champs requis à la saisie de l'AF
     * @param InputSet|null $inputSet
     * @return int
     */
    public function getNbRequiredFields(InputSet $inputSet = null)
    {
        return $this->rootGroup->getNbRequiredFields($inputSet);
    }

    /**
     * Ajoute un composant à l'AF (au root group)
     *
     * Crée l'algo associé si nécessaire
     *
     * @param Component $component
     */
    public function addComponent(Component $component)
    {
        // Ajout au root group si le composant n'est dans aucun groupe
        if ($component->getGroup() === null) {
            $this->rootGroup->addSubComponent($component);
        }
        // S'il s'agit d'un champ numérique, on crée automatiquement l'algo correspondant
        if ($component instanceof NumericField) {
            $algo = new NumericInputAlgo();
            $algo->setRef($component->getRef());
            $algo->setLabel($component->getLabel());
            $algo->setInputRef($component->getRef());
            $algo->setUnit($component->getUnit());
            $algo->save();
            $this->addAlgo($algo);
        }
        // S'il s'agit d'un champ de sélection, on crée automatiquement l'algo correspondant
        if ($component instanceof SelectSingle) {
            $algo = new InputSelectionAlgo();
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
     * @param Component $component
     */
    public function removeComponent(Component $component)
    {
        // S'il s'agit d'un champ numérique ou de sélection, on supprime automatiquement l'algo correspondant
        if ($component instanceof NumericField
            || $component instanceof SelectSingle
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
     * Vérifie si une condition d'algo porte sur un composant
     *
     * @param Component $component
     *
     * @return bool
     */
    public function hasAlgoConditionOnInput(Component $component)
    {
        return $this->algoSet->hasConditionOnInputRef($component->getRef());
    }

    /**
     * @return AFLibrary
     */
    public function getLibrary()
    {
        return $this->library;
    }

    /**
     * Fonction appelée avant un persist de l'objet (défini dans le mapper).
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
