@dbFull
Feature: Control of organizational data feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Control of organizational data scenario
  # Accès à l'onglet "Contrôle"
    Given I am on "orga/organization/edit/workspace/1"
    And I wait for the page to finish loading
    And I open tab "Contrôle"
    Then I should see the "consistency1" datagrid
    And the row 1 of the "consistency1" datagrid should contain:
      | control                        | diagnostic | failure  |
      | Axe ne contenant aucun élément  | NOT OK     | Axe vide |
    And the row 2 of the "consistency1" datagrid should contain:
      | control                                    | diagnostic | failure |
      | Élément pour lequel manque un élément parent | OK         |         |
    And the row 3 of the "consistency1" datagrid should contain:
      | control                                           | diagnostic | failure                                   |
      | Élément sans enfant d'un axe non situé à la racine | NOT OK     | Axe : Marque ; élément : Marque sans site |
