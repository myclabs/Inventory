<?php

namespace AF\Domain\AF;

/**
 * @author hugo.charbonnier
 * @author matthieu.napoli
 */
class ConfigError extends \AF\Domain\Algorithm\ConfigError
{
    /**
     * Le formulaire dans lequel l'erreur est présente
     * @var AF|null
     */
    protected $af;

    /**
     * {@inheritdoc}
     * @param AF|null $af
     */
    public function __construct($message = null, $isFatal = null, AF $af = null)
    {
        parent::__construct($message, $isFatal);
        $this->setAf($af);
    }

    /**
     * @param AF|null $af
     */
    public function setAf(AF $af = null)
    {
        $this->af = $af;
    }

    /**
     * @return AF|null
     */
    public function getAf()
    {
        return $this->af;
    }
}
