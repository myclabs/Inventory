@dbEmpty
Feature: User list

  Background:
    Given I am logged in

  @javascript @readOnly
  Scenario: User list content scenario
    Given I am on "user/profile/list"
    Then I should see the "users" datagrid
    And the "users" datagrid should contain 1 row
    And the row 1 of the "users" datagrid should contain:
      | prenom         | nom     | email               | enabled | detailsUser |
      | Administrateur | Système | admin@myc-sense.com | Activé  | Éditer      |
