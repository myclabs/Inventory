<?php

use MyCLabs\UnitAPI\Operation\Result\OperationResult;
use Unit\UnitAPI;

/**
 * @author valentin.claras
 * @author hugo.charbonnier
 * @author yoann.croizer
 */
class Calc_Calculation_Unit extends Calc_Calculation
{
    /**
     * @var OperationResult|null
     */
    private $operationResult;

    /**
     * Vérifie que le tableau de component est bien homogène.
     *
     * @throws Core_Exception_InvalidArgument
     */
    public function checkComponent()
    {
        foreach ($this->components as $component) {
            if (!($component['operand'] instanceof UnitAPI)) {
                throw new Core_Exception_InvalidArgument('Array of components is not coherent.');
            }
        }
    }

    /**
     * Effectue une somme ou un produit d'unité.
     *
     * @return UnitAPI
     */
    public function calculate()
    {
        $this->checkComponent();

        if ($this->operation == Calc_Calculation::ADD_OPERATION) {
            return $this->calculateSum();
        } elseif ($this->operation == Calc_Calculation::MULTIPLY_OPERATION) {
            return $this->calculateProduct();
        }

        throw new Core_Exception_InvalidArgument('Unknow operation');
    }

    /**
     * Calcul d'une somme d'unités.
     *
     * @return UnitAPI
     */
    protected function calculateSum()
    {
        // Tableau d'unités envoyé pour la somme.
        $unitTab = array();

        $components = $this->components;
        foreach ($components as $component) {
            $unitTab[] = $component['operand']->getRef();
        }

        $this->operationResult = UnitAPI::calculateSum($unitTab);

        return new UnitAPI($this->operationResult->getUnitId());
    }

    /**
     * Calcul d'un produit d'unités.
     *
     * @return UnitAPI
     */
    protected function calculateProduct()
    {
        // Tableau d'unités envoyées pour la mutliplication
        $unitTab = array();

        $components = $this->components;
        foreach ($components as $component) {
            $unitTab[] = array('unit' => $component['operand'], 'signExponent' => $component['signExponent']);
        }

        $this->operationResult = UnitAPI::multiply($unitTab);

        return new UnitAPI($this->operationResult->getUnitId());
    }

    /**
     * @return OperationResult|null
     */
    public function getOperationResult()
    {
        return $this->operationResult;
    }
}
