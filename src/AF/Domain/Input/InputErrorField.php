<?php

namespace AF\Domain\Input;

/**
 * Interface InputErrorField
 *
 * @author valentin.claras
 */
interface InputErrorField
{
    /**
     * @return bool
     */
    public function hasError();

    /**
     * @param string $errorMessage
     * @return $this
     */
    public function setError($errorMessage = null);

    /**
     * @return string
     */
    public function getError();
}