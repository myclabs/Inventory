@dbFull
Feature: Cell comment tab feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Cell comment tab scenario
    Given I am on "orga/cell/input/cell/1/fromCell/1/"
    And I open tab "Commentaires"
  # Au départ, aucun commentaire
    Then I should see "Aucun commentaire."
  # Ajout d'un commentaire dans une saisie
    And I fill in "newComment" with "h1. Commentaire cellule _globale_."
    And I click "Ajouter un commentaire"
    And I wait for 2 seconds
    Then I should see "Commentaire cellule globale."
  # Retour à la page de la cellule globale
    When I open tab "Saisie"
    And I click "Quitter"
  # Ajout second commentaire
    And I go input the "/1-zone:europe/&/2-marque:marque_a/" cell
    And I switch to the new tab
    And I open tab "Commentaires"
    And I fill in "newComment" with "h2. Commentaire cellule _Europe Marque A_."
    And I click "Ajouter un commentaire"
    And I wait for 2 seconds
    Then I should see "Commentaire cellule Europe Marque A."
  # Retour à la page de la cellule globale
    When I open tab "Saisie"
    And I click "Quitter"
  # Accès à l'onglet "Commentaires"
    Then I should see "par Administrateur Système à propos de la saisie Vue globale : « Commentaire cellule globale. »."
    And I should see "par Administrateur Système à propos de la saisie Europe | Marque A : « Commentaire cellule Europe Marque A. »."

