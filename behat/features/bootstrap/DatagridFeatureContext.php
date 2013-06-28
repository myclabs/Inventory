<?php
/**
 * @author matthieu.napoli
 */

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ElementTextException;
use Behat\Mink\WebAssert;

trait DatagridFeatureContext
{
    /**
     * @param string|null $name
     * @return WebAssert
     */
    public abstract function assertSession($name = null);
    public abstract function assertElementContainsText($element, $text);
    public abstract function waitForPageToFinishLoading();
    public abstract function clickElement($selector);

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
     * @Then /^the column "(?P<column>[^"]*)" of the row (?P<row>\d+) of the "(?P<datagrid>[^"]*)" datagrid should contain "(?P<content>[^"]*)"$/
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
    public function clickInRow($link, $row, $datagrid)
    {
        $linkSelector = $this->getDatagridSelector($datagrid)
            . " .yui-dt-data tr:nth-child($row)"
            . " a:contains('$link')";

        $this->clickElement($linkSelector);
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
