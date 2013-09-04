<?php

namespace Keyword\Application;

/**
 * Service Keyword.
 * @author valentin.claras
 */
class KeywordService
{
    /**
     * @var \Keyword\Domain\KeywordRepository
     */
    protected $keywordRepository;

    /**
     * @param string $keywordRef
     * @return KeywordDTO
     */
    public function get($keywordRef)
    {
        return new KeywordDTO($this->keywordRepository->getOneByRef($keywordRef));
    }

}
