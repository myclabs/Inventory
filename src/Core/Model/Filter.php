<?php
/**
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Model
 */

/**
 * Filtre dans une requête
 *
 * @package    Core
 * @subpackage Model
 */
class Core_Model_Filter
{
    /**
     * Condition de filtre : et.
     */
    const CONDITION_AND = 'Filter_Condition_And';
    /**
     * Condition de filtre : ou.
     */
    const CONDITION_OR = 'Filter_Condition_Or';

    /**
     * Opérateur de filtre : égal.
     */
    const OPERATOR_EQUAL = 'Filter_Operator_Equal';
    /**
     * Opérateur de filtre : contient.
     */
    const OPERATOR_CONTAINS = 'Filter_Operator_Contains';
    /**
     * Opérateur de filtre : contient.
     */
    const OPERATOR_BEGINS = 'Filter_Operator_Begins';
    /**
     * Opérateur de filtre : contient.
     */
    const OPERATOR_ENDS = 'Filter_Operator_Ends';
    /**
     * Opérateur de filtre : supérieur.
     */
    const OPERATOR_HIGHER = 'Filter_Operator_Higher';
    /**
     * Opérateur de filtre : inférieur.
     */
    const OPERATOR_LOWER = 'Filter_Operator_Lower';
    /**
     * Opérateur de filtre : supérieur ou égal.
     */
    const OPERATOR_HIGHER_EQUAL = 'Filter_Operator_HigherEqual';
    /**
     * Opérateur de filtre : inférieur ou égal.
     */
    const OPERATOR_LOWER_EQUAL = 'Filter_Operator_LowerEqual';
    /**
     * Opérateur de filtre : non égal.
     */
    const OPERATOR_NOT_EQUAL = 'Filter_Operator_NotEqual';
    /**
     * Opérateur de filtre : null.
     */
    const OPERATOR_NULL = 'Filter_Operator_Null';
    /**
     * Opérateur de filtre : non null.
     */
    const OPERATOR_NOT_NULL = 'Filter_Operator_NotNull';
    /**
     * Opérateur de filtre : sous filtre.
     */
    const OPERATOR_SUB_FILTER = 'Filter_Operator_SubFilter';

    /**
     * Type de connecteur logique entre les différentes conditions.
     *
     * Par défaut AND.
     *
     * @var const
     */
    public $condition = self::CONDITION_AND;

    /**
     * Conditions du filtre.
     *
     * @var array(name, value, operator, alias).
     */
    protected $_conditions = array();

    /**
     * Ajoute une condition au filtre.
     *
     * @param string $name Nom de l'attribut ou de la colonne sur lequel appliquer la condition.
     * @param mixed $value Valeur de la condition.
     * @param int $operator Opération à appliquer pour la condition (cf constantes).
     * @param string $alias Alias sur l'objet concerné par la condition dans la requêtte DQL.
     *
     * @return void
     */
    public function addCondition($name, $value, $operator=self::OPERATOR_EQUAL, $alias=null)
    {
        $this->_conditions[] = array(
            'name'     => $name,
            'value'    => $value,
            'operator' => $operator,
            'alias'    => $alias,
        );
    }

    /**
     * Renvoie les conditions du filtre.
     *
     * @return array(
     *  array(
     *      'name'     => $name,
     *      'value'    => $value,
     *      'operator' => $operator,
     *      'alias'    => $alias
     *  )
     * );
     */
    public function getConditions()
    {
        return $this->_conditions;
    }

    /**
     * Définit les conditions du filtre.
     *
     * @param array(array('name'=>$name,'value'=>$value,'operator'=>$operator)) $conditions
     *
     * @return void
     */
    public function setConditions($conditions)
    {
        $this->_conditions = $conditions;
    }

    /**
     * Valide les attributs de la classe.
     *
     * @return void
     */
    public function validate()
    {
        if (($this->condition !== self::CONDITION_AND) && ($this->condition !== self::CONDITION_OR)) {
            throw new Core_Exception_InvalidArgument('The logical connector has to be a class constant : "CONDITION".');
        }
        if (!(is_array($this->_conditions))) {
            throw new Core_Exception_InvalidArgument('Invalid data format for attribute "_conditions".');
        }
        // Vérifie chaque condition.
        foreach ($this->_conditions as $condition) {
            $this->validateCondition($condition);
        }
    }

    /**
     * Valide les attributs d'une condition.
     *
     * @param array $condition
     *
     * @return void
     */
    protected function validateCondition($condition)
    {
        if (!(isset($condition['name']))) {
            throw new Core_Exception_InvalidArgument('One of the conditions has no name.');
        }
        if (!(isset($condition['operator']))) {
            throw new Core_Exception_InvalidArgument('Condition "'.$condition['name'].'" has no operator.');
        }
        if ((!(isset($condition['value'])))
                && ($condition['operator'] !== self::OPERATOR_NULL)
                && ($condition['operator'] !== self::OPERATOR_NOT_NULL)) {
            throw new Core_Exception_InvalidArgument('Condition "'.$condition['name'].'" has no value.');
        }
        // Vérification de l'opérateur
        if (($condition['operator'] !== null)
                && ($condition['operator'] !== self::OPERATOR_EQUAL)
                && ($condition['operator'] !== self::OPERATOR_CONTAINS)
                && ($condition['operator'] !== self::OPERATOR_BEGINS)
                && ($condition['operator'] !== self::OPERATOR_ENDS)
                && ($condition['operator'] !== self::OPERATOR_HIGHER)
                && ($condition['operator'] !== self::OPERATOR_LOWER)
                && ($condition['operator'] !== self::OPERATOR_HIGHER_EQUAL)
                && ($condition['operator'] !== self::OPERATOR_LOWER_EQUAL)
                && ($condition['operator'] !== self::OPERATOR_NOT_EQUAL)
                && ($condition['operator'] !== self::OPERATOR_NULL)
                && ($condition['operator'] !== self::OPERATOR_NOT_NULL)
                && ($condition['operator'] !== self::OPERATOR_SUB_FILTER)
        ) {
            throw new Core_Exception_InvalidArgument('Condition "'.$condition['name'].'" has an invalid operator.');
        }
        // Vérification de la valeur pour un OPERATOR_SUB_FILTER
        if ($condition['operator'] === self::OPERATOR_SUB_FILTER) {
            if ($condition['name'] === 'main') {
                throw new Core_Exception_InvalidArgument('SubFilter name "'.$condition['name'].'" is the main Filter.');
            } else if (!($condition['value'] instanceof Core_Model_Filter)) {
                throw new Core_Exception_InvalidArgument('SubFilter "'.$condition['name'].'" must be a Filter.');
            } else if (empty($condition['value']->_conditions)) {
                throw new Core_Exception_InvalidArgument('SubFilter "'.$condition['name'].'" must have one condition.');
            } else {
                $condition['value']->validate();
            }
        }
    }

}
