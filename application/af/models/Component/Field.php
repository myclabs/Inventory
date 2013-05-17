<?php
/**
 * @author     matthieu.napoli
 * @author     hugo.charbonnier
 * @author     thibaud.rolland
 * @author     yoann.croizer
 * @package    AF
 * @subpackage Form
 */

/**
 * Classe abstraite AF_Model_Component_Field.
 * @package    AF
 * @subpackage Form
 */
abstract class AF_Model_Component_Field extends AF_Model_Component
{

    /**
     * Est-ce que le champs est activé (par défaut il est activé)
     * @var bool
     */
    protected $enabled = true;


    /**
     * @return bool Est-ce que le champ est activé
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled Est-ce que le champ est activé
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (bool) $enabled;
    }

}
