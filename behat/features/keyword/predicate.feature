@dbFull
Feature: keywordPredicate

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of a predicate
    Given I am on "keyword/predicate/manage"
    Then I should see the "predicates" datagrid
  # Ajout d'une paire prédicat/prédicat inverse, messages d'erreur
    When I click "Ajouter"
    Then I should see the popup "Ajout d'une paire prédicat / prédicat inverse"
  # Caractères autorisés pour l'identifiant du prédicat direct
  # Identifiant requis pour le prédicat inverse
    When I fill in "predicates_ref_addForm" with "bépo"
    And I click "Valider"
    And I wait for the page to finish loading
    Then the field "predicates_ref_addForm" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
    Then the field "predicates_reverseRef_addForm" should have error: "Merci de renseigner ce champ."
  # Identifiant requis pour le prédicat direct
  # Caractères non autorisés pour l'identifiant du prédicat inverse
    When I fill in "predicates_ref_addForm" with ""
    And I fill in "predicates_reverseRef_addForm" with "bépo"
    And I click "Valider"
    Then the field "predicates_ref_addForm" should have error: "Merci de renseigner ce champ."
    Then the field "predicates_reverseRef_addForm" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Identifiants des prédicats direct et inverse différents
    When I fill in "predicates_ref_addForm" with "auie"
    And I fill in "predicates_reverseRef_addForm" with "auie"
    And I click "Valider"
    Then the field "predicates_ref_addForm" should have error: "Merci de saisir des identifiants différents pour les prédicats direct et inverse."
    Then the field "predicates_reverseRef_addForm" should have error: "Merci de saisir des identifiants différents pour les prédicats direct et inverse."
    When I click "Annuler"
  # Ajout correct
    And I click "Ajouter"
    Then I should see the popup "Ajout d'une paire prédicat / prédicat inverse"
    When I fill in "predicates_label_addForm" with "prédicat direct"
    And I fill in "predicates_ref_addForm" with "predicat_direct"
    And I fill in "predicates_reverseLabel_addForm" with "prédicat inverse"
    And I fill in "predicates_reverseRef_addForm" with "predicat_inverse"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Ajout d'un prédicat dont l'identifiant ou l'identifiant inverse existe déjà
    When I click "Ajouter"
    Then I should see the popup "Ajout d'une paire prédicat / prédicat inverse"
    When I fill in "predicates_ref_addForm" with "predicat_direct"
    And I click "Valider"
    Then the field "predicates_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
    When I click element "#predicates_addPanel a.btn:contains('Annuler')"
    And I click "Ajouter"
    Then I should see the popup "Ajout d'une paire prédicat / prédicat inverse"
    When I fill in "predicates_ref_addForm" with ""
    And I fill in "predicates_reverseRef_addForm" with "predicat_direct"
    And I click "Valider"
    Then the field "predicates_reverseRef_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."

  @javascript
  Scenario: Suppression of a predicate
    Given I am on "keyword/predicate/manage"
    Then I should see the "predicates" datagrid
    And the row 1 of the "predicates" datagrid should contain:
      | label    | ref      | reverseLabel | reverseRef |
      | contient | contient | fait partie de | fait_partie_de |
  # Suppression d'un prédicat non utilisé dans une relation
    When I click "Supprimer" in the row 1 of the "predicates" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
  # Suppression d'un prédicat non utilisé dans une relatioWhen I click "Supprimer" in the row 1 of the "predicates" datagrid
    When I click "Supprimer" in the row 1 of the "predicates" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Ce prédicat ne peut pas être supprimé, car il est utilisé dans (au moins) une relation entre mots clés."
    And the row 1 of the "predicates" datagrid should contain:
      | label    | ref      | reverseLabel | reverseRef |
      | est plus général que | est_plus_general_que | est plus spécifique que | est_plus_specifique_que |