@dbFull
Feature: classifContext

  Background:
    Given I am logged in

  @javascript
  Scenario: classifContext1
    Given I am on "classif/context/manage"
    Then I should see the "editContexts" datagrid
  # Ajout d'un contexte, identifiant vide
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un contexte"
    When I click "Valider"
    Then the field "editContexts_ref_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout d'un contexte, identifiant avec des caractères non autorisés
    When I fill in "editContexts_ref_addForm" with "bépo"
    And I click "Valider"
    Then the field "editContexts_ref_addForm" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Ajout d'un contexte, saisie correcte
    When I fill in "editContexts_label_addForm" with "À supprimer"
    And I fill in "editContexts_ref_addForm" with "a_supprimer"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
    And the row 1 of the "editContexts" datagrid should contain:
      | label       | ref         |
      | À supprimer | a_supprimer |
  # Ajout d'un contexte, identifiant déjà utilisé
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un contexte"
    And I fill in "editContexts_ref_addForm" with "a_supprimer"
    And I click "Valider"
    Then the field "editContexts_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Suppression d'un contexte (non utilisé par un indicateur contextualisé)
    When I click element "#editContexts_addPanel a.btn:contains('Annuler')"
    When I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."