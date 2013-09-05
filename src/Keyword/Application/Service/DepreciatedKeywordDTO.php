<?php

namespace Keyword\Application\Service;

/**
 * @author valentin.claras
 */
class DepreciatedKeywordDTO extends KeywordDTO
{
    /**
     * @param string $oldKeywordRef
     */
    public function __construct($oldKeywordRef)
    {
        $this->ref = $oldKeywordRef;
        $this->label = $oldKeywordRef;
    }

}
