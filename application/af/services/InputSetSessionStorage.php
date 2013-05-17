<?php
/**
 * @author     matthieu.napoli
 * @package    AF
 * @subpackage Service
 */

/**
 * @package    AF
 * @subpackage Service
 */
class AF_Service_InputSetSessionStorage extends Core_Singleton
{

    const SESSION_EXPIRATION = 3600;

    /**
     * @param AF_Model_AF $af
     * @param bool        $createIfNotFound Si l'InputSet n'est pas trouvé, en crée un nouveau automatiquement
     * @return AF_Model_InputSet_Primary|null
     */
    public function getInputSet(AF_Model_AF $af, $createIfNotFound = true)
    {
        // On cherche la saisie en session
        $session = $this->getSessionStorage();
        if (!isset($session->inputSet[$af->getId()])) {
            if ($createIfNotFound) {
                $inputSet = new AF_Model_InputSet_Primary($af);
                /** @noinspection PhpUndefinedFieldInspection */
                $session->inputSet[$af->getId()] = $inputSet;
            } else {
                $inputSet = null;
            }
        } else {
            $inputSet = $session->inputSet[$af->getId()];
        }
        return $inputSet;
    }

    /**
     * @param AF_Model_AF               $af
     * @param AF_Model_InputSet_Primary $inputSet
     */
    public function saveInputSet(AF_Model_AF $af, AF_Model_InputSet_Primary $inputSet)
    {
        $session = $this->getSessionStorage();
        /** @noinspection PhpUndefinedFieldInspection */
        $session->inputSet[$af->getId()] = $inputSet;
    }

    /**
     * @return Zend_Session_Namespace
     */
    protected function getSessionStorage()
    {
        $session = new Zend_Session_Namespace(get_class());
        $session->setExpirationSeconds(self::SESSION_EXPIRATION);
        if (!is_array($session->inputSet)) {
            /** @noinspection PhpUndefinedFieldInspection */
            $session->inputSet = [];
        }
        return $session;
    }

}
