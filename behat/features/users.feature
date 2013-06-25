Feature: Users
  User management.

  Background:
    Given I am logged in

  @javascript
  Scenario: User list
    Given I am on "user/profile/list"
    And I wait for the page to finish loading
    Then I should see the "users" datagrid
    And the "users" datagrid should contain 1 row
    And the row 1 of the "users" datagrid should contain:
      | nom            | email | detailsUser |
      | Administrateur | admin | Éditer      |

  @javascript
  Scenario: Adding user
    Given I am on "user/profile/list"
    And I wait for the page to finish loading
    When I follow "Ajouter"
    And I wait 1 seconds
    Then I should see the popup "utilisateur"
