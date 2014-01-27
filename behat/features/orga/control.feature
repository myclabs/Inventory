@dbFull
Feature: Control of organizational data feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Control of organizational data scenario
  # Accès à l'onglet "Contrôle"
    Given I am on "orga/organization/edit/idOrganization/1"
    And I wait for the page to finish loading
    And I open tab "Contrôle"
    Then I should see the "consistency1" datagrid
    And the row 1 of the "consistency1" datagrid should contain:
      | control                        | diagnostic | failure  |
      | Axe ne contenant aucun élément  | NOT OK     | Axe vide |
    And the row 2 of the "consistency" datagrid should contain:
      | control                                    | diagnostic | failure |
      | Élément pour lequel manque un élément parent | OK         |         |
    And the row 3 of the "consistency" datagrid should contain:
      | control                                           | diagnostic | failure                                   |
      | Élément sans enfant d'un axe non situé à la racine | NOT OK     | Axe : Marque ; élément : Marque sans site |
    And the row 4 of the "consistency" datagrid should contain:
      | control                                                                  | diagnostic | failure |
      | Niveau organisationnel manquant pour l'affichage de l'onglet "Collectes" | OK         |         |
  # Ajout de la granularité "Pays"
    And I open tab "Niveaux"
    And I click "Ajouter"
    Then I should see the popup "Ajout d'un niveau organisationnel"
    When I additionally select "Pays" from "granularity1_axes_addForm"
    # And I select "Navigable" in radio "Navigable"
    And I select "Oui" in radio "Rôles"
    And I click "Valider"
    And I wait 20 seconds
    Then the following message is shown and closed: "Ajout effectué."
    When I open tab "Contrôle"
    And I click "Renouveler le contrôle"
    Then the row 4 of the "consistency" datagrid should contain:
      | control                                                                  | diagnostic | failure                 |
      | Niveau organisationnel manquant pour l'affichage de l'onglet "Collectes" | NOT OK     | Année \| Pays \| Marque |