@dbFull
Feature: Family list consult feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Family list consult scenario
  # Affichage du datagrid
    Given I am on "techno/family/list"
    And I wait for the page to finish loading
    Then I should see the "familyDatagrid" datagrid
    And the row 1 of the "familyDatagrid" datagrid should contain:
      | category                        | label                                               | ref                                | type               | unit           |
      | Catégorie contenant une famille | Combustion de combustible, mesuré en unité de masse | combustion_combustible_unite_masse | Facteur d'émission | kg équ. CO2/t |
    And the row 3 of the "familyDatagrid" datagrid should contain:
      | category                        | label                          | ref                         | type        | unit |
      | Catégorie contenant une famille | Masse volumique de combustible | masse_volumique_combustible | Coefficient | t/m³ |
  # Lien "Cliquer pour accéder"
    When I click "Cliquer pour accéder" in the row 1 of the "familyDatagrid" datagrid
    Then I should see a "h1:contains('Combustion de combustible, mesuré en unité de masse')" element
  # Vérification qu'on est bien en consultation
    When I open tab "Documentation"
    Then I should see "Il n'y a aucune documentation pour cette famille."
  # Vérification de l'unité affichée
    And I should see "Unité : kg équ. CO2/t"
  # Vérification de l'unité affichée
    When I am on "techno/family/list"
    And I wait for the page to finish loading
    And I click "Cliquer pour accéder" in the row 3 of the "familyDatagrid" datagrid
    Then I should see a "h1:contains('Masse volumique de combustible')" element
    And I should see "Unité : t/m³"