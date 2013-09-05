<?php

namespace Keyword\Application\Service;

use Keyword\Domain\KeywordRepository;
/**
 * Service Keyword.
 * @author valentin.claras
 */
class KeywordService
{
    /**
     * @var KeywordRepository
     */
    protected $keywordRepository;


    /**
     * @param KeywordRepository $keywordRepository
     */
    public function __construct(KeywordRepository $keywordRepository)
    {
        $this->keywordRepository = $keywordRepository;
    }

    /**
     * @param string $keywordRef
     * @return KeywordDTO
     */
    public function get($keywordRef)
    {
        return new KeywordDTO($this->keywordRepository->getOneByRef($keywordRef));
    }

    /**
     * @return KeywordDTO[]
     */
    public function getAll()
    {
        $keywords = [];
        foreach ($this->keywordRepository->getAll() as $keyword) {
            $keywords[] = new KeywordDTO($keyword);
        }
        return $keywords;
    }

}
