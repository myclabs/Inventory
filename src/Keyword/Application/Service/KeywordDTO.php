<?php

namespace Keyword\Application\Service;

/**
 * @author valentin.claras
 */
class KeywordDTO
{
    /**
     * @var string
     */
    protected $ref;

    /**
     * @var string
     */
    protected $label;


    /**
     * @param string $ref
     * @param string $label
     */
    public function __construct($ref, $label='')
    {
        $this->ref = $ref;
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->ref;
    }

    /**
     * @return string Label
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * @return string Label
     */
    public function getLabel()
    {
        return $this->label;
    }

}
