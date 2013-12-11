<?php

use DI\Container;
use MyCLabs\UnitAPI\Exception\IncompatibleUnitsException;
use MyCLabs\UnitAPI\OperationService;
use Unit\UnitAPI;

/**
 * @author valentin.claras
 * @author hugo.charbonnier
 * @author yoann.croizer
 */
class Calc_Calculation_Unit extends Calc_Calculation
{
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
     * L'opération vérifie juste que les unités sont compatibles.
     *
     * @throws IncompatibleUnitsException
     * @return string Unité
     */
    protected function calculateSum()
    {
        /** @var Container $container */
        $container = Zend_Registry::get('container');
        /** @var OperationService $operationService */
        $operationService = $container->get(OperationService::class);

        $returnedUnit = null;

        foreach ($this->components as $component) {
            if ($returnedUnit === null) {
                $returnedUnit = $component['operand'];
                continue;
            }

            $unit = $component['operand'];
            if (!$operationService->areCompatible($returnedUnit, $unit)) {
                throw new IncompatibleUnitsException(sprintf(
                    'Impossible to add units %s and %s because they are incompatible',
                    $returnedUnit,
                    $unit
                ));
            }
        }

        return $returnedUnit;
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

        return UnitAPI::multiply($unitTab);
    }
}
