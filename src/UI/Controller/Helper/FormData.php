<?php
/**
 * @author     matthieu.napoli
 * @package    UI
 * @subpackage Form
 */

/**
 * Helper for form submissions
 * @package    UI
 * @subpackage Form
 */
class UI_Controller_Helper_FormData
{

    protected $formData = [];

    /**
     * @var Zend_Controller_Request_Abstract
     */
    protected $request;

    /**
     * @param array                            $data
     * @param Zend_Controller_Request_Abstract $request
     */
    public function __construct($data, $request)
    {
        $this->formData = $data;
        $this->request = $request;
    }

    /**
     * Cherche une valeur du formulaire seulement dans les champs visibles, puis ceux cachés, puis dans l'URL
     * @param string $field
     * @return mixed
     */
    public function getValue($field)
    {
        if (isset($this->formData[$field]['value'])
            && $this->formData[$field]['value'] !== null
            && $this->formData[$field]['value'] !== ''
        ) {
            return $this->formData[$field]['value'];
        }
        if (isset($this->formData[$field]['hiddenValues'][$field])
            && $this->formData[$field]['hiddenValues'][$field] !== null
            && $this->formData[$field]['hiddenValues'][$field] !== ''
        ) {
            return $this->formData[$field]['hiddenValues'][$field];
        }
        if ($this->request->getParam($field) !== null) {
            return $this->request->getParam($field);
        }
        return null;
    }

    /**
     * Cherche une valeur du formulaire seulement dans les champs cachés
     * @param string $field
     * @param string $hiddenValueName
     * @return mixed
     */
    public function getHiddenValue($field, $hiddenValueName)
    {
        if (isset($this->formData[$field]['hiddenValues'][$hiddenValueName])
            && $this->formData[$field]['hiddenValues'][$hiddenValueName] !== null
            && $this->formData[$field]['hiddenValues'][$hiddenValueName] !== ''
        ) {
            return $this->formData[$field]['hiddenValues'][$hiddenValueName];
        }
        return null;
    }

}
