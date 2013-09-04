<?php

namespace Keyword\Application;

use Keyword\Domain\Keyword;

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
     * @param Keyword $keyword
     */
    public function __construct(Keyword $keyword)
    {
        $this->ref = $keyword->getRef();
        $this->label = $keyword->getLabel();
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
