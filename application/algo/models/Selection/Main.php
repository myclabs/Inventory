<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @author  yoann.croizer
 * @package Algo
 */

use TEC\Expression;

/**
 * @package Algo
 */
class Algo_Model_Selection_Main extends Algo_Model_Selection implements Exec_Interface_ValueProvider
{

    /**
     * @var string|null
     */
    protected $expression;

    /**
     * @var Expression|null
     */
    protected $tecExpression;

    /**
     * {@inheritdoc}
     */
    public function execute(Algo_Model_InputSet $inputSet)
    {
        $this->inputSet = $inputSet;
        if (!$this->expression) {
            return [];
        }
        $executionSelect = new Exec_Execution_Select($this->tecExpression);
        // on doit avoir en sortie un tableau de Algo_Model_Output
        return $executionSelect->executeExpression($this);
    }

    /**
     * Retourne les algorithmes numériques qui seront exécutés par la méthode execute()
     * @param Algo_Model_InputSet $inputSet
     * @return Algo_Model_Numeric[]
     */
    public function getSelectedNumericAlgos(Algo_Model_InputSet $inputSet)
    {
        $this->inputSet = $inputSet;
        if (!$this->expression) {
            return [];
        }

        $executionSelect = new Exec_Execution_Select($this->tecExpression);
        // On doit avoir en sortie un tableau des refs des algos numériques
        $refs = $executionSelect->getSelectedLeafs($this);

        $algos = [];
        foreach ($refs as $algoRef) {
            $algos[] = $this->set->getAlgoByRef($algoRef);
        }
        return $algos;
    }

