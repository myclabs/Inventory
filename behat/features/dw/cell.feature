@dbFull
Feature: Cell dataware analysis feature

  Background:
    Given I am logged in

  @javascript
  Scenario: New cell analysis scenario
  # Accès à l'onglet "Analyses" et au datagrid des analyses (cellule globale)
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Analyses"
    Then I should see the "report" datagrid
  # Nouvelle analyse
    When I click "Nouvelle analyse"
    And I click "Lancer"
    Then the field "indicatorRatio" should have error: "Merci de préciser la nature des valeurs à fournir."
    And the field "chartType" should have error: "Merci de préciser le type de graphique à afficher."
  # Retour
    When I click "Retour"
    Then I should see "Vue globale Organisation avec données"

  @javascript
  Scenario: Cell existing preconfigured analysis scenario
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Analyses"
    Then I should see the "report" datagrid
    And the row 2 of the "report" datagrid should contain:
      | label                        |
      | Chiffre d'affaire, par année |
    When I click "Cliquer pour accéder" in the row 1 of the "report" datagrid
