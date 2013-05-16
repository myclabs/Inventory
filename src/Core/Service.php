<?php
/**
 * Classe Core_Service
 * @author  valentin.claras
 * @author  benjamin.bertin
 * @package    Core
 * @subpackage Service
 */

/**
 * Service class : Used between controllers and model.
 * @package    Core
 * @subpackage Service
 */
abstract class Core_Service extends Core_Singleton
{
    /**
     * Magical PHP method __call
     * @param string $methodName
     * @param array $arguments
     */
    public function __call($methodName, $arguments)
    {
        if (method_exists($this, $methodName.'Service')) {
            $name = $methodName.'Service';
            $returnValue = call_user_func_array(array($this, $name), $arguments);
            //@todo inclure les fonctions des traits de manières automatique.
            // Par exemple toutes celles commençant par _service.
            return $returnValue;
        }
        throw new Core_Exception_NotFound('There\'s no Service matching '.$methodName);
    }
}
