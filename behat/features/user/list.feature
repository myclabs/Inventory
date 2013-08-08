@dbEmpty
Feature: User list feature

  Background:
    Given I am logged in

  @javascript
  Scenario: User list content scenario
    Given I am on "user/profile/list"
    Then I should see the "users" datagrid
    And the "users" datagrid should contain 1 row
    And the row 1 of the "users" datagrid should contain:
      | nom            | email | emailValidated | enabled | detailsUser |
      | Administrateur | admin | Effectuée  | Activé  | Éditer      |

  @javascript
  Scenario: Adding user with empty form
    Given I am on "user/profile/list"
    When I follow "Ajouter"
    Then I should see the popup "Ajout d'un compte utilisateur"
    When I press "Valider"
    And I wait for the page to finish loading
    Then the field "users_email_addForm" should have error: "Merci de renseigner ce champ."
