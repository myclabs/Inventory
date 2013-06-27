<?php
/**
 * @author matthieu.napoli
 */

use Behat\Behat\Context\Step;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ExpectationException;
use Behat\MinkExtension\Context\MinkContext;

define('APPLICATION_ENV', 'developpement');
define('RUN', false);

require_once __DIR__ . '/../../../application/init.php';

require_once 'DatabaseFeatureContext.php';
require_once 'DatagridFeatureContext.php';
require_once 'PopupFeatureContext.php';

/**
 * Features context.
 */
class FeatureContext extends MinkContext
{
    use DatabaseFeatureContext;
    use DatagridFeatureContext;
    use PopupFeatureContext;

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
        $datagridOK = '$(".yui-dt-message:contains(\"Chargement\"):visible").length == 0';

        // Timeout de 6 secondes
        $this->getSession()->wait(6000, "($jqueryOK) && ($datagridOK)");
    }

    /**
     * @When /^(?:|I )wait (?:|for )(?P<seconds>\d+) seconds$/
     */
    public function wait($seconds)
    {
        $this->getSession()->wait($seconds * 1000);
    }

    /**
     * @Then /^the field "(?P<field>[^"]*)" should have error: "(?P<error>(?:[^"]|\\")*)"$/
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

    /**
     * Clicks a button or link with specified id|title|alt|text.
     *
     * @When /^(?:|I )click "(?P<name>(?:[^"]|\\")*)"$/
     */
    public function click($name)
    {
        $name = $this->fixStepArgument($name);
        $node = $this->findLinkOrButton($name);
        $node->click();

        $this->waitForPageToFinishLoading();
    }

    /**
     * Open a collapse with specified text.
     *
     * @When /^(?:|I )open collapse "(?P<collapse>(?:[^"]|\\")*)"$/
     */
    public function openCollapse($label)
    {
        $label = $this->fixStepArgument($label);
        $node = $this->getSession()->getPage()->find(
            'css',
            'legend:contains("' . $label . '")'
        );
        $node->click();

        $this->waitForPageToFinishLoading();
    }

    /**
     * Open a collapse with specified text.
     *
     * @When /^(?:|I )open tab "(?P<label>(?:[^"]|\\")*)"$/
     */
    public function openTab($label)
    {
        $label = $this->fixStepArgument($label);
        $node = $this->getSession()->getPage()->find(
            'css',
            '.nav-tabs a:contains("' . $label . '")'
        );
        $node->click();

        $this->waitForPageToFinishLoading();
    }

    /**
     * Finds link with specified locator.
     *
     * @param string $locator link id, title, text or image alt
     *
     * @return NodeElement|null
     */
    private function findLinkOrButton($locator)
    {
        return $this->getSession()->getPage()->find(
            'named',
            array(
                 'link_or_button',
                 $this->getSession()->getSelectorsHandler()->xpathLiteral($locator)
            )
        );
    }
}
