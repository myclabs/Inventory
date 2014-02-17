@dbFull
Feature: AF structure feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Change the position of an AF group
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Structure"
    And I wait 4 seconds
  # Déplacement d'un groupe, à la fin
    When I click "Groupe vide"
    Then I should see the popup "Déplacement dans la structure du formulaire"
    When I check "Dernier"
  # L'attente qui suit semble, curieusement, nécessaire
    And I click "Confirmer"
    And I wait 4 seconds
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement d'un groupe, au début
    And I click "Groupe contenant un sous-groupe"
    Then I should see the popup "Déplacement dans la structure du formulaire"
    When I check "Premier"
    And I click "Confirmer"
    And I wait 4 seconds
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement d'un groupe, après un autre composant ou group
    And I click "Groupe contenant un champ"
    Then I should see the popup "Déplacement dans la structure du formulaire"
    When I check "Après"
    And I select "Champ sélection simple" from "afTree_selectAfter"
    And I click "Confirmer"
    And I wait 4 seconds
    Then the following message is shown and closed: "Modification effectuée."

  @javascript
  Scenario: Change the parent of an AF group
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Structure"
    And I wait for 4 seconds
  # Modification du parent d'un groupe (depuis la racine)
    When I click "Groupe vide"
    And I wait for 2 seconds
    Then I should see the popup "Déplacement dans la structure du formulaire"
    When I select "Groupe contenant un sous-groupe" from "afTree_changeParent"
    And I click "Confirmer"
    And I wait for 3 seconds
    Then the following message is shown and closed: "Modification effectuée."
  # Modification du parent d'un groupe (vers la racine)
    When I click "Groupe vide"
    And I wait for 2 seconds
    Then I should see the popup "Déplacement dans la structure du formulaire"
    When I select "Racine" from "afTree_changeParent"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."

  @javascript
  Scenario: Change the position of an AF component (not a group)
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Structure"
    And I wait 4 seconds
  # Déplacement d'un composant, à la fin
    And I click "Sous-formulaire non répété"
    Then I should see the popup "Déplacement dans la structure du formulaire"
    When I check "Dernier"
    And I click "Confirmer"
    And I wait 4 seconds
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement d'un composant, au début
    And I click "Champ texte long"
    Then I should see the popup "Déplacement dans la structure du formulaire"
    When I check "Premier"
    And I click "Confirmer"
    And I wait 4 seconds
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement d'un composant, après un autre composant
    And I click "Champ texte court"
    Then I should see the popup "Déplacement dans la structure du formulaire"
    When I check "Après"
    And I select "Sous-formulaire répété" from "afTree_selectAfter"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."


  @javascript
  Scenario: Change the parent of an AF component (not a group)
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Structure"
    And I wait 4 seconds
  # Modification du parent d'un composant (depuis la racine)
    And I click "Champ sélection multiple"
    Then I should see the popup "Déplacement dans la structure du formulaire"
    When I wait 2 seconds
    And I select "Groupe contenant un champ" from "afTree_changeParent"
    And I click "Confirmer"
    And I wait 4 seconds
    Then the following message is shown and closed: "Modification effectuée."
  # Modification du parent d'un composant (vers la racine)
    When I wait 10 seconds
    And I click "Champ numérique cible activation"
    Then I should see the popup "Déplacement dans la structure du formulaire"
    When I wait 2 seconds
    And I select "Racine" from "afTree_changeParent"
    And I click "Confirmer"
    And I wait 4 seconds
    Then the following message is shown and closed: "Modification effectuée."
