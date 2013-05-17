<?php
/**
 * @author     matthieu.napoli
 * @author     hugo.charbonnier
 * @author     thibaud.rolland
 * @author     yoann.croizer
 * @package    AF
 * @subpackage Form
 */

/**
 * Gestion des sous formulaires et des repetitions de sous formulaires.
 * @package    AF
 * @subpackage Form
 */
class AF_Model_Component_SubAF_Repeated extends AF_Model_Component_SubAF
{

    /**
     * Constante associée à l'attribut minInputNumber.
     * Aucun sous module au départ.
     * @var int
     */
    const MININPUTNUMBER_0 = 0;
    /**
     * Constante associée à l'attribut minInputNumber.
     * Un sous module supprimable au départ.
     * @var int
     */
    const MININPUTNUMBER_1_DELETABLE = 1;
    /**
     * Constante associée à l'attribut minInputNumber.
     * Un sous module non supprimable au départ.
     * @var int
     */
    const MININPUTNUMBER_1_NOT_DELETABLE = 2;

    /**
     * Attribut utilisé pour les sous modules répétés.
     * A pour valeur possible :
     *  - 0 Aucun sous module au départ.
     *  - 1 Un sous module supprimable au départ.
     *  - 2 Un sous module non supprimable au départ.
     * @var integer
     */
    protected $minInputNumber = 0;

    /**
     * Active un libellé libre saisi par l'utilisateur
     * @var bool
     */
    protected $withFreeLabel = false;


    /**
     * {@inheritdoc}
     */
    public function getUIElement(AF_GenerationHelper $generationHelper)
    {
        // Groupe contenant une liste de sous-formulaires
        $uiElement = new UI_Form_Element_Group($this->ref);
        $uiElement->setLabel($this->label);
        $uiElement->getElement()->help = $this->help;
        $uiElement->getElement()->hidden = !$this->visible;
        switch ($this->foldaway) {
            case self::FOLDAWAY:
                $uiElement->foldaway = true;
                break;
            case self::FOLDED:
                $uiElement->folded = true;
                break;
            default:
                $uiElement->foldaway = false;
        }
        // Récupère la saisie correspondant à cet élément
        $input = null;
        if ($generationHelper->getInputSet()) {
            /** @var $input AF_Model_Input_SubAF_Repeated */
            $input = $generationHelper->getInputSet()->getInputForComponent($this);
            if ($input) {
                $uiElement->getElement()->hidden = $input->isHidden();
                $uiElement->getElement()->disabled = $input->isDisabled();
            }
        }
        // Récupère les sous-inputSets correspondant à ce sous-af
        if ($input) {
            $subInputSets = $input->getValue();
            $number = 0;
            foreach ($subInputSets as $subInputSet) {
                $uiElement->addElement($this->getSingleSubAFUIElement($generationHelper, $number, $subInputSet));
                $number++;
            }
        } else {
            if ($this->minInputNumber !== self::MININPUTNUMBER_0) {
                // Ajoute un seul exemplaire du formulaire par défaut
                $uiElement->addElement($this->getSingleSubAFUIElement($generationHelper, 0, null));
            }
        }
        // Bouton d'ajout d'un nouveau groupe
        if (!$generationHelper->isReadOnly()) {
            $addButtonGroup = new UI_Form_Element_Group('addButtonGroup');
            $addButtonGroup->addAttribute('class', 'addSubAFGroup');
            $addButtonGroup->addAttribute('data-id-af-owner', $this->getAf()->getId());
            $addButtonGroup->addAttribute('data-ref-component', $this->getRef());
            $addButtonGroup->getElement()->prefixRef($this->ref);
            $addButtonGroup->setLabel('');
            $addButton = new UI_HTML_Button(__('UI', 'verb', 'add'));
            $addButton->addAttribute('class', 'addSubAF');
            $addButton->addAttribute('data-loading-text', "...");
            $addButton->addAttribute('data-id-af-owner', $this->getAf()->getId());
            $addButton->addAttribute('data-ref-component', $this->getRef());
            $addButtonHTML = new UI_Form_Element_HTML('addButton_' . $this->ref);
            $addButtonHTML->content = $addButton->render();
            $addButtonGroup->addElement($addButtonHTML);
            $addButtonGroup->foldaway = false;
            $uiElement->addElement($addButtonGroup);
        }
        // Actions
        foreach ($this->actions as $action) {
            $uiElement->getElement()->addAction($generationHelper->getUIAction($action));
        }
        return $uiElement;
    }

