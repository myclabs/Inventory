<?php
/**
 * @author  matthieu.napoli
 * @package Techno
 */

use Keyword\Application\Service\DepreciatedKeywordDTO;

/**
 * Service de validation des données de Techno
 * @package Techno
 */
class Techno_Service_Validator
{

    /**
     * Contrôle les références vers les mots-clés de Keyword
     * @return string[] Mots-clés inconnus
     */
    public function validateMeaningsKeywords()
    {
        $errors = [];

        /** @var $meanings Techno_Model_Meaning[] */
        $meanings = Techno_Model_Meaning::loadList();

        foreach ($meanings as $meaning) {
            $keyword = $meaning->getKeyword();
            if (! $keyword instanceof DepreciatedKeywordDTO) {
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

        /** @var $families Techno_Model_Family[] */
        $families = Techno_Model_Family::loadList();

        foreach ($families as $family) {
            foreach ($family->getTags() as $tag) {
                $keyword = $tag->getValue();
                if (! $keyword instanceof DepreciatedKeywordDTO) {
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

        /** @var $families Techno_Model_Family[] */
        $families = Techno_Model_Family::loadList();

        foreach ($families as $family) {
            foreach ($family->getDimensions() as $dimension) {
                foreach ($dimension->getMembers() as $member) {
                    $keyword = $member->getKeyword();
                    if (! $keyword instanceof DepreciatedKeywordDTO) {
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
