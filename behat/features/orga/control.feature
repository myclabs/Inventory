@dbFull
Feature: Control of organizational data feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Control of organizational data scenario
  # Accès à l'onglet "Contrôle"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Organisation"
    And I open tab "Contrôle"
    Then I should see the "consistency" datagrid
    And the row 1 of the "consistency" datagrid should contain:
      | control                        | diagnostic | failure  |
      | Axe ne contenant aucun membre  | NOT OK     | Axe vide |
    # TODO préciser contenu failure
    And the row 2 of the "consistency" datagrid should contain:
      | control                                    | diagnostic | failure |
      | Membre pour lequel manque un membre parent | NOT OK     |         |
    And the row 3 of the "consistency" datagrid should contain:
      | control                                           | diagnostic | failure                                |
      | Membre sans enfant d'un axe non situé à la racine | NOT OK     | Axe: Marque; membre : Marque sans site |
