<?php

namespace Keyword\Domain;

use Core\Domain\EntityRepository;

/**
 * Gère les Keyword.
 * @author valentin.claras
 */
interface KeywordRepository extends EntityRepository
{
    /**
     * Retourne un Keyword grâce à son ref.
     *
     * @param string $keywordRef
     * @throws \Core_Exception_NotFound
     * @throws \Core_Exception_TooMany
     * @return Keyword
     */
    function getOneByRef($keywordRef);

    /**
     * Charge la liste des Keyword ne possédant pas d'association en tant qu'objet.
     *
     * @return Keyword[]
     */
    function getRoots();

}
