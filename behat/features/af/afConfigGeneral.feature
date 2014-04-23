@dbFull
Feature: AF configuration general tab feature

  Background:
    Given I am logged in

  @javascript
  Scenario: AF configuration general tab scenario
    Given I am on "af/edit/menu/id/7"
    And I wait for the page to finish loading

    Then the "label" field should contain "Formulaire vide"
    And the "documentation" field should contain ""

    When I fill in "label" with "Test"
    And I fill in "documentation" with "Blabla"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Modification effectuée."

    And the "label" field should contain "Test"
    And the "documentation" field should contain "Blabla"
