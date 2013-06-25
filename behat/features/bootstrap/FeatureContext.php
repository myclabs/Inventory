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

/**
 * Features context.
 */
class FeatureContext extends MinkContext
{
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
     * @Then /^(?:|I )should see the popup "(?P<popup>[^"]*)"$/
     */
    public function assertPopupVisible($popup)
    {
        $jsCondition = '$(".modal:contains(\"' . $popup . '\"):visible").length > 0';

        // Timeout de 2 secondes
        $this->getSession()->wait(2000, $jsCondition);

        // Test that a popup is visible
        $return = $this->getSession()->evaluateScript("return $jsCondition;");
        if ($return == false) {
            throw new ExpectationException("No popup with title '$popup' is visible", $this->getSession());
        }

        $this->assertSession()->elementContains('css', '.modal .modal-header', $popup);

        // Petite pause pour l'animation du popup
        $this->getSession()->wait(300);
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

    /**
     * @Then /^(?:|I )should see the "(?P<datagrid>[^"]*)" datagrid$/
     */
    public function assertDatagridVisible($datagrid)
    {
        $this->assertSession()->elementExists('css', $this->getDatagridSelector($datagrid));
    }

    /**
     * @Then /^the "(?P<datagrid>[^"]*)" datagrid should contain (?P<num>\d+) row$/
     */
    public function assertDatagridNumRows($datagrid, $num)
    {
        $rowSelector = $this->getDatagridSelector($datagrid) . ' .yui-dt-data tr';

        $this->assertSession()->elementsCount('css', $rowSelector, $num);
    }

    /**
     * @Then /^the row (?P<row>\d+) of the "(?P<datagrid>[^"]*)" datagrid should contain:$/
     */
    public function assertDatagridRowContains($row, $datagrid, TableNode $fields)
    {
        foreach ($fields->getHash() as $line) {
            foreach ($line as $column => $content) {
                $this->assertDatagridCellContains($column, $row, $datagrid, $content);
            }
        }
    }

    /**
     * @Then /^the column "(?P<column>[^"]*)" of the row (?P<row>\d+) of the "(?P<datagrid>[^"]*)" datagrid should contain "(?P<content>[^"]*)"$/
     */
    public function assertDatagridCellContains($column, $row, $datagrid, $content)
    {
        $cellSelector = $this->getDatagridSelector($datagrid)
            . " .yui-dt-data tr:nth-child($row)"
            . " .yui-dt-col-$column";

        try {
            $this->assertElementContainsText($cellSelector, $content);
        } catch (ElementTextException $e) {
            $message = sprintf("The text '%s' was not found at line %s and column '%s'. \n\nOriginal message: %s",
                $content, $row, $column, $e->getMessage());
            throw new \Exception($message);
        }
    }

    /**
     * @param string $name Datagrid name
     * @return string
     */
    private function getDatagridSelector($name)
    {
        return "#{$name}_container.yui-dt";
    }
}
