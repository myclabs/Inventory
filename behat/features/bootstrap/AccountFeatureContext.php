<?php

use Behat\Mink\Element\NodeElement;
use Behat\Mink\WebAssert;

/**
 * @author matthieu.napoli
 */
trait AccountFeatureContext
{
    /**
     * @Given /^I am on the dashboard for account (\d+)$/
     */
    public function iAmOnTheDashboardForAccount($id)
    {
        $this->visit('account/dashboard/index/id/' . $id);
    }

    /**
     * @When /^I switch to account "([^"]*)"$/
     */
    public function iSwitchToAccount($account)
    {
        $inputNode = $this->findElement('#accountSwitcher');
        $inputNode->selectOption($account, false);
    }

    /**
     * @Then /^I should see the "([^"]*)" AF library$/
     */
    public function iShouldSeeTheAFLibrary($name)
    {
        $this->assertSession()->elementExists('css', ".afLibrary:contains(\"$name\")");
    }

    /**
     * @Then /^I should see the "([^"]*)" parameter library$/
     */
    public function iShouldSeeTheParameterLibrary($name)
    {
        $this->assertSession()->elementExists('css', ".parameterLibrary:contains(\"$name\")");
    }

    /**
     * @param string|null $name
     * @return WebAssert
     */
    public abstract function assertSession($name = null);
    /**
     * @param string $page
     */
    public abstract function visit($page);
    /**
     * Finds element with specified selector.
     * @param string $selector
     * @param string $type
     * @return NodeElement
     */
    protected abstract function findElement($selector, $type = 'css');
}
