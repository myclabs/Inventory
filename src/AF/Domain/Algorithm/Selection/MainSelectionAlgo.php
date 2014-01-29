<?php

namespace AF\Domain\Algorithm\Selection;

use AF\Domain\Algorithm\Algo;
use AF\Domain\Algorithm\AlgoConfigurationError;
use AF\Domain\Algorithm\Index\AlgoResultIndex;
use AF\Domain\Algorithm\Index\FixedIndex;
use AF\Domain\Algorithm\InputSet;
use AF\Domain\Algorithm\Numeric\NumericAlgo;
use Core_Exception_NotFound;
use Exception;
use Exec\Execution\Select;
use Exec\Provider\ValueInterface;
use TEC\Exception\InvalidExpressionException;
use TEC\Expression;

/**
 * @author matthieu.napoli
 * @author hugo.charbonnier
 * @author yoann.croizer
 */
class MainSelectionAlgo extends SelectionAlgo implements ValueInterface
{
    /**
     * @var string|null
     */
    protected $expression;

    /**
     * {@inheritdoc}
     */
    public function execute(InputSet $inputSet)
    {
        $this->inputSet = $inputSet;
        if (!$this->expression) {
            return [];
        }

        // Construit l'arbre
        $tecExpression = new Expression($this->expression, Expression::TYPE_SELECT);

        $executionSelect = new Select($tecExpression);
        // on doit avoir en sortie un tableau de Output
        return $executionSelect->executeExpression($this);
    }

    /**
     * Retourne les algorithmes numériques qui seront exécutés par la méthode execute()
     * @param InputSet $inputSet
     * @return NumericAlgo[]
     */
    public function getSelectedNumericAlgos(InputSet $inputSet)
    {
        $this->inputSet = $inputSet;
        if (!$this->expression) {
            return [];
        }

        $tecExpression = new Expression($this->expression, Expression::TYPE_SELECT);
        $executionSelect = new Select($tecExpression);
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
        if ($algo instanceof NumericAlgo) {
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
        if ($this->expression == null) {
            $errors[] = new AlgoConfigurationError(__('Algo', 'configControl', 'emptyMainAlgorithmExpression'), false);
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
            if ($algo instanceof NumericAlgo) {
                if ($algo->isIndexed() && !in_array($algo, $algosInMain)) {
                    $message = __('Algo', 'configControl', 'algoIndexedNotInMain', ['REF_ALGO' => $algo->getRef()]);
                    $errors[] = new AlgoConfigurationError($message, false);
                }
            }
        }

        // Valide l'expression
        $tecExpression = new Expression($this->expression, Expression::TYPE_SELECT);
        $executionSelect = new Select($tecExpression);
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
            $configError = new AlgoConfigurationError();
            $configError->isFatal(true);
            $configError->setMessage(__('Algo', 'configControl', 'noAlgorithmForOperandInMainAlgorithm', [
                'EXPRESSION' => $this->expression,
                'REF_OPERAND' => $ref
            ]));
            $errors[] = $configError;
            return $errors;
        }

        // Vérifie qu'il s'agit d'un algo numérique
        if (!$algo instanceof NumericAlgo) {
            $configError = new AlgoConfigurationError();
            $configError->isFatal(true);
            $configError->setMessage(__('Algo', 'configControl', 'nonNumericOperandInMainAlgorithm', [
                'EXPRESSION' => $this->expression,
                'REF_OPERAND' => $ref
            ]));
            $errors[] = $configError;
            return $errors;
        }

        // Vérifie que l'algorithme numérique a un label non vide
        if (!$algo->getLabel()) {
            $errors[] = new AlgoConfigurationError(__('Algo', 'configControl', 'operandWithoutLabelInMainAlgorithm', [
                'EXPRESSION' => $this->expression,
                'REF_OPERAND' => $ref
            ]), false);
        }

        // Vérifie qu'il y'a bien un context indicator
        if (!$algo->isIndexed()) {
            $errors[] = new AlgoConfigurationError(__('Algo', 'configControl', 'operandWithoutIndicatorInMainAlgorithm', [
                'EXPRESSION' => $this->expression,
                'REF_OPERAND' => $ref
            ]), true);
            return $errors;
        }

        // Vérifie que l'algo numérique a une unité compatible avec celle de son context indicator
        $algoUnit = $algo->getUnit();
        $indicatorUnit = $algo->getContextIndicator()->getIndicator()->getUnit();
        try {
            if (!$algoUnit->isEquivalent($indicatorUnit)) {
                $message = __('Algo', 'configControl', 'algoUnitIncompatibleWithIndicator', [
                    'REF_OPERAND'    => $ref,
                    'ALGO_UNIT'      => $algoUnit->getSymbol(),
                    'INDICATOR_UNIT' => $indicatorUnit->getSymbol(),
                ]);
                $errors[] = new AlgoConfigurationError($message, true);
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
            $errors[] = new AlgoConfigurationError(__('Algo', 'configControl', 'algoIndexationInvalid', [
                'REF_OPERAND' => $ref
            ]), true);
            return $errors;
        }

        // Vérifie que les index ont une valeur
        foreach ($algoIndexes as $algoIndex) {
            if ($algoIndex instanceof FixedIndex && !$algoIndex->hasClassifMember()) {
                $errors[] = new AlgoConfigurationError(__('Algo', 'configControl', 'algoIndexationInvalid', [
                    'REF_OPERAND' => $ref
                ]), true);
            }
            if ($algoIndex instanceof AlgoResultIndex && !$algoIndex->getAlgo()) {
                $errors[] = new AlgoConfigurationError(__('Algo', 'configControl', 'algoIndexationInvalid', [
                    'REF_OPERAND' => $ref
                ]), true);
            }
        }

        return $errors;
    }

    /**
     * @return string
     */
    public function getExpression()
    {
        $tecExpression = new Expression($this->expression, Expression::TYPE_SELECT);
        return $tecExpression->getAsString();
    }

    /**
     * @param string|null $expression
     * @throws InvalidExpressionException
     */
    public function setExpression($expression = null)
    {
        if ($expression == null) {
            $this->expression = null;
        } else {
            $tecExpression = new Expression($expression, Expression::TYPE_SELECT);
            $tecExpression->check();
            // Expression OK
            $this->expression = (string) $expression;
        }
    }

    /**
     * Retourne tous les algos appelés par l'expression de cet algo
     * @return Algo[]
     */
    public function getSubAlgos()
    {
        if (!$this->expression) {
            return [];
        }
        $tecExpression = new Expression($this->expression, Expression::TYPE_SELECT);
        $leafs = $tecExpression->getRootNode()->getAllLeafsRecursively();
        $subAlgos = [];
        foreach ($leafs as $leaf) {
            $subAlgoRef = $leaf->getName();
            $subAlgos[] = Algo::loadByRef($this->set, $subAlgoRef);
        }
        return $subAlgos;
    }
}
