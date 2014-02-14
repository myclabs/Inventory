@dbFull @skipped
Feature: Check uncertainty computation feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Check uncertainty computation scenario
    Given I am on "orga/cell/view/idCell/1"
    And I wait for the page to finish loading
  # Accès cellule "Annecy"
    And I click element "#granularity4 legend"
    And I click ".cell[data+tag='/1-zone:europe/1-pays:france/2-site:annecy/&/2-marque:marque_a/2-site:annecy/'] .fa-bar-chart-o"
    And I click element "#reports6 .modal-footer .fa-plus"
    And I click element "#indicatorRatio_indicator"
    And I select "Histogramme vertical" from "chartType"
    And I click "Lancer"
    Then the following message is shown and closed: "Analyse effectuée."
    When I open tab "Valeurs"
    Then the row 1 of the "reportValues" datagrid should contain:
      | valueAxiso_annee | valueDigital | valueUncertainty |
      | 2012             | 33,3         | 23%              |