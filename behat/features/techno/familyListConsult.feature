@dbFull
Feature: Family list consult feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Family list consult scenario
    Given I am on "techno/family/list"
    Then I should see the "familyDatagrid" datagrid
    And the row 1 of the "familyDatagrid" datagrid should contain:
      | category                        | label                                               | ref                                | type               | unit           |
      | Catégorie contenant une famille | Combustion de combustible, mesuré en unité de masse | combustion_combustible_unite_masse | Facteur d'émission | kg équ. CO2/kg |

