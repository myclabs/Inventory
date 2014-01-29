<?php

namespace AF\Domain\AF\Component;

use AF\Domain\AF\Component;

/**
 * Champ de formulaire.
 *
 * @author matthieu.napoli
 * @author hugo.charbonnier
 * @author thibaud.rolland
 * @author yoann.croizer
 */
abstract class Field extends Component
{
    /**
     * Est-ce que le champ est activé (par défaut il est activé)
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
