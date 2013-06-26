<?php
/**
 * @author matthieu.napoli
 */

use Behat\Behat\Context\Step;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ElementTextException;
use Behat\Mink\Exception\ExpectationException;
use Behat\MinkExtension\Context\MinkContext;

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once 'DatagridFeatureContext.php';
require_once 'PopupFeatureContext.php';

/**
 * Features context.
 */
class FeatureContext extends MinkContext
{
    use DatagridFeatureContext;
    use PopupFeatureContext;

    /**
     * @Given /^the database is "(?P<db>[^"]*)"$/
     */
    public function assertDatabase($db)
    {
    }

    /**
     * @Given /^(?:|I )am logged in$/
     */
    public function assertLoggedIn()
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
     * @When /^(?:|I )wait for (?:|the )page to finish loading$/
     */
    public function waitForPageToFinishLoading()
    {
        $jqueryOK = '0 === jQuery.active';
        $yuiOK = '($(".yui-dt").length == 0) || ($(".yui-dt-data>tr").length > 0)';

        // Timeout de 6 secondes
        $this->getSession()->wait(6000, "($jqueryOK) && ($yuiOK)");
    }

    /**
     * @When /^(?:|I )wait (?:|for )(?P<seconds>\d+) seconds$/
     */
    public function wait($seconds)
    {
        $this->getSession()->wait($seconds * 1000);
    }

    /**
     * @Then /^the field "(?P<field>[^"]*)" should have error: "(?P<error>[^"]*)"$/
     */
    public function assertFieldHasError($field, $error)
    {
        $field = $this->fixStepArgument($field);
        $error = $this->fixStepArgument($error);

        $node = $this->assertSession()->fieldExists($field);
        $fieldId = $node->getAttribute('id');

        $expression = '$("#' . $fieldId . '").parents(".controls").children(".errorMessage").text()';

        $errorMessage = $this->getSession()->evaluateScript("return $expression;");

        if ($errorMessage != $error) {
            throw new ExpectationException("No error message '$error' for field '$field'.\n"
                . "Error message found: '$errorMessage'.\n"
                . "Javascript expression: '$expression'.", $this->getSession());
        }
    }
}
