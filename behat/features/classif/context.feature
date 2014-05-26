@dbFull
Feature: Classification context feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of a classification context (not a context indicator)
    Given I am on "classification/context/list?library=1"
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
    When I fill in "editContexts_label_addForm" with "Test"
    And I fill in "editContexts_ref_addForm" with "test"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
    And the row 4 of the "editContexts" datagrid should contain:
      | label | ref  |
      | Test  | test |
  # Ajout d'un contexte, identifiant déjà utilisé
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un contexte"
    And I fill in "editContexts_ref_addForm" with "test"
    And I click "Valider"
    Then the field "editContexts_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Test bouton "Annuler"
    When I click "Annuler"
    Then I should not see "Ajout d'un contexte"

  @javascript
  Scenario: Edition of a classification context
    # TODO : position
    Given I am on "classification/context/list?library=1"
    Then I should see the "editContexts" datagrid
  # Modification des différents attributs, saisie correcte
    When I set "Général modifié" for column "label" of row 1 of the "editContexts" datagrid with a confirmation message
    And I set "general_modifie" for column "ref" of row 1 of the "editContexts" datagrid with a confirmation message
    Then the row 1 of the "editContexts" datagrid should contain:
      | label       | ref         |
      | Général modifié | general_modifie |
  # Modification de l'identifiant, identifiant vide
    When I set "" for column "ref" of row 1 of the "editContexts" datagrid
    Then the following message is shown and closed: "Merci de renseigner ce champ."
  # Modification de l'identifiant, identifiant avec des caractères non autorisés
    When I set "bépo" for column "ref" of row 1 of the "editContexts" datagrid
    Then the following message is shown and closed: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Modification de l'identifiant, identifiant déjà utilisé
    When I set "deplacement" for column "ref" of row 1 of the "editContexts" datagrid
    Then the following message is shown and closed: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."

  @javascript
  Scenario:  Deletion of a classification context
    Given I am on "classification/context/list?library=1"
    Then I should see the "editContexts" datagrid
    And the row 3 of the "editContexts" datagrid should contain:
      | label                         | ref                           |
      | Sans indicateur contextualisé | sans_indicateur_contextualise |
  # Contexte non utilisé par un indicateur contextualisé
    When I click "Supprimer" in the row 3 of the "editContexts" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
  # context utilisé par un indicateur contextualisé
    When I click "Supprimer" in the row 1 of the "editContexts" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Ce contexte ne peut pas être supprimé, car il est utilisé pour (au moins) un indicateur contextualisé."
