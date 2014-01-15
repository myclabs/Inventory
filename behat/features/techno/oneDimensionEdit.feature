@dbFull
Feature: Family one dimension edit feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Add member to a family dimension, correct input
    Given I am on "techno/family/edit/id/5"
    And I wait for the page to finish loading
  # Affichage famille et en-tête
    Then I should see "Famille test non vide"
    And I should see "Combustible : combustible"
    And I should see the "combustibleMembersDatagrid" datagrid
    And the "combustibleMembersDatagrid" datagrid should contain 2 row
    When I click element "#combustibleAddMemberButton"
    Then I should see the popup "Ajout d'une liste de membres"
    And I fill in "inputMemberList" with "amont_combustion, Amont de la combustion"
    Then I should see "1 membre(s) prêts à être ajoutés"
    When I click element "#addMemberPopup button:contains('Ajouter')"
    Then the following message is shown and closed: "1 membre(s) ont été ajouté(s)."
    And I wait 1 seconds
    And the "combustibleMembersDatagrid" datagrid should contain 3 row
  # Le nouvel élément a été ajouté à la fin
    And the row 3 of the "combustibleMembersDatagrid" datagrid should contain:
      | label                  | ref              |
      | Amont de la combustion | amont_combustion |

  @javascript
  Scenario: Add member to a family dimension, incorrect input
    Given I am on "techno/dimension/details/id/4"
    And I wait for the page to finish loading
    And I click "Ajouter"
    Then I should see the popup "Ajout d'une liste de membres"
    When I click "Valider"
    Then the field "Identifiant" should have error: "Merci de renseigner ce champ."
    And I click "Annuler"

  @javascript
  Scenario: Delete member
    Given I am on "techno/dimension/details/id/4"
    And I wait for the page to finish loading
  # Pour le contenu du datagrid voir plus haut
    When I click "Supprimer" in the row 1 of the "membersDatagrid" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    And the "membersDatagrid" datagrid should contain 1 row
