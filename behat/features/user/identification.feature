@dbFull
Feature: Identification feature
  The login form authenticates users.

  @readOnly @readOnly
  Scenario: Login redirection
    Given I am on the homepage
    And I wait for the page to finish loading
    Then I should be on "user/action/login"
    And I should see "Vous n'êtes pas connecté"
    And I should see "Connexion"

  @javascript @readOnly
  Scenario: Logging in with wrong password
    Given I am on the homepage
    And I wait for the page to finish loading
    When I fill in "email" with "admin@myc-sense.com"
    And I fill in "password" with "blahblah"
    And I click "connection"
    Then I should see "Attention ! Le mot de passe indiqué est invalide."

  @javascript @readOnly
  Scenario: Logging in correctly
    Given I am on the homepage
    And I wait for the page to finish loading
    When I fill in "email" with "admin@myc-sense.com"
    And I fill in "password" with "myc-53n53"
    And I click "connection"
    Then I should see "Workspaces"

  @javascript @readOnly
  Scenario: Logging out
    Given I am logged in
    And I am on the homepage
    And I wait for the page to finish loading
    When I click "currentUserButton"
    And I click "Déconnexion"
    # On est redirigé vers la page d'accueil
    Then the following message is shown and closed: "Vous n'êtes pas connecté."
    And I should see "Vous n'êtes pas connecté"
    And I should see "Connexion"

  @javascript @readOnly
  Scenario: Forgottent password
  # TODO : à tester, pour l'instant l'accès à la page du captcha pose problème (installation des fontes) donc non testé.
    Given I am on the homepage
    And I wait for the page to finish loading
  #  And I click "Mot de passe oublié ?"

  @javascript @readOnly
  Scenario: Trying to reach an url without being connected
    Given I am on the homepage
    And I go to "orga/granularity/report/idCell/1/"
    Then the following message is shown and closed: "Vous n'êtes pas connecté."
