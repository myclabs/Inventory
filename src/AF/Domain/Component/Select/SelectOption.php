<?php

namespace AF\Domain\Component\Select;

use AF\Domain\Component\Select;
use AF\Domain\Algorithm\Condition\SelectConditionAlgo;
use Core\Translation\TranslatedString;
use Core_Exception_InvalidArgument;
use Core_Exception_UndefinedAttribute;
use Core_Model_Entity;
use Core_Model_Query;
use Core_ORM_ForeignKeyViolationException;
use Core_Strategy_Ordered;
use Core_Tools;
use Mnapoli\Translated\Translator;
use AF\Application\Form\Element\Option;

/**
 * Gestion des options associées aux composants de type select.
 *
 * @author matthieu.napoli
 * @author hugo.charbonnier
 * @author thibaud.rolland
 * @author yoann.croizer
 */
class SelectOption extends Core_Model_Entity
{
    use Core_Strategy_Ordered;

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
     * @var TranslatedString
     */
    protected $label;

    /**
     * @var Select
     */
    protected $select;

    public function __construct()
    {
        $this->label = new TranslatedString();
    }

    /**
     * Génère un élément UI
     * @return Option
     */
    public function getUIElement()
    {
        $uiElement = new Option($this->ref);
        $uiElement->value = $this->ref;
        $uiElement->label = $this->uglyTranslate($this->label);
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
     * @return TranslatedString
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param TranslatedString $label
     */
    public function setLabel(TranslatedString $label)
    {
        $this->label = $label;
    }

    /**
     * @return Select
     */
    public function getSelect()
    {
        return $this->select;
    }

    /**
     * @param Select $select
     */
    public function setSelect(Select $select)
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
        $query->filter->addCondition(SelectConditionAlgo::QUERY_SET, $this->getSelect()->getAf()->getMainAlgo()->getSet());
        $query->filter->addCondition(SelectConditionAlgo::QUERY_VALUE, $this->getRef());
        $algos = SelectConditionAlgo::loadList($query);
        $unitOfWork = \Core\ContainerSingleton::getEntityManager()->getUnitOfWork();
        foreach ($algos as $algo) {
            if ($unitOfWork->getEntityState($algo) === \Doctrine\ORM\UnitOfWork::STATE_MANAGED) {
                throw new Core_ORM_ForeignKeyViolationException(
                    get_class(current($algos)),
                    'value',
                    get_class($this),
                    'id'
                );
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

    /**
     * @deprecated Moche, très moche
     * @todo À supprimer quand la génération UI est sortie du modèle
     * @param TranslatedString $string
     * @return string
     */
    protected function uglyTranslate(TranslatedString $string)
    {
        /** @var Translator $translator */
        $translator = \Core\ContainerSingleton::getContainer()->get(Translator::class);

        return $translator->get($string);
    }
}
