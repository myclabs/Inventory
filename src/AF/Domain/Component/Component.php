<?php

namespace AF\Domain\Component;

use AF\Domain\Action\Action;
use AF\Domain\AF;
use AF\Domain\AFConfigurationError;
use AF\Domain\AFGenerationHelper;
use AF\Domain\Input\Input;
use AF\Domain\InputSet\InputSet;
use AF\Domain\Algorithm\Condition\ElementaryConditionAlgo;
use Core\Translation\TranslatedString;
use Core_Exception_UndefinedAttribute;
use Core_Model_Entity;
use Core_Strategy_Ordered;
use Core_Tools;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Mnapoli\Translated\TranslationHelper;
use MyCLabs\MUIH\Button;
use MyCLabs\MUIH\Icon;
use UI_Form_Element_HTML;
use UI_Form_ZendElement;
use Zend_Form_Element;

/**
 * @author matthieu.napoli
 * @author hugo.charbonnier
 * @author thibaud.rolland
 * @author yoann.croizer
 */
abstract class Component extends Core_Model_Entity
{
    use Core_Strategy_Ordered;

    // Filtres pour les requetes
    const QUERY_AF = 'af';
    const QUERY_REF = 'ref';

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $ref;

    /**
     * @var TranslatedString
     */
    protected $label;

    /**
     * @var TranslatedString
     */
    protected $help;

    /**
     * Est-ce que le champ est visible (par défaut visible)
     * @var boolean
     */
    protected $visible = true;

    /**
     * @var Action[]|Collection
     */
    protected $actions;

    /**
     * @var Group|null
     */
    protected $group;

    /**
     * @var AF|null
     */
    protected $af;


    public function __construct()
    {
        $this->label = new TranslatedString();
        $this->help = new TranslatedString();
        $this->actions = new ArrayCollection();
    }

    /**
     * Génère un élément UI
     * @param AFGenerationHelper $generationHelper
     * @return UI_Form_ZendElement|Zend_Form_Element
     */
    abstract public function getUIElement(AFGenerationHelper $generationHelper);

    /**
     * Retourne le nombre de champs requis dans le composant pour la saisie de l'AF
     * @param InputSet|null $inputSet
     * @return int
     */
    abstract public function getNbRequiredFields(InputSet $inputSet = null);

    /**
     * Méthode utilisée pour vérifier la configuration des champs.
     * @return AFConfigurationError[]
     */
    public function checkConfig()
    {
        return [];
    }

    public function setLabel(TranslatedString $label)
    {
        $this->label = $label;
    }

    /**
     * @return TranslatedString
     */
    public function getLabel()
    {
        return $this->label;
    }

    public function setHelp(TranslatedString $help)
    {
        $this->help = $help;
    }

    /**
     * @return TranslatedString
     */
    public function getHelp()
    {
        return $this->help;
    }

    /**
     * Get Actions
     * @return Action[]
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * Add an action to the component
     * @param Action $action
     */
    public function addAction(Action $action)
    {
        if (!$this->hasAction($action)) {
            $this->actions->add($action);
        }
    }

    /**
     * See if the component has already the action
     * @param Action $action
     * @return bool
     */
    public function hasAction(Action $action)
    {
        return $this->actions->contains($action);
    }

    /**
     * Remove the action from the component
     * @param Action $action
     */
    public function removeAction(Action $action)
    {
        if ($this->hasAction($action)) {
            $this->actions->removeElement($action);
        }
    }

    /**
     * @param Group|null $group
     */
    public function setGroup(Group $group = null)
    {
        if ($this->group !== $group) {
            $this->deletePosition();

            $this->group = $group;
            if ($group) {
                $group->addSubComponent($this);
            }

            $this->setPosition();
        }
    }

    /**
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the ref attribute.
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * @param string $ref
     */
    public function setRef($ref)
    {
        Core_Tools::checkRef($ref);

        $oldRef = $this->ref;

        $this->ref = (string) $ref;

        // Modifie également le ref des algos condition qui pointent vers ce champ
        $af = $this->getAf();
        if ($af) {
            foreach ($af->getAlgos() as $algo) {
                if ($algo instanceof ElementaryConditionAlgo
                    && $algo->getInputRef() == $oldRef
                ) {
                    $algo->setInputRef($ref);
                    $algo->save();
                }
            }
        }
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * @param bool $visible
     */
    public function setVisible($visible)
    {
        $this->visible = (boolean) $visible;
    }

    /**
     * @return AF|null
     */
    public function getAf()
    {
        return $this->af;
    }

    /**
     * @param AF $af
     */
    public function setAf(AF $af)
    {
        $this->af = $af;
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
            'af'    => $this->af,
            'group' => $this->group,
        ];
    }

    /**
     * Permet de charger un Component par son ref et son AF
     * @param string $ref
     * @param AF     $af
     * @return static
     */
    public static function loadByRef($ref, AF $af)
    {
        return self::getEntityRepository()->loadBy(['ref' => $ref, 'af' => $af]);
    }

    /**
     * Renvoie le nom de la classe a utiliser pour manipuler l'ordre sur les éléments proche de l'objet
     * @return string
     */
    protected static function getOrderedBaseEntityName()
    {
        return __CLASS__;
    }

    /**
     * Retourne le composant UI pour l'historique des valeurs de la saisie
     * @param Input $input
     * @return UI_Form_Element_HTML
     */
    protected function getHistoryComponent(Input $input)
    {
        $historyButton = new Button(new Icon('clock-o'));
        $historyButton->addClass('input-history');
        $historyButton->setAttribute('title', __('UI', 'history', 'valueHistory'));
        $historyButton->setAttribute('data-input-id', $input->getId());
        $historyButton->setAttribute('data-toggle', 'button');
        $historyButton->setAttribute('data-container', 'body');

        return new UI_Form_Element_HTML($this->ref . 'History', $historyButton->render());
    }

    /**
     * @deprecated Moche, très moche
     * @todo À supprimer quand la génération UI est sortie du modèle
     * @param TranslatedString $string
     * @return string
     */
    protected function uglyTranslate(TranslatedString $string)
    {
        /** @var TranslationHelper $translationHelper */
        $translationHelper = \Core\ContainerSingleton::getContainer()->get(TranslationHelper::class);

        return $translationHelper->toString($string);
    }
}
