@dbFull
Feature: Cell form tab edition feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Cell form tab edition scenario, global input granularity
  # Accès à l'onglet "Formulaires" et à un des datagrids
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Formulaires"
    And I open collapse "Niveau organisationnel global"
    Then I should see the "aFGranularityConfig1" datagrid
  # Sélection d'un formulaire comptable (granularité de saisie plus grossière que la granularité des inventaires
    When I set "Combustion de combustible, mesuré en unité de masse" for column "aF" of row 1 of the "aFGranularityConfig1" datagrid with a confirmation message
    Then the row 1 of the "aFGranularityConfig1" datagrid should contain:
      | aF                                                  |
      | Combustion de combustible, mesuré en unité de masse |
  # Dé-sélection d'un formulaire comptable
    When I set "" for column "aF" of row 1 of the "aFGranularityConfig1" datagrid with a confirmation message
    Then the row 1 of the "aFGranularityConfig1" datagrid should contain:
      | aF |
      |    |


  @javascript
  Scenario: Cell form tab edition scenario, "Année | Catégorie | Site" input granularity
  # Accès à l'onglet "Formulaires" et à un des datagrids
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Formulaires"
    And I open collapse "Année | Catégorie"
    Then I should see the "aFGranularityConfig8" datagrid
  # Sélection d'un formulaire comptable (granularité de saisie plus grossière que la granularité des inventaires
    When I set "Combustion de combustible, mesuré en unité de masse" for column "aF" of row 1 of the "aFGranularityConfig8" datagrid with a confirmation message
    Then the row 1 of the "aFGranularityConfig8" datagrid should contain:
      | aF                                                  |
      | Combustion de combustible, mesuré en unité de masse |
  # Dé-sélection d'un formulaire comptable
    When I set "" for column "aF" of row 1 of the "aFGranularityConfig8" datagrid with a confirmation message
    Then the row 1 of the "aFGranularityConfig8" datagrid should contain:
      | aF |
      |    |