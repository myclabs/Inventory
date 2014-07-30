@dbFull
Feature: History of user actions on a cell feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Input history feature, no history scenario
    Given I am on "orga/cell/view/cell/1"
    And I wait for the page to finish loading
    Then I should see "L'historique est vide."

  @javascript
  Scenario: Input history scenario, general data form, creation of an input
    Given I am on "orga/cell/input/cell/4/fromCell/1/"
    And I wait for the page to finish loading
  # TODO : vérifier que pas de bouton pour la saisie initiale.
  # Création de la saisie initiale
    When I fill in "chiffre_affaire" with "10"
    And I click "Enregistrer"
    And I reload the page
    And I wait for the page to finish loading
  # Ouverture du popup d'historique
    And I click element "#chiffre_affaireHistory"
    And I wait 2 seconds
    Then I should see a "code:contains('10 k€ ± 0 %')" element
  # Fermeture du popup d'historique
    When I click element "#chiffre_affaireHistory"
    And I click "Quitter"
    Then I should see "La saisie Europe | Marque B a été enregistrée pour la première fois par Administrateur Système."





