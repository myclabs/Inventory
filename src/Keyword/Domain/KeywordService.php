<?php

namespace Keyword\Domain;

/**
 * Service Keyword.
 * @author valentin.claras
 * @author bertrand.ferry
 */
class KeywordService
{
    /**
     * @var KeywordRepository
     */
    protected $keywordRepository;


    /**
     * Constructeur du Service Keyword.
     *
     * @param KeywordRepository $repository
     * @return \Keyword\Domain\KeywordService
     */
    public function __construct(KeywordRepository $repository)
    {
        $this->keywordRepository = $repository;
    }

    /**
     * Retourne le Keyword correspondant à la ref donnée.
     *
     * @param string $ref
     * @return Keyword
     */
    public function get($ref)
    {
        return $this->keywordRepository->getOneByRef($ref);
    }

    /**
     * Ajoute un Keyword.
     *
     * @param Keyword $keyword
     */
    public function add($keyword)
    {
        $this->checkRef($keyword->getRef());
        $this->keywordRepository->add($keyword);
    }

    /**
     * Supprime un Keyword.
     *
     * @param Keyword $keyword
     *
     * @return string Le label du Keyword.
     */
    public function remove($keyword)
    {
        $this->keywordRepository->remove($keyword);
    }

    /**
     * Renoie les messages d'erreur concernant la validation d'une ref.
     *
     * @param string $ref
     *
     * @return mixed string null
     */
    public function getErrorMessageForNewRef($ref)
    {
        try {
            \Core_Tools::checkRef($ref);
        } catch (\Core_Exception_User $e) {
            return $e->getMessage();
        }
        if ($ref === 'this') {
            return __('Keyword', 'list', 'keywordRefThis');
        }
        try {
            $existingKeywordWithRef = $this->keywordRepository->getOneByRef($ref);
            return __('UI', 'formValidation', 'alreadyUsedIdentifier', array('REF' => $ref));
        } catch (\Core_Exception_NotFound $e) {
            // Pas de Keyword trouvé.
        }
        return null;
    }

    /**
     * Vérifie la disponibilité d'une référence pour un keyword.
     *
     * @param string $ref
     *
     * @throws \Core_Exception_User
     */
    private function checkRef($ref)
    {
        \Core_Tools::checkRef($ref);
        if ($ref === 'this') {
            throw new \Core_Exception_User('Keyword', 'list', 'keywordRefThis');
        }
        try {
            $existingKeywordWithRef = $this->keywordRepository->getOneByRef($ref);
            throw new \Core_Exception_User('UI', 'formValidation', 'alreadyUsedIdentifier', array('REF' => $ref));
        } catch (\Core_Exception_NotFound $e) {
            // Pas de Keyword trouvé.
        }
    }

}
