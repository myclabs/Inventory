@dbFull
Feature: Cell form tab edition feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Cell form tab edition scenario, global input granularity
  # Accès à l'onglet "Formulaires" et à un des datagrids
    Given I am on "orga/workspace/edit/workspace/1"
    And I wait for the page to finish loading
    And I open tab "Formulaires"
  #  And I open collapse "Niveau organisationnel global" (ne fonctionne pas car il y en a deux)
    And I click "Niveau organisationnel global Niveau organisationnel global"
    Then I should see the "datagridCellAfs1" datagrid
  # Sélection d'un formulaire comptable (granularité de saisie plus grossière que la granularité des inventaires
    When I set "Combustion de combustible, mesuré en unité de masse" for column "af" of row 1 of the "datagridCellAfs1" datagrid with a confirmation message
    Then the row 1 of the "datagridCellAfs1" datagrid should contain:
      | af                                                  |
      | Combustion de combustible, mesuré en unité de masse |
  # Dé-sélection d'un formulaire comptable
    When I set "" for column "af" of row 1 of the "datagridCellAfs1" datagrid with a confirmation message
    Then the row 1 of the "datagridCellAfs1" datagrid should contain:
      | af |
      |    |


  @javascript
  Scenario: Cell form tab edition scenario, "Année | Catégorie | Site" input granularity
  # Accès à l'onglet "Formulaires" et à un des datagrids
    Given I am on "orga/workspace/edit/workspace/1"
    And I wait for the page to finish loading
    And I open tab "Formulaires"
    And I click "Année | Catégorie Année | Site | Catégorie"
    Then I should see the "datagridCellAfs9" datagrid
  # Sélection d'un formulaire comptable (granularité de saisie plus grossière que la granularité des inventaires
    When I set "Combustion de combustible, mesuré en unité de masse" for column "af" of row 1 of the "datagridCellAfs9" datagrid with a confirmation message
    Then the row 1 of the "datagridCellAfs9" datagrid should contain:
      | af                                                  |
      | Combustion de combustible, mesuré en unité de masse |
  # Dé-sélection d'un formulaire comptable
    When I set "" for column "af" of row 1 of the "datagridCellAfs9" datagrid with a confirmation message
    Then the row 1 of the "datagridCellAfs9" datagrid should contain:
      | af |
      |    |