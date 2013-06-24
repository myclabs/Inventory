Feature: Identification
  The login form authenticates users.

  Scenario: Login redirection
    Given I am on "/"
    Then I should see "Connexion"

  @javascript
  Scenario: Logging in with wrong password
    Given I am on "/user/action/login"
    When I fill in "email" with "admin"
    And I fill in "password" with "blahblah"
    And I press "Connexion"
    And I wait for page to finish loading
    Then I should see "Attention ! Le mot de passe indiqué est invalide."

  @javascript
  Scenario: Logging in correcly
    Given I am on "/user/action/login"
    When I fill in "email" with "admin"
    And I fill in "password" with "myc-53n53"
    And I press "Connexion"
    And I wait for page to finish loading
    Then I should see "Organisations"
