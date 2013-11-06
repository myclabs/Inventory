@dbFull
Feature: Cell comment tab feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Cell comment tab scenario
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Commentaires"
  # Au départ, aucun commentaire
    Then I should see "Aucun commentaire."
  # Ajout d'un commentaire dans une saisie
    When I open tab "Saisies"
    And I open collapse "Niveau organisationnel global"
    And I click "Cliquer pour accéder" in the row 1 of the "aFGranularity1Input1" datagrid
    And I open tab "Commentaires"
    And I click "Ajouter un commentaire"
    And I fill in "addContent" with "h1. Commentaire cellule _globale_."
    And I click element "#Ajouter"
    Then I should see "Commentaire cellule globale."
  # Retour à la page de la cellule globale
    When I open tab "Saisie"
    And I click "Quitter"
  # Ajout second commentaire
    And I open collapse "Zone | Marque"
    And I click "Cliquer pour accéder" in the row 1 of the "aFGranularity1Input2" datagrid
    And I open tab "Commentaires"
    And I click "Ajouter un commentaire"
    And I fill in "addContent" with "h2. Commentaire cellule _Europe Marque A_."
    And I click element "#Ajouter"
    Then I should see "Commentaire cellule _Europe Marque A_."
    # TODO : améliorer affichage commentaires
  # Retour à la page de la cellule globale
    When I open tab "Saisie"
    And I click "Quitter"
  # Accès à l'onglet "Commentaires"
    And I open tab "Commentaires"
    Then I should see "Administrateur Système"
    And I should see "Europe | Marque A"
    And I should see "Commentaire cellule Europe Marque A."
    And I should see " Vue globale"
    And I should see "Commentaire cellule globale."

