<?php

use Behat\Mink\WebAssert;

/**
 * @author matthieu.napoli
 */
trait AccountFeatureContext
{
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
}
