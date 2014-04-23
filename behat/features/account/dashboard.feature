@dbFull
Feature: Dashboard

  Background:
    Given I am logged in

  @javascript @readOnly
  Scenario: The My C-Sense account should show public libraries
    Given I am on the dashboard for account "My C-Sense"
    Then I should see "Aucun workspace"
    And I should see the "Formulaires My C-Sense" AF library
    And I should see the "Paramètres My C-Sense" parameter library
    And I should see the "Classification My C-Sense" classification library

  @javascript @readOnly
  Scenario: I should be able to create a new AF library
    Given I am on the dashboard for account "My C-Sense"
    When I create a new "Test library" AF library
    Then the following message is shown and closed: "La bibliothèque a été créée."
    And I should see "Test library"
    And I should see "Liste des formulaires"

  @javascript @readOnly
  Scenario: I should be able to create a new parameter library
    Given I am on the dashboard for account "My C-Sense"
    When I create a new "Test library" parameter library
    Then the following message is shown and closed: "La bibliothèque a été créée."
    And I should see "Test library"
    And I should see "Liste des familles"

  @javascript @readOnly
  Scenario: I should be able to create a new classification library
    Given I am on the dashboard for account "My C-Sense"
    When I create a new "Test library" classification library
    Then the following message is shown and closed: "La bibliothèque a été créée."
    And I should see "Test library"

  @javascript @readOnly
  Scenario: I visit an account that doesn't have libraries
    Given I am on the dashboard for account "Pizza Forever Inc."
    Then I should see "Aucune bibliothèque de formulaires"
    And I should see "Aucune bibliothèque de paramètres"
    And I should see "Aucune bibliothèque de classification"
