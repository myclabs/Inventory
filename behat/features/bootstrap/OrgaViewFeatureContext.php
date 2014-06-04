<?php
/**
 * @author valentin.claras
 */

use Behat\Behat\Context\Step;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Session;
use Behat\Mink\WebAssert;

trait OrgaViewFeatureContext
{
    /**
     * @Then /^(?:|I )should see the "(?P<cell>[^"]*)" cell$/
     */
    public function assertCellVisible($cell)
    {
        $this->waitForPageToFinishLoading();

        $this->assertSession()->elementExists('css', $this->getCellSelector($cell));
    }

    /**
     * @When /^(?:|I )go input the "(?P<cell>[^"]*)" cell$/
     */
    public function assertCellInputLinkGo($cell)
    {
        $inputLinkSelector = $this->getCellSelector($cell) .
            ' td:not(.cell-member) a.go-input:not(.disabled)';

        $this->clickElement($inputLinkSelector);
    }

    /**
     * @Then /^the "(?P<cell>[^"]*)" cell input status should be "(?P<inputStatus>[^"]*)"$/
     */
    public function assertCellInputStatusVisible($cell, $inputStatus)
    {
        switch ($inputStatus) {
            case 'statusFinished':
                $inputStatusIcon = 'certificate';
                break;
            case 'statusComplete':
                $inputStatusIcon = 'check-square-o';
                break;
            case 'statusCalculationIncomplete':
                $inputStatusIcon = 'warning';
                break;
            case 'statusInputIncomplete':
                $inputStatusIcon = 'pencil-square-o';
                break;
            case 'statusNotStarted':
                $inputStatusIcon = 'square-o';
                break;
            case 'statusAFNotConfigured':
                $inputStatusIcon = 'wrench';
                break;
            default:
                throw new Exception('Invalid inputStatus ('.$inputStatus.') specified.');
        }
        $inputStatusIconSelector = $this->getCellSelector($cell) .
            '[data-input-status="' . $inputStatus . '"] td:not(.cell-member) ' .
            'span.input-status i.fa-' . $inputStatusIcon;

        $this->assertSession()->elementExists('css', $inputStatusIconSelector);
    }

    /**
     * @Then /^the "(?P<cell>[^"]*)" cell input link should be visible$/
     */
    public function assertCellInputLinkVisible($cell)
    {
        $inputStatusLinkSelector = $this->getCellSelector($cell) .
            ' td:not(.cell-member) a.go-input:not(.disabled)';

        $this->assertSession()->elementExists('css', $inputStatusLinkSelector);
    }

    /**
     * @Then /^the "(?P<cell>[^"]*)" cell input link should not be visible$/
     */
    public function assertCellInputLinkNotVisible($cell)
    {
        $inputStatusLinkSelector = $this->getCellSelector($cell) .
            ' td:not(.cell-member) a.go-input.disabled';

        $this->assertSession()->elementExists('css', $inputStatusLinkSelector);
    }

    /**
     * @Then /^the "(?P<cell>[^"]*)" cell inventory status should be "(?P<inventoryStatus>[^"]*)"$/
     */
    public function assertCellInventoryStatusVisible($cell, $inventoryStatus)
    {
        switch ($inventoryStatus) {
            case 'closed':
                $inventoryStatusIcon = 'lock';
                break;
            case 'active':
                $inventoryStatusIcon = 'unlock-alt';
                break;
            case 'notLaunched':
                $inventoryStatusIcon = 'ban';
                break;
            default:
                throw new Exception('Invalid inventoryStatus ('.$inventoryStatus.') specified.');
        }
        $inventoryStatusIconSelector = $this->getCellSelector($cell) .
            '[data-inventory-status="' . $inventoryStatus . '"] td:not(.cell-member) ' .
            'span.inventory-status i.fa-' . $inventoryStatusIcon;

        $this->assertSession()->elementExists('css', $inventoryStatusIconSelector);
    }

    /**
     * @Then /^the "(?P<cell>[^"]*)" cell inventory status should not be visible$/
     */
    public function assertCellInventoryStatusNotVisible($cell)
    {
        $inventoryStatusSelector = $this->getCellSelector($cell) .
            ' td:not(.cell-member) span.inventory-status';

        $this->assertSession()->elementNotExists('css', $inventoryStatusSelector);
    }