    /**
     * Génère un groupe contenant un seul sous-AF
     * @param AF_GenerationHelper        $generationHelper
     * @param int                        $number
     * @param AF_Model_InputSet_Sub|null $inputSet
     * @return UI_Form_Element_Group
     */
    public function getSingleSubAFUIElement(AF_GenerationHelper $generationHelper, $number,
                                            AF_Model_InputSet_Sub $inputSet = null
    ) {
        $ref = $this->ref . UI_Generic::REF_SEPARATOR . $number;
        // On crée un groupe qui contient un sous-formulaire
        $afGroup = new UI_Form_Element_Group($ref);
        $afGroup->addAttribute('class', 'subAFGroup');
        $afGroup->addAttribute('data-id-af-owner', $this->getAf()->getId());
        $afGroup->addAttribute('data-ref-component', $this->getRef());
        $afGroup->setLabel($number + 1);
        $afGroup->addAttribute("data-number", $number);
        // Pour chaque sous module, on peut ajouter un label choisi librement par l'utilisateur
        if ($this->withFreeLabel) {
            $label = new UI_Form_Element_Text('freeLabel');
            $label->getElement()->prefixRef($ref);
            $label->setLabel(__('AF', 'inputInput', 'freeLabel'));
            if ($inputSet) {
                $label->setValue($inputSet->getFreeLabel());
            }
            if ($generationHelper->isReadOnly()) {
                $label->getElement()->setReadOnly();
            }
            $afGroup->addElement($label);
        }
        // Sous-formulaire
        $subForm = $this->calledAF->generateSubForm($generationHelper, $inputSet);
        $subForm->getElement()->prefixRef($ref);
        $afGroup->addElement($subForm);
        // Bouton de suppression
        if ($this->minInputNumber !== self::MININPUTNUMBER_1_NOT_DELETABLE  && !$generationHelper->isReadOnly()) {
            $deleteButton = new UI_HTML_Button(__('UI', 'verb', 'delete'));
            $deleteButton->addAttribute('class', 'removeSubAF');
            $deleteButton->addAttribute('data-id-af-owner', $this->getAf()->getId());
            $deleteButton->addAttribute('data-ref-component', $this->getRef());
            $deleteButton->addAttribute('data-number', $number);
            $deleteButtonHTML = new UI_Form_Element_HTML("deleteButton_{$this->ref}_$number");
            $deleteButtonHTML->content = $deleteButton->render();
            $afGroup->addElement($deleteButtonHTML);
        }

        return $afGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function getNbRequiredFields(AF_Model_InputSet $inputSet = null)
    {
        if ($inputSet) {
            /** @var $input AF_Model_Input_SubAF_Repeated */
            $input = $inputSet->getInputForComponent($this);
            if ($input) {
                // Si le sous-af est caché
                if ($input->isHidden()) {
                    return 0;
                }
                $subInputSets = $input->getValue();
                $nbRequiredFields = 0;
                foreach ($subInputSets as $subInputSet) {
                    $nbRequiredFields += $this->getCalledAF()->getNbRequiredFields($subInputSet);
                }
                return $nbRequiredFields;
            }
        }
        // Pas de saisie
        if ($this->getMinInputNumber() == self::MININPUTNUMBER_0) {
            return 0;
        }
        return $this->getCalledAF()->getNbRequiredFields($inputSet);
    }

    /**
     * @return int Nombre minimum d'apparition du formulaire
     */
    public function getMinInputNumber()
    {
        return $this->minInputNumber;
    }

    /**
     * Définit le nombre minimum d'apparition du formulaire
     * A pour valeur possible :
     *  - 0 Aucun sous module au départ.
     *  - 1 Un sous module supprimable au départ.
     *  - 2 Un sous module non supprimable au départ.
     * @param int $minInputNumber
     */
    public function setMinInputNumber($minInputNumber)
    {
        $this->minInputNumber = (int) $minInputNumber;
    }

    /**
     * @return bool Active un libellé libre saisi par l'utilisateur
     */
    public function getWithFreeLabel()
    {
        return $this->withFreeLabel;
    }

    /**
     * @param bool $withFreeLabel Active un libellé libre saisi par l'utilisateur
     */
    public function setWithFreeLabel($withFreeLabel)
    {
        $this->withFreeLabel = (bool) $withFreeLabel;
    }

}
