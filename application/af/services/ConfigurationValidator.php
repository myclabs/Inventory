<?php
use AF\Domain\AF;
use AF\Domain\AFConfigurationError;
use AF\Domain\Condition\Condition;
use AF\Domain\Component\Component;
use AF\Domain\Algorithm\Algo;
use AF\Domain\Algorithm\AlgoConfigurationError;

/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @package AF
 */

/**
 * Classe responsable de la vérification de la configuration des AF
 * @package AF
 */
class AF_Service_ConfigurationValidator
{

    /**
     * Méthode qui gère le controle de la configuration des AFs.
     * @param \AF\Domain\AF $af
     * @return AlgoConfigurationError[]
     */
    public function validateAF(AF $af)
    {
        return array_merge(
            $this->validateFormElements($af),
            $this->validateAlgos($af),
            $this->validateConditions($af)
        );
    }

    /**
     * Méthode qui gère le control de la configuration des champs d'un Af.
     * @param AF $af
     * @return AlgoConfigurationError[]
     */
    protected function validateFormElements(AF $af)
    {
        return $af->getRootGroup()->checkConfig();
    }

    /**
     * Méthode qui gère le control de la configuration des algos associés à un Af.
     * @param \AF\Domain\AF $af
     * @return AlgoConfigurationError[]
     */
    protected function validateAlgos(AF $af)
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
     * @param \AF\Domain\AF $af
     * @return AlgoConfigurationError[]
     */
    protected function validateConditions(AF $af)
    {
        return $this->getErrors($af->getConditions());
    }

    /**
     * Méthode qui récupère les erreurs sur une liste d'éléments
     * @param Algo[]|Component[]|\AF\Domain\Condition\Condition[] $elementsList
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
     * @param AlgoConfigurationError[] $errors
     * @param \AF\Domain\AF        $af
     * @return AlgoConfigurationError[]
     */
    protected function toAFConfigErrors(array $errors, AF $af)
    {
        $returnedArray = [];
        foreach ($errors as $error) {
            $returnedArray[] = $this->toAFConfigError($error, $af);
        }
        return $returnedArray;
    }

    /**
     * @param AlgoConfigurationError $error
     * @param \AF\Domain\AF      $af
     * @return AlgoConfigurationError
     */
    protected function toAFConfigError(AlgoConfigurationError $error, AF $af)
    {
        return new AlgoConfigurationError($error->getMessage(), $error->getFatal(), $af);
    }

}
