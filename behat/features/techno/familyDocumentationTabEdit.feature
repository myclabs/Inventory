@dbFull
Feature: Family documentation tab edit feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Family edit documentation scenario
  # À partir d'une documentation vide
    Given I am on "techno/family/edit/id/3"
    And I wait for the page to finish loading
    Then I should see "Famille test vide"
    When I open tab "Documentation"
    And I fill in "documentation" with "h1. Test documentation"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Modification effectuée."
  # Vérification que la documentation est bien affichée en consultation
    Given I am on "techno/family/details/id/3"
    And I wait for the page to finish loading
    And I open tab "Documentation"
    Then I should see a "#container_documentation h1:contains('Test documentation')" element
  # Vérification que la documentation est bien réaffichée en édition
    Given I am on "techno/family/edit/id/3"
    And I open tab "Documentation"
    Then the "documentation" field should contain "h1. Test documentation"