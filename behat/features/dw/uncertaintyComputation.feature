@dbFull
Feature: Check uncertainty computation feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Check uncertainty computation scenario
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
  # Accès cellule "Annecy"
    And I click element ".fa-plus"
    And I click element "#goTo3"
    And I open tab "Analyses"
    And I click "Nouvelle analyse"
    And I click element "#indicatorRatio_indicator"
    And I select "Histogramme vertical" from "chartType"
    And I click "Lancer"
    Then the following message is shown and closed: "Analyse effectuée."
    When I open tab "Valeurs"
    Then the row 1 of the "reportValues" datagrid should contain:
      | valueAxiso_annee | valueDigital | valueUncertainty |
      | 2012             | 33,3         | 23%              |