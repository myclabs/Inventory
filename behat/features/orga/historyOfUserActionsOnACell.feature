@dbFull
Feature: History of user actions on a cell feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Input history feature, no history scenario
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Historique"
    Then I should see "Aucun historique à afficher."
  # TODO : texte à modifier.

  @javascript
  Scenario: Input history scenario, general data form, creation of an input
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
  # Accès à la saisie"
    When I open tab "Saisies"
    And I open collapse "Zone | Marque"
    Then I should see the "aFGranularity1Input2" datagrid
    When I click "Cliquer pour accéder" in the row 2 of the "aFGranularity1Input2" datagrid
  # TODO : vérifier que pas de bouton pour la saisie initiale.
  # Création de la saisie initiale
    When I fill in "chiffre_affaire" with "10"
    And I click "Enregistrer"
    And I reload the page
    And I wait for the page to finish loading
  # Ouverture du popup d'historique
    And I click element "#chiffre_affaireHistory .btn"
    Then I should see a "code:contains('10 k€ ± 0 %')" element
  # Fermeture du popup d'historique
    When I click element "#chiffre_affaireHistory .btn"
    And I click "Quitter"
    And I open tab "Historique"
    Then I should see "La saisie Europe | Marque B a été enregistrée pour la première fois par Administrateur Système."
  # Descente au niveau Zone | Marque et vérification que le contenu d'historique est encore présent
    When I click element ".icon-plus"
    And I select "Europe" from "zone"
    And I select "Marque B" from "marque"
    And I click element "#goTo2"
    Then I should see "Europe | Marque B Workspace avec données"
    When I open tab "Historique"
    Then I should see "La saisie Europe | Marque B a été enregistrée pour la première fois par Administrateur Système."
  # Descente au niveau site et vérification que le contenu d'historique n'est plus présent
    When I click element ".icon-plus"
    And I select "Grenoble" from "site"
    And I click element "#goTo3"
    Then I should see "Grenoble Workspace avec données"
    When I open tab "Historique"
    Then I should see "Aucun historique à afficher."





