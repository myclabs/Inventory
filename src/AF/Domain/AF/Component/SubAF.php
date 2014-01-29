<?php

namespace AF\Domain\AF\Component;

use AF\Domain\AF\AF;
use AF\Domain\AF\Component;

/**
 * Gestion des sous formulaires et des repetitions de sous formulaires
 *
 * @author matthieu.napoli
 * @author hugo.charbonnier
 * @author thibaud.rolland
 * @author yoann.croizer
 */
abstract class SubAF extends Component
{
    /**
     * Constante associée à l'attribut 'foldaway'.
     * Correspond à un élément non repliable.
     * @var integer
     */
    const UNFOLDAWAY = 0;

    /**
     * Constante associée à l'attribut 'foldaway'.
     * Correspond à un élément repliable mais initialement non replié.
     * @var integer
     */
    const FOLDAWAY = 1;

    /**
     * Constante associée à l'attribut 'foldaway'.
     * Correspond à un élément repliable et initialement replié.
     * @var integer
     */
    const FOLDED = 2;

    /**
     * Identifiant du formulaire appelé.
     * @var AF
     */
    protected $calledAF;

    /**
     * Flag indiquant si l'élément est repliable.
     * @var integer
     */
    protected $foldaway = self::FOLDAWAY;


    /**
     * {@inheritdoc}
     */
    public function checkConfig()
    {
        $errors = parent::checkConfig();
        $errors = array_merge($errors, $this->getCalledAF()->getRootGroup()->checkConfig());
        return $errors;
    }

    /**
     * @param AF $af
     */
    public function setCalledAF(AF $af)
    {
        $this->calledAF = $af;
    }

    /**
     * @return AF
     */
    public function getCalledAF()
    {
        return $this->calledAF;
    }

    /**
     * Get the foldaway attribute.
     * @return integer
     */
    public function getFoldaway()
    {
        return $this->foldaway;
    }

    /**
     * Set the foldaway attribute.
     * @param integer $foldaway
     */
    public function setFoldaway($foldaway)
    {
        $this->foldaway = (int) $foldaway;
    }
}
