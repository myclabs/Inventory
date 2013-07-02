@dbEmpty
Feature: Identification
  The login form authenticates users.

  Scenario: Login redirection
    Given I am on the homepage
    Then I should be on "user/action/login"
    And I should see "Connexion"
    And I should see a "#loginButton" element

  @javascript
  Scenario: Logging in with wrong password
    Given I am on the homepage
    When I fill in "email" with "admin"
    And I fill in "password" with "blahblah"
    And I press "connection"
    And I wait for page to finish loading
    Then I should see "Attention ! Le mot de passe indiqué est invalide."

  @javascript
  Scenario: Logging in correctly
    Given I am on the homepage
    When I fill in "email" with "admin"
    And I fill in "password" with "myc-53n53"
    And I press "connection"
    And I wait for page to finish loading
    Then I should see "Organisations"

  @javascript
  Scenario: Logging out
    Given I am logged in
    When I click "currentUserButton"
    And I click "logoutButton"
    # TODO
    And I wait 10 seconds
    # On est redirigé vers la page d'accueil
    # Then the following message is shown and closed: "Vous n'êtes pas connecté."
    # Then I should see "Vous n'êtes pas connecté"
    # And I should see "Connexion"
