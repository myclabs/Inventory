@dbFull
Feature: Dashboard

  Background:
    Given I am logged in

  @javascript @readOnly
  Scenario: The My C-Sense account should show public libraries
    Given I am on the dashboard for account "My C-Sense"
    Then I should see "Aucun workspace."
    And I should see the "Formulaires My C-Sense" AF library
    And I should see the "Paramètres My C-Sense" parameter library
