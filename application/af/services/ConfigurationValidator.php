<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @package AF
 */

/**
 * Classe responsable de la vérification de la configuration des AF
 * @package AF
 */
class AF_Service_ConfigurationValidator extends Core_Singleton
{

    /**
     * Méthode qui gère le controle de la configuration des AFs.
     * @param AF_Model_AF $af
     * @return AF_ConfigError[]
     */
    public function validateAF(AF_Model_AF $af)
    {
        return array_merge(
            $this->validateFormElements($af),
            $this->validateAlgos($af),
            $this->validateConditions($af)
        );
    }

    /**
     * Méthode qui gère le control de la configuration des champs d'un Af.
     * @param AF_Model_AF $af
     * @return AF_ConfigError[]
     */
    protected function validateFormElements(AF_Model_AF $af)
    {
        return $af->getRootGroup()->checkConfig();
    }

    /**
     * Méthode qui gère le control de la configuration des algos associés à un Af.
     * @param AF_Model_AF $af
     * @return AF_ConfigError[]
     */
    protected function validateAlgos(AF_Model_AF $af)
    {
        // Vérifie les algos de l'AF
        $errors = $this->toAFConfigErrors($this->getErrors($af->getAlgos()), $af);
        // Valide les algos des sous-AF
        foreach ($af->getSubAfList() as $subAF) {
            $calledAF = $subAF->getCalledAF();
            $errors = array_merge($errors,
                                  $this->toAFConfigErrors($this->getErrors($calledAF->getAlgos()), $calledAF));
        }
        return $errors;
    }

    /**
     * Méthode qui gère le control de la configuration des conditions associées à un Af.
     * @param AF_Model_AF $af
     * @return AF_ConfigError[]
     */
    protected function validateConditions(AF_Model_AF $af)
    {
        return $this->getErrors($af->getConditions());
    }

    /**
     * Méthode qui récupère les erreurs sur une liste d'éléments
     * @param Algo_Model_Algo[]|AF_Model_Component[]|AF_Model_Condition[] $elementsList
     * @return array
     */
    protected function getErrors($elementsList)
    {
        $errors = [];
        foreach ($elementsList as $element) {
            $errors = array_merge($errors, $element->checkConfig());
        }
        return $errors;
    }

    /**
     * @param Algo_ConfigError[] $errors
     * @param AF_Model_AF        $af
     * @return AF_ConfigError[]
     */
    protected function toAFConfigErrors(array $errors, AF_Model_AF $af)
    {
        $returnedArray = [];
        foreach ($errors as $error) {
            $returnedArray[] = $this->toAFConfigError($error, $af);
        }
        return $returnedArray;
    }

    /**
     * @param Algo_ConfigError $error
     * @param AF_Model_AF      $af
     * @return AF_ConfigError
     */
    protected function toAFConfigError(Algo_ConfigError $error, AF_Model_AF $af)
    {
        return new AF_ConfigError($error->getMessage(), $error->getFatal(), $af);
    }

}
