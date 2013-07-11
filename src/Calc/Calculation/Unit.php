<?php
/**
 * @author valentin.claras
 * @author hugo.charbonnier
 * @author yoann.croizer
 * @package    Calc
 * @subpackage Calculation
 */
use Unit\UnitAPI;

/**
 * @package    Calc
 * @subpackage Calculation
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

        return UnitAPI::calculateSum($unitTab);
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