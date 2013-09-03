<?php

namespace Keyword\Application;

use Keyword\Domain\Keyword;

/**
 * @author valentin.claras
 */
class KeywordDTO
{
    /**
     * Referent textuel du Keyword.
     *
     * @var string
     */
    protected $ref = '';

    /**
     * Label du Keyword.
     *
     * @var string
     */
    protected $label;


    /**
     * Constructeur.
     *
     * @param Keyword $keyword
     */
    public function __construct(Keyword $keyword)
    {
        $this->ref = $keyword->getRef();
        $this->label = $keyword->getLabel();
    }

    /**
     * Retourne le label du mot-cle (ou null si le mot-cle n'est pas valide).
     *
     * @return string Label
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Retourne le label du mot-cle.
     *
     * @return string Label
     */
    public function getLabel()
    {
        return $this->label;
    }

}
