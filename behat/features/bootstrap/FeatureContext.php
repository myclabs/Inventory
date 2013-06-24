<?php
/**
 * @author matthieu.napoli
 */

use Behat\Behat\Context\Step;
use Behat\MinkExtension\Context\MinkContext;

require_once __DIR__ . '/../../../vendor/autoload.php';

/**
 * Features context.
 */
class FeatureContext extends MinkContext
{
    /**
     * @When /^I wait for page to finish loading$/
     */
    public function iWaitForPageToFinishLoading()
    {
        // Timeout de 6 secondes
        $this->getSession()->wait(6000, '(0 === jQuery.active)');
    }

    /**
     * @Given /^I am logged in$/
     */
    public function iAmLoggedIn()
    {
        return [
            new Step\Given('I am on the homepage'),
            new Step\Given('I fill in "email" with "admin"'),
            new Step\Given('I fill in "password" with "myc-53n53"'),
            new Step\Given('I press "connection"'),
            new Step\Given('I wait for page to finish loading'),
        ];
    }

    /**
     * @When /^I wait (?:|for )(?P<num>\d+) seconds$/
     */
    public function iWait($seconds)
    {
        $this->getSession()->wait($seconds * 1000);
    }
}
