<?php
/**
 * @author matthieu.napoli
 */

namespace AuditTrail\Domain\Context;

/**
 * Classe abstraite d'un contexte
 *
 * Value Object
 */
abstract class Context
{
    /**
     * @var int
     */
    private $id;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
