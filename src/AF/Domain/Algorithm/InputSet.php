<?php

namespace AF\Domain\Algorithm;

use AF\Domain\Algorithm\Input\Input;

/**
 * @author  matthieu.napoli
 */
interface InputSet
{
    /**
     * Retourne la saisie d'un élément à partir de son ref.
     *
     * @param string $ref
     *
     * @return Input|null
     */
    public function getInputByRef($ref);

    /**
     * Retourne une valeur définie par le contexte à partir de son nom.
     *
     * @param string $name
     *
     * @return mixed|null
     */
    public function getContextValue($name);
}
