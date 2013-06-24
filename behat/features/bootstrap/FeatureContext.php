<?php
/**
 * @author matthieu.napoli
 */

use Behat\MinkExtension\Context\MinkContext;

require_once __DIR__ . '/../../../vendor/autoload.php';

/**
 * Features context.
 */
class FeatureContext extends MinkContext
{
    /**
     * @Given /^I wait for page to finish loading$/
     */
    public function iWaitForPageToFinishLoading()
    {
        $this->getSession()->wait(4000, '(0 === jQuery.active)');
    }
}
