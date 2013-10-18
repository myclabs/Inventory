<?php

namespace Keyword\Application\Service;

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
     * Construit un DTO à partir d'un Keyword
     * @param Keyword $keyword
     * @return KeywordDTO
     */
    public static function fromKeyword(Keyword $keyword)
    {
        return new self($keyword->getRef(), $keyword->getLabel());
    }

    /**
     * @param string $ref
     * @param string $label
     */
    public function __construct($ref, $label = '')
    {
        $this->ref = $ref;
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->ref;
    }
}
