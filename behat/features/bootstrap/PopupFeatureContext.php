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
}
