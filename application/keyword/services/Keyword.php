<?php
/**
 * Classe Keyword_Service_Keyword
 * @author valentin.claras
 * @author bertrand.ferry
 * @package Keyword
 */

/**
 * Service Keyword.
 * @package Keyword
 */
class Keyword_Service_Keyword extends Core_Service
{
    /**
     * Renvoie l'instance Singleton de la classe.
     *
     * @return Keyword_Service_Keyword
     */
    public static function getInstance()
    {
        return parent::getInstance();
    }

    /**
     * Ajoute un Keyword et le renvoie.
     *
     * @param string $ref
     * @param string $label
     *
     * @return Keyword_Model_Keyword
     */
    public function addService($ref, $label)
    {
        return $this->createKeyword($ref, $label);
    }

    /**
     * Change la ref d'un Keyword puis renvoie ce dernier.
     *
     * @param string $keywordRef
     * @param string $newRef
     *
     * @return Keyword_Model_Keyword
     */
    public function updateRefService($keywordRef, $newRef)
    {
        $keyword = Keyword_Model_Keyword::loadByRef($keywordRef);

        $this->checkKeywordRef($newRef);

        $keyword->setRef($newRef);

        return $keyword;
    }

    /**
     * Change le label d'un Keyword puis renvoie ce dernier.
     *
     * @param string $keywordRef
     * @param string $newLabel
     *
     * @return Keyword_Model_Keyword
     */
    public function updateLabelService($keywordRef, $newLabel)
    {
        $keyword = Keyword_Model_Keyword::loadByRef($keywordRef);

        $keyword->setLabel($newLabel);

        return $keyword;
    }

    /**
     * Change la ref et le label d'un Keyword puis renvoie ce dernier.
     *
     * @param string $keywordRef
     * @param string $newRef
     * @param string $newLabel
     *
     * @return Keyword_Model_Keyword
     */
    public function updateRefAndLabelService($keywordRef, $newRef, $newLabel)
    {
        $keyword = Keyword_Model_Keyword::loadByRef($keywordRef);

        $this->checkKeywordRef($newRef);

        $keyword->setRef($newRef);
        $keyword->setLabel($newLabel);

        return $keyword;
    }

    /**
     * Supprime un Keyword.
     *
     * @param string $keywordRef Ref du keyword
     *
     * @return string Le label du Keyword.
     */
    public function deleteService($keywordRef)
    {
        $keyword = Keyword_Model_Keyword::loadByRef($keywordRef);

        $keyword->delete();

        return $keyword->getLabel();
    }

    /**
     * Ajoute un Keyword avec une association en tant qu'objet et le renvoie.
     *
     * @param string $ref
     * @param string $label
     * @param string $subjectKeywordRef
     * @param bool   $predicateRef
     *
     * @return Keyword_Model_Keyword
     */
    public function addAsObjectInAssociationService($ref, $label, $subjectKeywordRef, $predicateRef)
    {
        $keyword = $this->createKeyword($ref, $label);

        $association = new Keyword_Model_Association();
        $association->setSubject(Keyword_Model_Keyword::loadByRef($subjectKeywordRef));
        $association->setObject($keyword);
        $association->setPredicate(Keyword_Model_Predicate::loadByRef($predicateRef));
        $association->save();

        return $keyword;
    }

    /**
     * Ajoute un Keyword avec une association en tant que sujet et le renvoie.
     *
     * @param string $ref
     * @param string $label
     * @param string $objectKeywordRef
     * @param bool   $predicateRef
     *
     * @return Keyword_Model_Keyword
     */
    public function addAsSubjectInAssociationService($ref, $label, $objectKeywordRef, $predicateRef)
    {
        $keyword = $this->createKeyword($ref, $label);

        $association = new Keyword_Model_Association();
        $association->setSubject($keyword);
        $association->setObject(Keyword_Model_Keyword::loadByRef($objectKeywordRef));
        $association->setPredicate(Keyword_Model_Predicate::loadByRef($predicateRef));
        $association->save();

        return $keyword;
    }

    /**
     * Créer un Keyword et le renvoie.
     *
     * @param string $ref
     * @param string $label
     *
     * @return Keyword_Model_Keyword
     */
    protected function createKeyword($ref, $label)
    {
        $keyword = new Keyword_Model_Keyword();

        if (empty($ref)) {
            $ref = $label;
        }
        $this->checkKeywordRef($ref);

        $keyword->setRef($ref);
        $keyword->setLabel($label);
        $keyword->save();

        return $keyword;
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
            Core_Tools::checkRef($ref);
        } catch (Core_Exception_User $e) {
            return $e->getMessage();
        }
        if ($ref === 'this') {
            return __('Keyword', 'list', 'keywordRefThis');
        }
        $queryRefUsed = new Core_Model_Query();
        $queryRefUsed->filter->addCondition(Keyword_Model_Keyword::QUERY_REF, $ref);
        if (Keyword_Model_Keyword::countTotal($queryRefUsed) > 0) {
            return __('UI', 'formValidation', 'alreadyUsedIdentifier', array('REF' => $ref));
        }

        return null;
    }

    /**
     * Vérifie la disponibilité d'une référence pour un keyword.
     *
     * @param string $ref
     *
     * @throws Core_Exception_User
     */
    protected function checkKeywordRef($ref)
    {
        Core_Tools::checkRef($ref);
        if ($ref === 'this') {
            throw new Core_Exception_User('Keyword', 'list', 'keywordRefThis');
        }
        $queryRefUsed = new Core_Model_Query();
        $queryRefUsed->filter->addCondition(Keyword_Model_Keyword::QUERY_REF, $ref);
        if (Keyword_Model_Keyword::countTotal($queryRefUsed) > 0) {
            throw new Core_Exception_User('UI', 'formValidation', 'alreadyUsedIdentifier',
                                            array('REF' => $ref));
        }
    }

}