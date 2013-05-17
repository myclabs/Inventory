<?php
/**
 * @author    hugo.charbonnier
 * @package   AF
 */

/**
 * @package AF
 */
class AF_ConfigError extends Algo_ConfigError
{

    /**
     * Le formulaire dans lequel l'erreur est présente
     * @var AF_Model_AF|null
     */
    protected $af;


    /**
     * {@inheritdoc}
     * @param AF_Model_AF|null $af
     */
    public function __construct($message = null, $isFatal = null, AF_Model_AF $af = null)
    {
        parent::__construct($message, $isFatal);
        $this->setAf($af);
    }

    /**
     * @param AF_Model_AF|null $af
     */
    public function setAf(AF_Model_AF $af = null)
    {
        $this->af = $af;
    }

    /**
     * @return AF_Model_AF|null
     */
    public function getAf()
    {
        return $this->af;
    }

}
