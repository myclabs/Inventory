<?php
/**
 * @author matthieu.napoli
 */

use Behat\Behat\Context\Step;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Session;
use Behat\Mink\WebAssert;

trait DatagridFeatureContext
{
    /**
     * @Then /^(?:|I )should see the "(?P<datagrid>[^"]*)" datagrid$/
     */
    public function assertDatagridVisible($datagrid)
    {
        $this->waitForPageToFinishLoading();

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
     * @Then /^(?:|the )column "(?P<column>[^"]*)" of (?:|the )row (?P<row>\d+) of the "(?P<datagrid>[^"]*)" datagrid should contain "(?P<content>[^"]*)"$/
     */
    public function assertDatagridCellContains($column, $row, $datagrid, $content)
    {
        $cellSelector = $this->getDatagridSelector($datagrid)
            . " .yui-dt-data tr:nth-child($row)"
            . " .yui-dt-col-$column";

        try {
            $this->assertElementContainsText($cellSelector, $content);
        } catch (\Exception $e) {
            $message = sprintf("The text '%s' was not found at line %s and column '%s'. \n\nOriginal message: %s",
                $content, $row, $column, $e->getMessage());
            throw new \Exception($message);
        }
    }

    /**
     * @When /^(?:|I )click "(?P<link>[^"]*)" in the row (?P<row>\d+) of the "(?P<datagrid>[^"]*)" datagrid$/
     */
    public function clickLinkInRow($link, $row, $datagrid)
    {
        $linkSelector = $this->getDatagridSelector($datagrid)
            . " .yui-dt-data tr:nth-child($row)"
            . " a:contains('$link')";

        $this->clickElement($linkSelector);
    }

    /**
     * @Then /^(?:|I )set "(?P<content>[^"]*)" for (?:|the )column "(?P<column>[^"]*)" of (?:|the )row (?P<row>\d+) of the "(?P<datagrid>[^"]*)" datagrid$/
     */
    public function setCellContent($content, $column, $row, $datagrid)
    {
        $cellSelector = $this->getDatagridSelector($datagrid)
            . " .yui-dt-data tr:nth-child($row)"
            . " .yui-dt-col-$column";

        // Double-click
        $cellNode = $this->findElement($cellSelector);
        $cellNode->doubleClick();
        $this->waitForPageToFinishLoading();

        $popupSelector = '.yui-dt-editor:not([style*="display: none"])';

        // Text field
        $inputNodes = $this->findAllElements("$popupSelector input, $popupSelector select");
        if (count($inputNodes) === 1) {
            /** @var NodeElement $inputNode */
            $inputNode = current($inputNodes);
            if ($inputNode->getTagName() == 'input') {
                $inputNode->setValue($content);
            } else {
                // Select
                // Attend la fin du chargement
                $selectLoading = "$('$popupSelector select option:contains(\"Chargement\")').length == 0";
                $this->getSession()->wait(5000, "($selectLoading)");
                $inputNode->selectOption($content, false);
            }
        } else {
            // Radio
            $inputNodes = $this->findAllElements($popupSelector . ' input[type="radio"]');
            if (count($inputNodes) > 0) {
                $js = <<<JS
var inputId = $('.yui-dt-editor:visible label:contains("$content")').attr('for');
$("#" + inputId).prop('checked', true);
JS;
                $this->getSession()->executeScript($js);
            } else {
                // Select2
                $inputNodes = $this->findAllElements("$popupSelector .select2-container");
                if (count($inputNodes) === 1) {
                    $inputNode = $this->findElement("$popupSelector .select2-offscreen:not(.select2-focusser)");
                    if ($inputNode->getTagName() == 'select') {
                        $inputNode->selectOption($content, false);
                    } else {
                        $inputNode->setValue($content);
                    }
                } else {
                    // Textarea
                    $inputNodes = $this->findAllElements("$popupSelector textarea");
                    if (count($inputNodes) === 1) {
                        // Attend la fin du chargement
                        $textareaLoading = "$('$popupSelector textarea:contains(\"Chargement\")').length == 0";
                        $this->getSession()->wait(5000, "($textareaLoading)");
                        /** @var NodeElement $inputNode */
                        $inputNode = current($inputNodes);
                        $inputNode->setValue($content);
                    } else {
                        throw new \Exception("Unable to set cell value in datagrid");
                    }
                }
            }
        }

        // Submit
        $submitNode = $this->findElement("$popupSelector .yui-dt-button .yui-dt-default");
        $submitNode->click();

        $this->waitForPageToFinishLoading();
    }

    /**
     * @Then /^(?:|I )additionally select "(?P<content>[^"]*)" for (?:|the )column "(?P<column>[^"]*)" of (?:|the )row (?P<row>\d+) of the "(?P<datagrid>[^"]*)" datagrid$/
     */
    public function additionallySelectCell($content, $column, $row, $datagrid)
    {
        $cellSelector = $this->getDatagridSelector($datagrid)
            . " .yui-dt-data tr:nth-child($row)"
            . " .yui-dt-col-$column";

        // Double-click
        $cellNode = $this->findElement($cellSelector);
        $cellNode->doubleClick();
        $this->waitForPageToFinishLoading();

        $popupSelector = '.yui-dt-editor:not([style*="display: none"])';

        // Select
        $inputNode = $this->findElement("$popupSelector select");
        $inputNode->selectOption($content, true);

        // Submit
        $submitNode = $this->findElement("$popupSelector .yui-dt-button .yui-dt-default");
        $submitNode->click();

        $this->waitForPageToFinishLoading();
    }

    /**
     * @Then /^(?:|I )set "(?P<content>[^"]*)" for (?:|the )column "(?P<column>[^"]*)" of (?:|the )row (?P<row>\d+) of the "(?P<datagrid>[^"]*)" datagrid with a confirmation message$/
     */
    public function setCellContentConfirmationMessage($content, $column, $row, $datagrid)
    {
        $this->setCellContent($content, $column, $row, $datagrid);

        return [new Step\Then('the following message is shown and closed: "Modification effectuée."')];
    }

    /**
     * @param string $name Datagrid name
     * @return string
     */
    private function getDatagridSelector($name)
    {
        return "#{$name}_container.yui-dt";
    }

    /**
     * @param string|null $name
     * @return WebAssert
     */
    public abstract function assertSession($name = null);
    public abstract function assertElementContainsText($element, $text);
    public abstract function waitForPageToFinishLoading();
    public abstract function clickElement($selector);
    public abstract function fillField($field, $value);
    /**
     * @param string $selector
     * @param string $type
     * @return NodeElement
     */
    protected abstract function findElement($selector, $type = 'css');
    /**
     * @param string $cssSelector
     * @return NodeElement[]
     */
    protected abstract function findAllElements($cssSelector);
    /**
     * @param string|null $name
     * @return Session
     */
    public abstract function getSession($name = null);
}
