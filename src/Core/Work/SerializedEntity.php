<?php

namespace Core\Work;

/**
 * Serialized entity.
 *
 * @author matthieu.napoli
 */
class SerializedEntity
{
    public $class;
    public $id;

    public function __construct($class, $id)
    {
        $this->class = $class;
        $this->id = $id;
    }
}