    /**
     * {@inheritdoc}
     */
    public function getValueForExecution($ref)
    {
        $algo = $this->set->getAlgoByRef($ref);
        if ($algo instanceof Algo_Model_Numeric) {
            $result = $algo->executeAndIndex($this->inputSet);
        } else {
            // on a un algo de type condition, donc pas besoin d'indexation
            $result = $algo->execute($this->inputSet);
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function checkConfig()
    {
        $errors = parent::checkConfig();

        // Vérifie que l'expression n'est pas vide
        if (!$this->tecExpression) {
            $errors[] = new Algo_ConfigError(__('Algo', 'configControl', 'emptyMainAlgorithmExpression'), false);
            return $errors;
        }

        // Vérifie que chaque algo numérique non appelé par cet algo n'est pas indexé
        try {
            $algosInMain = $this->getSubAlgos();
        } catch (Core_Exception_NotFound $e) {
            // S'il y'a un algo inconnu, ça sera détecté dans checkValueForExecution donc on passe
            $algosInMain = [];
        }
        foreach ($this->getSet()->getAlgos() as $algo) {
            if ($algo instanceof Algo_Model_Numeric) {
                if ($algo->isIndexed() && !in_array($algo, $algosInMain)) {
                    $message = __('Algo', 'configControl', 'algoIndexedNotInMain', ['REF_ALGO' => $algo->getRef()]);
                    $errors[] = new Algo_ConfigError($message, false);
                }
            }
        }

        // Valide l'expression
        $executionSelect = new Exec_Execution_Select($this->tecExpression);
        return array_merge($errors, $executionSelect->getErrors($this));
    }

    /**
     * {@inheritdoc}
     */
    public function checkValueForExecution($ref)
    {
        $errors = [];

        // Teste si le ref correspond à un algo existant
        try {
            $algo = $this->set->getAlgoByRef($ref);
        } catch (Core_Exception_NotFound $e) {
            $configError = new Algo_ConfigError();
            $configError->isFatal(true);
            $configError->setMessage(__('Algo', 'configControl', 'noAlgorithmForOperandInMainAlgorithm',
                                        ['EXPRESSION' => $this->expression, 'REF_OPERAND' => $ref]));
            $errors[] = $configError;
            return $errors;
        }

        // Vérifie qu'il s'agit d'un algo numérique
        if (!$algo instanceof Algo_Model_Numeric) {
            $configError = new Algo_ConfigError();
            $configError->isFatal(true);
            $configError->setMessage(__('Algo', 'configControl', 'nonNumericOperandInMainAlgorithm',
                                        ['EXPRESSION' => $this->expression, 'REF_OPERAND' => $ref]));
            $errors[] = $configError;
            return $errors;
        }

        // Vérifie que l'algorithme numérique a un label non vide
        if (!$algo->getLabel()) {
            $errors[] = new Algo_ConfigError(__('Algo', 'configControl', 'operandWithoutLabelInMainAlgorithm',
                                                ['EXPRESSION' => $this->expression, 'REF_OPERAND' => $ref]), false);
        }

        // Vérifie qu'il y'a bien un context indicator
        if (!$algo->isIndexed()) {
            $errors[] = new Algo_ConfigError(__('Algo', 'configControl', 'operandWithoutIndicatorInMainAlgorithm',
                                                ['EXPRESSION' => $this->expression, 'REF_OPERAND' => $ref]), true);
            return $errors;
        }

        // Vérifie que l'algo numérique a une unité compatible avec celle de son context indicator
        $algoUnit = $algo->getUnit();
        $indicatorUnit = $algo->getContextIndicator()->getIndicator()->getUnit();
        try {
            if (!$algoUnit->isEquivalent($indicatorUnit)) {
                $message = __('Algo', 'configControl', 'algoUnitIncompatibleWithIndicator',
                              [
                              'REF_OPERAND'    => $ref,
                              'ALGO_UNIT'      => $algoUnit->getSymbol(),
                              'INDICATOR_UNIT' => $indicatorUnit->getSymbol(),
                              ]);
                $errors[] = new Algo_ConfigError($message, true);
            }
        } catch (Exception $e) {
        }

        // Vérifie que l'indexation de l'algo correspond aux axes du context indicator
        $isIndexationValid = true;
        $algoIndexes = $algo->getIndexes();
        $axes = $algo->getContextIndicator()->getAxes();
        // Vérifie que tous les index sont des axes du context indicator
        foreach ($algoIndexes as $algoIndex) {
            $algoAxis = $algoIndex->getClassifAxis();
            if (!in_array($algoAxis, $axes)) {
                $isIndexationValid = false;
                break;
            }
        }
        // Vérifie que tous les axes du context indicator sont dans les index
        foreach ($axes as $axis) {
            $match = false;
            foreach ($algoIndexes as $algoIndex) {
                $algoAxis = $algoIndex->getClassifAxis();
                if ($algoAxis == $axis) {
                    $match = true;
                }
            }
            if (!$match) {
                $isIndexationValid = false;
                break;
            }
        }
        if (!$isIndexationValid) {
            $errors[] = new Algo_ConfigError(__('Algo', 'configControl', 'algoIndexationInvalid',
                                                ['REF_OPERAND' => $ref]), true);
            return $errors;
        }

        // Vérifie que les index ont une valeur
        foreach ($algoIndexes as $algoIndex) {
            if ($algoIndex instanceof Algo_Model_Index_Fixed && !$algoIndex->hasClassifMember()) {
                $errors[] = new Algo_ConfigError(__('Algo', 'configControl', 'algoIndexationInvalid',
                                                    ['REF_OPERAND' => $ref]), true);
            }
            if ($algoIndex instanceof Algo_Model_Index_Algo && !$algoIndex->getAlgo()) {
                $errors[] = new Algo_ConfigError(__('Algo', 'configControl', 'algoIndexationInvalid',
                                                    ['REF_OPERAND' => $ref]), true);
            }
        }

        return $errors;
    }

    /**
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * @param string|null $expression
     * @throws InvalidExpressionException
     */
    public function setExpression($expression = null)
    {
        if ($expression == null) {
            $this->expression = null;
            $this->tecExpression = null;
        } else {
            $tecExpression = new Expression($expression, Expression::TYPE_SELECT);
            $tecExpression->check();
            // Expression OK
            $this->expression = (string) $expression;
            $this->tecExpression = $tecExpression;
        }
    }

    /**
     * Retourne tous les algos appelés par l'expression de cet algo
     * @return Algo_Model_Algo[]
     */
    public function getSubAlgos()
    {
        if (!$this->expression) {
            return [];
        }
        $leafs = $this->tecExpression->getRootNode()->getAllLeafsRecursively();
        $subAlgos = [];
        foreach ($leafs as $leaf) {
            $subAlgoRef = $leaf->getName();
            $subAlgos[] = Algo_Model_Algo::loadByRef($this->set, $subAlgoRef);
        }
        return $subAlgos;
    }

}
