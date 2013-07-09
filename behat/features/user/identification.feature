@dbFull
Feature: Identification feature
  The login form authenticates users.

  Scenario: Login redirection
    Given I am on the homepage
    And I wait for the page to finish loading
    Then I should be on "user/action/login"
    And I should see "Connexion"
    And I should see a "#loginButton" element

  @javascript
  Scenario: Logging in with wrong password
    Given I am on the homepage
    And I wait for the page to finish loading
    When I fill in "email" with "admin"
    And I fill in "password" with "blahblah"
    And I click "connection"
    Then I should see "Attention ! Le mot de passe indiqué est invalide."

  @javascript
  Scenario: Logging in correctly
    Given I am on the homepage
    And I wait for the page to finish loading
    When I fill in "email" with "admin"
    And I fill in "password" with "myc-53n53"
    And I click "connection"
    Then I should see "Organisations"

  @javascript
  Scenario: Logging out
    Given I am logged in
    And I am on the homepage
    And I wait for the page to finish loading
    When I click "currentUserButton"
    And I click "logoutButton"
    # On est redirigé vers la page d'accueil
    Then the following message is shown and closed: "Vous n'êtes pas connecté."
    And I should see "Vous n'êtes pas connecté"
    And I should see "Connexion"
