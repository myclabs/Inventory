<?php
/**
 * @author  matthieu.napoli
 * @author  thibaud.rolland
 * @author  hugo.charbonnier
 * @author  yoann.croizer
 * @package AF
 */

/**
 * @package    AF
 * @subpackage Condition
 */
abstract class AF_Model_Condition_Elementary extends AF_Model_Condition
{

    const RELATION_EQUAL = 1;
    const RELATION_NEQUAL = 2;
    const RELATION_GT = 3;
    const RELATION_LT = 4;
    const RELATION_GE = 5;
    const RELATION_LE = 6;
    const RELATION_CONTAINS = 7;
    const RELATION_NCONTAINS = 8;

    /**
     * @var AF_Model_Component_Field
     */
    protected $field;

    /**
     * @var int
     */
    protected $relation = self::RELATION_EQUAL;

    /**
     * @param AF_Model_Component_Field $field
     */
    public function setField(AF_Model_Component_Field $field)
    {
        $this->field = $field;
    }

    /**
     * @return AF_Model_Component_Field
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return int
     */
    public function getRelation()
    {
        return $this->relation;
    }

    /**
     * Méthode utilisée pour vérifier la configuration des conditions élémentaires.
     * @return AF_ConfigError[]
     */
    public function checkConfig()
    {
        $errors = array();
        // On vérifie que le champs de saisie associé à cette condition existe bien.
        if ($this->field === null) {
            $errors[] = new AF_ConfigError("La condition '$this->ref' n'est associée à aucun champ",
                                           false, $this->getAf());
        }
        return $errors;
    }

}
