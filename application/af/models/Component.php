<?php
/**
 * @author     matthieu.napoli
 * @author     hugo.charbonnier
 * @author     thibaud.rolland
 * @author     yoann.croizer
 * @package    AF
 * @subpackage Form
 */

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @package    AF
 * @subpackage Form
 */
abstract class AF_Model_Component extends Core_Model_Entity
{

    use Core_Strategy_Ordered;
    use Core_Model_Entity_Translatable;

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
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $help;

    /**
     * Est-ce que le champ est visible (par défaut visible)
     * @var boolean
     */
    protected $visible = true;

    /**
     * @var AF_Model_Action[]|Collection
     */
    protected $actions;

    /**
     * @var AF_Model_Component_Group|null
     */
    protected $group;

    /**
     * @var AF_Model_AF|null
     */
    protected $af;


    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->actions = new ArrayCollection();
    }

    /**
     * Génère un élément UI
     * @param AF_GenerationHelper $generationHelper
     * @return Zend_Form_Element
     */
    abstract public function getUIElement(AF_GenerationHelper $generationHelper);

    /**
     * Retourne le nombre de champs requis dans le composant pour la saisie de l'AF
     * @param AF_Model_InputSet|null $inputSet
     * @return int
     */
    abstract public function getNbRequiredFields(AF_Model_InputSet $inputSet = null);

    /**
     * Méthode utilisée pour vérifier la configuration des champs.
     * @return AF_ConfigError[]
     */
    public function checkConfig()
    {
        return [];
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = (string) $label;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $help
     */
    public function setHelp($help)
    {
        $this->help = (string) $help;
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        return $this->help;
    }

    /**
     * Get Actions
     * @return AF_Model_Action[]
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * Add an action to the component
     * @param AF_Model_Action $action
     */
    public function addAction(AF_Model_Action $action)
    {
        if (!$this->hasAction($action)) {
            $this->actions->add($action);
        }
    }

    /**
     * See if the component has already the action
     * @param AF_Model_Action $action
     * @return bool
     */
    public function hasAction(AF_Model_Action $action)
    {
        return $this->actions->contains($action);
    }

    /**
     * Remove the action from the component
     * @param AF_Model_Action $action
     */
    public function removeAction(AF_Model_Action $action)
    {
        if ($this->hasAction($action)) {
            $this->actions->removeElement($action);
        }
    }

    /**
     * @param AF_Model_Component_Group|null $group
     */
    public function setGroup(AF_Model_Component_Group $group = null)
    {
        if ($this->group !== $group) {
            $this->deletePosition();

            $this->group = $group;

            $this->setPosition();
        }
    }

    /**
     * @return AF_Model_Component_Group
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
                if ($algo instanceof Algo_Model_Condition_Elementary
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
     * @return AF_Model_AF|null
     */
    public function getAf()
    {
        return $this->af;
    }

    /**
     * @param AF_Model_AF $af
     */
    public function setAf(AF_Model_AF $af)
    {
        $this->af = $af;
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
            'af'    => $this->af,
            'group' => $this->group,
        ];
    }

    /**
     * Permet de charger un Component par son ref et son AF
     * @param string      $ref
     * @param AF_Model_AF $af
     * @return AF_Model_Component
     */
    public static function loadByRef($ref, AF_Model_AF $af)
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

}
