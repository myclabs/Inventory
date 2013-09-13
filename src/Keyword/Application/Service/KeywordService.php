<?php

namespace Keyword\Application\Service;

use Keyword\Domain\Keyword;
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
     * @param KeywordDTO $keywordDTO
     * @return bool
     */
    public function exists(KeywordDTO $keywordDTO)
    {
        try {
            $this->keywordRepository->getByRef($keywordDTO->getRef());
            return true;
        } catch (\Core_Exception_NotFound $e) {
            return false;
        }
    }

    /**
     * @param string $keywordRef
     * @return KeywordDTO
     */
    public function get($keywordRef)
    {
        return $this->createDTO($this->keywordRepository->getByRef($keywordRef));
    }

    /**
     * @return KeywordDTO[]
     */
    public function getAll()
    {
        $keywords = [];
        foreach ($this->keywordRepository->getAll() as $keyword) {
            $keywords[] = $this->createDTO($keyword);
        }
        return $keywords;
    }

    /**
     * @param Keyword $keyword
     * @return KeywordDTO
     */
    protected function createDTO(Keyword $keyword)
    {
        return new KeywordDTO($keyword->getRef(), $keyword->getLabel());
    }
}
