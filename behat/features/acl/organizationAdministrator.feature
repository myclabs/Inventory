@dbFull
Feature: Organization administrator feature

  @javascript
  Scenario: Administrator of a single organization
    Given I am on the homepage
    And I wait for the page to finish loading
  # Login en tant qu'utilisateur connecté
    When I fill in "email" with "administrateur.organisation@toto.com"
    And I fill in "password" with "administrateur.organisation@toto.com"
    And I click "connection"
  # On tombe sur la page de la cellule
    Then I should see the "organizations" datagrid
    And the "organizations" datagrid should contain 1 row