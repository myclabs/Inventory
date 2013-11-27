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
 * Gestion des options associées aux composants de type select.
 * @package    AF
 * @subpackage Form
 */
class AF_Model_Component_Select_Option extends Core_Model_Entity
{

    use Core_Strategy_Ordered;
    use Core_Model_Entity_Translatable;

    // Constant used for query sorting and filtering
    const QUERY_SELECT = 'select';

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
     * Flag indiquant si l'option est visible (par défaut visible)
     * @var boolean
     */
    protected $visible = true;

    /**
     * Flag indiquant si l'option est activée par défaut (par défaut activée)
     * @var boolean
     */
    protected $enabled = true;

    /**
     * @var AF_Model_Component_Select
     */
    protected $select;


    /**
     * Génère un élément UI
     * @return UI_Form_Element_Option
     */
    public function getUIElement()
    {
        $uiElement = new UI_Form_Element_Option($this->ref);
        $uiElement->value = $this->ref;
        $uiElement->label = $this->label;
        $uiElement->disabled = !$this->enabled;
        $uiElement->hidden = !$this->visible;
        return $uiElement;
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
     * @param string $ref
     */
    public function setRef($ref)
    {
        Core_Tools::checkRef($ref);
        $this->ref = (string) $ref;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = (string) $label;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (bool) $enabled;
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
        $this->visible = (bool) $visible;
    }

    /**
     * @return AF_Model_Component_Select
     */
    public function getSelect()
    {
        return $this->select;
    }

    /**
     * @param AF_Model_Component_Select $select
     */
    public function setSelect(AF_Model_Component_Select $select)
    {
        if ($this->select !== $select) {
            $this->select = $select;
            $select->addOption($this);
        }
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
        // Cherche si une condition d'algo porte sur ce champ
        $query = new Core_Model_Query();
        $query->filter->addCondition(Algo_Model_Condition_Elementary_Select::QUERY_VALUE, $this->getRef());
        $algos = Algo_Model_Condition_Elementary_Select::loadList($query);
        $entityManagers = Zend_Registry::get('EntityManagers');
        $unitOfWork = $entityManagers['default']->getUnitOfWork();
        foreach ($algos as $algo) {
            if ($unitOfWork->getEntityState($algo) === \Doctrine\ORM\UnitOfWork::STATE_MANAGED) {
                throw new Core_ORM_ForeignKeyViolationException(get_class(current($algos)), 'value',
                                                                get_class($this), 'id');
            }
        }
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
     * Renvoie les valeurs du contexte pour l'objet.
     * @throws Core_Exception_InvalidArgument
     * @return array
     */
    protected function getContext()
    {
        return [
            'select' => $this->select,
        ];
    }

}
