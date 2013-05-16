<?php
/**
 * @author valentin.claras
 * @author hugo.charbonnier
 * @author yoann.croizer
 * @package    Calc
 * @subpackage Calculation
 */

/**
 * Calc_Calculation
 * @package    Calc
 * @subpackage Calculation
 *
 */
abstract class Calc_Calculation
{
    // Constantes de classe.
    const SUM = 1;
    const SUBSTRACTION = -1;

    const PRODUCT = 1;
    const DIVISION = -1;

    const ADD_OPERATION = 'sum';
    const MULTIPLY_OPERATION = 'mul';

    /**
     * Type d'opération.
     * Utilise des constantes de la classe
     * sum -> addition
     * mul -> multiplication
     *
     * @var const
     */
    protected $operation;

    /**
     * Array : ('operand' => mixed, 'signExponent' => int )
     * signExponent  appartient {1, -1}
     *      si l'operation est une somme  : + => addition       - => soustraction
     *      si l'operation est un produit : * => multiplication / => division
     *
     * @var array
     */
    protected $components = array();


    /**
     * Défini le type de calcul utilisé pour cet expression.
     *
     * @see self::ADD_OPERATION
     * @see self::MULTIPLY_OPERATION
     *
     * @param int $operation
     * @throws Core_Exception_InvalidArgument
     */
    public function setOperation($operation)
    {
        if (!(in_array($operation, array(self::ADD_OPERATION, self::MULTIPLY_OPERATION)))) {
            throw new Core_Exception_InvalidArgument('The operation must be a class constant');
        }
        $this->operation = $operation;
    }

    /**
     * Fonction qui ajoute un composant pour l'opération avec son signe et son exposant.
     *
     * @param mixed $operand
     * @param int $signExponent
     *
     * @return void
     */
    public function addComponents($operand, $signExponent)
    {
        if (!(in_array($signExponent, array(self::SUM, self::SUBSTRACTION, self::PRODUCT, self::DIVISION)))) {
            throw new Core_Exception_InvalidArgument('The sign exponent must be a class constant.');
        }
        $this->components[] = array('operand' => $operand, 'signExponent' => $signExponent);
    }

    /**
     * Vérifie que le tableau de component est bien homogène.
     *
     * @throws Core_Exception_InvalidArgument
     */
    public abstract function checkComponent();

    /**
     * Fonction qui calcule selon l'opération.
     *
     * @return mixed
     */
    public abstract function calculate();

}