    /**
     * @Then /^the "(?P<cell>[^"]*)" cell inventory percent should be "(?P<percent>[^"]*)"$/
     */
    public function assertCellInventoryStatusPercent($cell, $percent)
    {
        $inventoryStatusSelector = $this->getCellSelector($cell) .
            ' td:not(.cell-member) div.inventory-progress ' .
            'div.inventory-progress-bar[style="width: ' . $percent . '%"]';

        $this->assertSession()->elementExists('css', $inventoryStatusSelector);
    }

    /**
     * @Then /^the "(?P<cell>[^"]*)" cell inventory percent should not be visible$/
     */
    public function assertCellInventoryStatusPercentNotVisible($cell)
    {
        $inventoryStatusSelector = $this->getCellSelector($cell) .
            ' td:not(.cell-member) div.inventory-progress';

        $this->assertSession()->elementExists('css', $inventoryStatusSelector);
    }

    /**
     * @Then /^the "(?P<cell>[^"]*)" cell inventory status should be editable$/
     */
    public function assertCellInventoryStatusEditable($cell)
    {
        $inventoryStatusActionsSelector = $this->getCellSelector($cell) .
            ' td:not(.cell-member) ' .
            'div.inventory-actions';

        $this->assertSession()->elementExists('css', $inventoryStatusActionsSelector);
    }

    /**
     * @Then /^the "(?P<cell>[^"]*)" cell inventory status should not be editable$/
     */
    public function assertCellInventoryStatusNotEditable($cell)
    {
        $inventoryStatusActionsSelector = $this->getCellSelector($cell) .
            ' td:not(.cell-member) div.inventory-actions';

        $this->assertSession()->elementNotExists('css', $inventoryStatusActionsSelector);
    }

    /**
     * @Then /^the "(?P<cell>[^"]*)" cell reports should be visible$/
     */
    public function assertCellReportsVisible($cell)
    {
        $reportsLinkSelector = $this->getCellSelector($cell) .
            ' td:not(.cell-member) a.show-reports';

        $this->assertSession()->elementExists('css', $reportsLinkSelector);
    }

    /**
     * @Then /^the "(?P<cell>[^"]*)" cell reports should not be visible$/
     */
    public function assertCellReportsNotVisible($cell)
    {
        $reportsLinkSelector = $this->getCellSelector($cell) .
            ' td:not(.cell-member) a.show-reports';

        $this->assertSession()->elementNotExists('css', $reportsLinkSelector);
    }

    /**
     * @Then /^the "(?P<cell>[^"]*)" cell users should be visible$/
     */
    public function assertCellUsersVisible($cell)
    {
        $usersLinkSelector = $this->getCellSelector($cell) .
            ' td:not(.cell-member) a.show-users';

        $this->assertSession()->elementExists('css', $usersLinkSelector);
    }

    /**
     * @Then /^the "(?P<cell>[^"]*)" cell users should not be visible$/
     */
    public function assertCellUsersNotVisible($cell)
    {
        $usersLinkSelector = $this->getCellSelector($cell) .
            ' td:not(.cell-member) a.show-users';

        $this->assertSession()->elementNotExists('css', $usersLinkSelector);
    }

    /**
     * @Then /^the "(?P<cell>[^"]*)" cell exports should be visible$/
     */
    public function assertCellExportsVisible($cell)
    {
        $exportsLinkSelector = $this->getCellSelector($cell) .
            ' td:not(.cell-member) a.show-exports';

        $this->assertSession()->elementExists('css', $exportsLinkSelector);
    }

    /**
     * @Then /^the "(?P<cell>[^"]*)" cell exports should not be visible$/
     */
    public function assertCellExportsNotVisible($cell)
    {
        $exportsLinkSelector = $this->getCellSelector($cell) .
            ' td:not(.cell-member) a.show-exports';

        $this->assertSession()->elementNotExists('css', $exportsLinkSelector);
    }

    /**
     * @param string $tag Cell tag.
     * @return string
     */
    private function getCellSelector($tag)
    {
        return 'table.granularity tr.cell[data-tag="'.$tag.'"]';
    }

    /**
     * @param string|null $name
     * @return WebAssert
     */
    public abstract function assertSession($name = null);
    public abstract function waitForPageToFinishLoading();
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
