<?php
/**
 * @author matthieu.napoli
 */

use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Session;
use Behat\Mink\WebAssert;

trait PopupFeatureContext
{
    /**
     * @param string|null $name
     * @return WebAssert
     */
    public abstract function assertSession($name = null);
    /**
     * @param string|null $name
     * @return Session
     */
    public abstract function getSession($name = null);

    /**
     * @Then /^(?:|I )should see the popup "(?P<popup>[^"]*)"$/
     */
    public function assertPopupVisible($popup)
    {
        $jsCondition = '$(".modal .modal-header:contains(\"' . $popup . '\"):visible").length > 0';

        // Timeout de 2 secondes
        $this->getSession()->wait(2000, $jsCondition);

        // Test that a popup is visible
        $this->assertSession()->elementExists('css', ".modal .modal-header:contains(\"$popup\")");

        // Petite pause pour l'animation du popup
        $this->getSession()->wait(300);
    }

    /**
     * @Then /^(?:|I )should see a popup$/
     */
    public function assertAPopupVisible()
    {
        $jsCondition = '$(".modal:visible").length > 0';

        // Timeout de 2 secondes
        $this->getSession()->wait(2000, $jsCondition);

        // Test that a popup is visible
        $this->assertSession()->elementExists('css', ".modal");

        // Petite pause pour l'animation du popup
        $this->getSession()->wait(300);
    }
}
