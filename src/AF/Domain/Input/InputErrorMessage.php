<?php

namespace AF\Domain\Input;

/**
 * Interface InputErrorMessage
 *
 * @author valentin.claras
 */
trait InputErrorMessage
{
    /**
     * @var string
     */
    protected $errorMessage = '';

    /**
     * @return bool
     */
    public function hasError()
    {
        return !empty($this->errorMessage);
    }

    /**
     * @param string $errorMessage
     * @return $this
     */
    public function setError($errorMessage = null)
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->errorMessage;
    }
}