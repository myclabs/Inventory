<?php

namespace Techno\Domain;

use Keyword\Application\Service\KeywordService;
use Techno\Domain\Family\Family;
use Techno\Domain\Meaning;

/**
 * Service de validation des données de Techno.
 * @author matthieu.napoli
 */
class ValidationService
{
    /**
     * @var KeywordService
     */
    protected $keywordService;


    /**
     * @param KeywordService $keywordService
     */
    public function __construct(KeywordService $keywordService)
    {
        $this->keywordService = $keywordService;
    }

    /**
     * Contrôle les références vers les mots-clés de Keyword
     * @return string[] Mots-clés inconnus
     */
    public function validateMeaningsKeywords()
    {
        $errors = [];

        /** @var $meanings Meaning[] */
        $meanings = Meaning::loadList();

        foreach ($meanings as $meaning) {
            $keyword = $meaning->getKeyword();
            if ($this->keywordService->exists($keyword)) {
                continue;
            }
            $errors[] = $keyword;
        }

        return $errors;
    }

    /**
     * Contrôle les références vers les mots-clés des tags des familles
     * @return array Mots-clés inconnus
     */
    public function validateFamilyTagsKeywords()
    {
        $errors = [];

        /** @var $families Family[] */
        $families = Family::loadList();

        foreach ($families as $family) {
            foreach ($family->getTags() as $tag) {
                $keyword = $tag->getValue();
                if ($this->keywordService->exists($keyword)) {
                    continue;
                }
                $errors[] = [
                    'family'  => $family,
                    'tag'     => $tag,
                    'keyword' => $keyword,
                ];
            }
        }

        return $errors;
    }

    /**
     * Contrôle les références vers les mots-clés des membres des familles
     * @return array Mots-clés inconnus
     */
    public function validateFamilyMembersKeywords()
    {
        $errors = [];

        /** @var $families Family[] */
        $families = Family::loadList();

        foreach ($families as $family) {
            foreach ($family->getDimensions() as $dimension) {
                foreach ($dimension->getMembers() as $member) {
                    $keyword = $member->getKeyword();
                    if ($this->keywordService->exists($keyword)) {
                        continue;
                    }
                    $errors[] = [
                        'family'    => $family,
                        'dimension' => $dimension,
                        'keyword'   => $keyword,
                    ];
                }
            }
        }

        return $errors;
    }
}
