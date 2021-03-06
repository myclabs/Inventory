@dbFull
Feature: Family list consult feature

  Background:
    Given I am logged in

  @javascript @readOnly
  Scenario: Family list consult scenario
  # Affichage du datagrid
    Given I am on "parameter/library/view/id/1"
    And I wait for the page to finish loading
    Then I should see the "familyDatagrid" datagrid
    And the row 1 of the "familyDatagrid" datagrid should contain:
      | category                        | label                                               | ref                                | unit          |
      | Catégorie contenant une famille | Combustion de combustible, mesuré en unité de masse | combustion_combustible_unite_masse | kg équ. CO2/t |
    And the row 2 of the "familyDatagrid" datagrid should contain:
      | category                        | label                          | ref                         | unit |
      | Catégorie contenant une famille | Masse volumique de combustible | masse_volumique_combustible | t/m³ |
  # Lien "Cliquer pour accéder"
    When I click "Cliquer pour accéder" in the row 1 of the "familyDatagrid" datagrid
    Then I should see a "h1:contains('Combustion de combustible, mesuré en unité de masse')" element
