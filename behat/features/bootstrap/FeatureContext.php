<?php
/**
 * @author matthieu.napoli
 */

use Behat\Behat\Context\Step;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ExpectationException;
use Behat\MinkExtension\Context\MinkContext;
use WebDriver\Exception;

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
     * @BeforeScenario
     */
    public function setWindowSize()
    {
        $this->getSession()->resizeWindow(1280, 1024);
    }

    /**
     * @BeforeScenario
     */
    public function setLanguage()
    {
//        $this->getSession()->setRequestHeader('Accept-Language', 'fr');
    }

    /**
     * @Given /^(?:|I )am logged in$/
     */
    public function assertLoggedIn()
    {
        return [
            new Step\Given('I am on "user/action/login?refer=index%2Faccueil"'),
            new Step\Given('I fill in "email" with "admin"'),
            new Step\Given('I fill in "password" with "myc-53n53"'),
            new Step\Given('I press "connection"'),
            new Step\Given('I wait for page to finish loading'),
        ];
    }

    /**
     * @Then /^the following message is shown and closed: "(?P<message>(?:[^"]|\\")*)"$/
     */
    public function assertMessageShown($message)
    {
        return [
            new Step\Then('the "#messageZone" element should contain "' . $message . '"'),
            new Step\Then('I click element "#messageZone button.close"'),
            new Step\Then('I wait for 0.5 seconds'),
            new Step\Then('the "#messageZone" element should not contain "' . $message . '"'),
        ];
    }

    /**
     * @When /^(?:|I )wait for (?:|the )page to finish loading$/
     */
    public function waitForPageToFinishLoading()
    {
        $this->getSession()->wait(50);

        // Chargements AJAX
        $jqueryOK = '0 === jQuery.active';
        $datagridOK = '$(".yui-dt-message:contains(\"Chargement\"):visible").length == 0';
        $maskOK = '$("#loadingMask:visible").length == 0';
        // Timeout de 10 secondes
        $this->getSession()->wait(10000, "($jqueryOK) && ($datagridOK) && ($maskOK)");

        // Animations JS
        $popupOK = '$(".modal-backdrop:visible").length == 0';
        // Timeout de 1 s
        $this->getSession()->wait(1000, "($popupOK)");
    }

    /**
     * @When /^(?:|I )wait (?:|for )(?P<seconds>[\d\.]+) seconds$/
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

        if (strpos($errorMessage, $error) === false) {
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
     * Clicks an element found using CSS selectors.
     *
     * @When /^(?:|I )click element "(?P<selector>(?:[^"]|\\")*)"$/
     */
    public function clickElement($selector)
    {
        $node = $this->findElement($selector);
        $node->focus();
        $node->click();

        $this->waitForPageToFinishLoading();
    }

    /**
     * Focus on an element found using CSS selectors.
     *
     * @When /^(?:|I )focus on element "(?P<selector>(?:[^"]|\\")*)"$/
     */
    public function focusOnElement($selector)
    {
        $node = $this->findElement($selector);
        $node->focus();
    }

    /**
     * Clicks a button or link with specified id|title|alt|text.
     *
     * @When /^(?:|I )select "(?P<value>(?:[^"]|\\")*)" in radio "(?P<label>(?:[^"]|\\")*)"$/
     */
    public function selectRadio($value, $label)
    {
        $value = $this->fixStepArgument($value);

        $selector = ".control-group:contains(\"$label\") .input label:contains(\"$value\")>input";

        /** @var NodeElement[] $nodes */
        $nodes = $this->getSession()->getPage()->findAll('css', $selector);

        if (count($nodes) === 0) {
            throw new ExpectationException("No radio with label '$label' and value '$value' found.",
                $this->getSession());
        }

        $nodes = array_filter($nodes, function(NodeElement $node) {
                return $this->isElementVisible($node);
            });

        if (count($nodes) === 0) {
            throw new ExpectationException("No radio with label '$label' and value '$value' is visible.",
                $this->getSession());
        }

        if (count($nodes) > 1) {
            $nb = count($nodes);
            throw new ExpectationException("Too many ($nb) radio with label '$label' and value '$value' are visible.",
                $this->getSession());
        }

        /** @var NodeElement $node */
        $node = current($nodes);
        $node->check();
    }

    /**
     * Open a collapse with specified text.
     *
     * @When /^(?:|I )open collapse "(?P<collapse>(?:[^"]|\\")*)"$/
     * @When /^(?:|I )close collapse "(?P<collapse>(?:[^"]|\\")*)"$/
     */
    public function toggleCollapse($label)
    {
        $label = $this->fixStepArgument($label);
        $node = $this->findElement('//legend[text()[normalize-space(.)="' . $label . '"]]', 'xpath');

        $node->click();

        // Animation
        $this->wait(0.1);
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

        if ($node === null) {
            throw new ExpectationException("No tab with label '$label' was found.",
                $this->getSession());
        }

        $node->click();

        $this->waitForPageToFinishLoading();
    }

    /**
     * Finds link or button with specified locator.
     *
     * @param string $locator link id, title, text or image alt
     *
     * @throws ExpectationException Not found
     * @return NodeElement|null
     */
    private function findLinkOrButton($locator)
    {
        /** @var NodeElement[] $nodes */
        $nodes = $this->getSession()->getPage()->findAll(
            'named',
            array(
                 'link_or_button',
                 $this->getSession()->getSelectorsHandler()->xpathLiteral($locator)
            )
        );

        if (count($nodes) === 0) {
            throw new ExpectationException("No link or button with text, id or title '$locator' found.",
                $this->getSession());
        }

        $nodes = array_filter($nodes, function(NodeElement $node) {
                return $this->isElementVisible($node);
            });

        if (count($nodes) === 0) {
            throw new ExpectationException("No link or button with text, id or title '$locator' is visible.",
                $this->getSession());
        }

        if (count($nodes) > 1) {
            $nb = count($nodes);
            throw new ExpectationException("Too many ($nb) links or buttons with text, id or title '$locator' are visible.",
                $this->getSession());
        }

        return current($nodes);
    }

    /**
     * Finds element with specified selector.
     *
     * @param string $selector
     * @param string $type
     *
     * @throws Behat\Mink\Exception\ExpectationException
     * @return NodeElement
     */
    protected function findElement($selector, $type = 'css')
    {
        /** @var NodeElement[] $nodes */
        $nodes = $this->getSession()->getPage()->findAll($type, $selector);

        if (count($nodes) === 0) {
            throw new ExpectationException("No element matches selector '$selector'.",
                $this->getSession());
        }

        $nodes = array_filter($nodes, function(NodeElement $node) {
                return $this->isElementVisible($node);
            });

        if (count($nodes) === 0) {
            throw new ExpectationException("No element matching '$selector' is visible.",
                $this->getSession());
        }

        if (count($nodes) > 1) {
            $nb = count($nodes);
            throw new ExpectationException("Too many ($nb) elements matching '$selector' are visible.",
                $this->getSession());
        }

        return current($nodes);
    }

    /**
     * Finds elements with specified selector.
     *
     * @param string $cssSelector
     *
     * @return NodeElement[]
     */
    protected function findAllElements($cssSelector)
    {
        /** @var NodeElement[] $nodes */
        $nodes = $this->getSession()->getPage()->findAll('css', $cssSelector);

        $nodes = array_filter($nodes, function(NodeElement $node) {
                return $this->isElementVisible($node);
            });

        return $nodes;
    }

    private function isElementVisible(NodeElement $node)
    {
        if (!$node->isVisible()) {
            return false;
        }

        while ($node) {
            if (strpos($node->getAttribute('style'), 'height: 0') !== false) {
                return false;
            }
            $node = $node->getParent();

            if ($node->getTagName() == 'body') {
                $node = null;
            }
        }
        return true;
    }
}
