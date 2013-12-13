<?php

/**
 * @author valentin.claras
 * @author hugo.charbonnier
 * @author yoann.croizer
 */
class Calc_Calculation_UnitValue extends Calc_Calculation
{
    /**
     * Vérifie que le tableau de component est bien homogène.
     *
     * @throws Core_Exception_InvalidArgument
     */
    public function checkComponent()
    {
        foreach ($this->components as $component) {
            if (!($component['operand'] instanceof Calc_UnitValue)) {
                throw new Core_Exception_InvalidArgument(sprintf(
                    'Calculation expects an array of %s, %s given',
                    Calc_UnitValue::class,
                    is_object($component['operand']) ? get_class($component['operand']) : gettype($component['operand'])
                ));
            }
        }
    }

    /**
     * Calcul une somme ou un produit de valeurs associées à leurs unités
     * en fonction de l'operation spécifiée.
     *
     * @throws Core_Exception_InvalidArgument
     * @return Calc_UnitValue
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
     * Effectue une somme d'unités et de valeurs
     *
     * On prend chaques composants, et on sépare la somme des unités de la somme des valeurs.
     * Lors de la somme des unités on récupère pour chaque unité son facteur de conversion
     * Lors de la somme des valeurs, on multiplie chaque valeur par le facteur de conversion
     * récupéré préalablement
     *
     * @return Calc_UnitValue
     */
    protected function calculateSum()
    {
        // Units ----------------------------------------------------
        $calcUnit = new Calc_Calculation_Unit();
        $calcUnit->operation = Calc_Calculation::ADD_OPERATION;
        foreach ($this->components as $component) {
            // On ajoute les composants de la somme.
            $calcUnit->addComponents($component['operand']->getUnit(), $component['signExponent']);
        }
        // On calcul la somme des unités.
        $calculationUnit = $calcUnit->calculate();

        // Values ----------------------------------------------------
        $calcValue = new Calc_Calculation_Value();
        $calcValue->operation = Calc_Calculation::ADD_OPERATION;
        foreach ($this->components as $component) {
            /** @var Calc_UnitValue $unitValue */
            $unitValue = $component['operand'];

            // Conversion dans l'unité de résultat
            $newDigitalValue = $unitValue->convertTo($calculationUnit)->getDigitalValue();

            $value = new Calc_Value($newDigitalValue, $unitValue->getRelativeUncertainty());

            $calcValue->addComponents($value, $component['signExponent']);
        }
        // On calcul la somme des valeurs.
        $calculationValue = $calcValue->calculate();

        // On rempli une unitValue avec avec la valeur et l' unité calculées.
        return new Calc_UnitValue(
            $calculationUnit,
            $calculationValue->getDigitalValue(),
            $calculationValue->getRelativeUncertainty()
        );
    }

    /**
     * Effectue un produit d'unités et de valeurs
     *
     * On prend chaques composants, et on sépare le produit des unités du produit des valeurs.
     * Lors du produit des unités s'il s'agit d'une division on prend l'inverse du facteur
     * de conversion.
     * Lors du produit des valeurs on mutlpilie le produit final par le facteur de conversion.
     *
     * @return Calc_UnitValue
     */
    protected function calculateProduct()
    {
        $facteurConversion = 1;

        // Units ----------------------------------------------------
        $calcUnit = new Calc_Calculation_Unit();
        $calcUnit->operation = Calc_Calculation::MULTIPLY_OPERATION;

        foreach ($this->components as $component) {
            // Si il s'agit d'une division.
            if ($component['signExponent'] == Calc_Calculation::DIVISION) {
                // On prend l'inverse du facteur de conversion.
                $facteurConversion /= $component['operand']->getUnit()->getConversionFactor();
            } elseif ($component['signExponent'] == Calc_Calculation::PRODUCT) {
                // Sinon on prend le facteur de conversion.
                $facteurConversion *= $component['operand']->getUnit()->getConversionFactor();
            }
            // On ajoute les composants du produit.
            $calcUnit->addComponents($component['operand']->getUnit(), $component['signExponent']);
        }
        // On calcul le produit des unités.
        $calculationUnit = $calcUnit->calculate();

        // Values ----------------------------------------------------
        $calcValue = new Calc_Calculation_Value();
        $calcValue->operation = Calc_Calculation::MULTIPLY_OPERATION;
        foreach ($this->components as $component) {
            /** @var Calc_UnitValue $unitValue */
            $unitValue = $component['operand'];
            $value = new Calc_Value($unitValue->getDigitalValue(), $unitValue->getRelativeUncertainty());
            $calcValue->addComponents($value, $component['signExponent']);
        }
        // On calcul la somme des valeurs.
        $calculationValue = $calcValue->calculate();
        // On multpilie le resultat par le facteur de conversion.
        $calculationDigitalValue = $calculationValue->getDigitalValue() * $facteurConversion;

        // On rempli une unitValue avec avec la valeur et l'unité calculée
        return new Calc_UnitValue(
            $calculationUnit,
            $calculationDigitalValue,
            $calculationValue->getRelativeUncertainty()
        );
    }
}
