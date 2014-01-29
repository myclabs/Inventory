<?php
namespace AF\Domain\AF\Component\SubAF;

use AF\Domain\AF\InputSet\InputSet;
use AF\Domain\AF\GenerationHelper;
use AF\Domain\AF\Component\SubAF;
use AF\Domain\AF\Input\SubAF\RepeatedSubAFInput;
use AF\Domain\AF\InputSet\SubInputSet;
use UI_Form_Element_Group;
use UI_Form_Element_GroupRepeated;
use UI_Form_Element_Text;

/**
 * Gestion des sous formulaires et des repetitions de sous formulaires.
 *
 * @author matthieu.napoli
 * @author hugo.charbonnier
 * @author thibaud.rolland
 * @author yoann.croizer
 */
class RepeatedSubAF extends SubAF
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
    public function getUIElement(GenerationHelper $generationHelper)
    {
        // Groupe contenant une liste de sous-formulaires
        $uiElement = new UI_Form_Element_GroupRepeated($this->ref);
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

        // Ajoute les en-têtes du tableau
        if ($this->withFreeLabel) {
            $label = new UI_Form_Element_Text('freeLabel');
            $label->setLabel(__('AF', 'inputInput', 'freeLabel'));
            $uiElement->addElement($label);
        }
        foreach ($this->calledAF->getRootGroup()->getSubComponentsRecursive() as $component) {
            $subElement = $component->getUIElement(new GenerationHelper());
            $uiElement->addElement($subElement);
        }

        // Récupère la saisie correspondant à cet élément
        $input = null;
        if ($generationHelper->getInputSet()) {
            /** @var $input RepeatedSubAFInput */
            $input = $generationHelper->getInputSet()->getInputForComponent($this);
            if ($input) {
                $uiElement->getElement()->hidden = $input->isHidden();
                $uiElement->getElement()->disabled = $input->isDisabled();
            }
        }
        // Récupère les sous-inputSets correspondant à ce sous-af
        if ($input) {
            $subInputSets = $input->getValue();
            foreach ($subInputSets as $subInputSet) {
                $uiElement->addLineValue($this->getSingleSubAFUIElement($generationHelper, $subInputSet));
            }
        } else {
            if ($this->minInputNumber !== self::MININPUTNUMBER_0) {
                // Ajoute un seul exemplaire du formulaire par défaut
                $uiElement->addLineValue($this->getSingleSubAFUIElement($generationHelper, null));
            }
        }
        // Actions
        foreach ($this->actions as $action) {
            $uiElement->getElement()->addAction($generationHelper->getUIAction($action));
        }

        $uiElement->getElement()->prefixRef($this->ref);
        return $uiElement;
    }

    /**
     * Génère un groupe contenant un seul sous-AF
     * @param \AF\Domain\AF\GenerationHelper        $generationHelper
     * @param SubInputSet|null $inputSet
     * @return UI_Form_Element_Group
     */
    private function getSingleSubAFUIElement(
        GenerationHelper $generationHelper,
        SubInputSet $inputSet = null
    ) {
        // On crée un groupe qui contient un sous-formulaire
        $afGroup = new UI_Form_Element_Group($this->ref);
        // Pour chaque sous af, on peut ajouter un label choisi librement par l'utilisateur
        if ($this->withFreeLabel) {
            $label = new UI_Form_Element_Text('freeLabel');
            $label->setLabel(__('AF', 'inputInput', 'freeLabel'));
            if ($inputSet) {
                $label->setValue($inputSet->getFreeLabel());
            }
            if ($generationHelper->isReadOnly()) {
                $label->getElement()->setReadOnly();
            }
            $afGroup->addElement($label);
        }
        // Génère le sous-formulaire
        $subForm = $this->calledAF->generateSubForm($generationHelper, $inputSet);
        // Ajoute chaque élément du sous-formulaire au groupe
        foreach ($subForm->getElement()->getChildrenElements() as $uiElement) {
            $afGroup->addElement($uiElement);
        }

        return $afGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function getNbRequiredFields(InputSet $inputSet = null)
    {
        if ($inputSet) {
            /** @var $input RepeatedSubAFInput */
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
        return $this->getCalledAF()->getNbRequiredFields();
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
