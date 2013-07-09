@dbFull
Feature: Keywords datagrid

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of a keyword
    Given I am on "keyword/keyword/manage"
    Then I should see the "keywords" datagrid
  # Ajout d'un mot clé, identifiant vide
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un mot clé"
    When I click "Valider"
    Then the field "keywords_ref_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout d'un mot clé, identifiant avec des caractères non autorisés
    When I fill in "keywords_ref_addForm" with "bépo"
    And I click "Valider"
    Then the field "keywords_ref_addForm" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Ajout d'un mot clé, identifiant égal à "this"
    When I fill in "keywords_ref_addForm" with "this"
    And I click "Valider"
    Then the field "keywords_ref_addForm" should have error: "Merci de ne pas choisir « this » comme identifiant de mot clé, ce terme est réservé pour l'écriture des requêtes sémantiques."
  # Ajout d'un mot clé, identifiant correct
    When I fill in "keywords_label_addForm" with "AAA"
    And I fill in "keywords_ref_addForm" with "aaa"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Ajout d'un mot clé, identifiant déjà utilisé
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un mot clé"
    When I fill in "keywords_ref_addForm" with "aaa"
    And I click "Valider"
    Then the field "keywords_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Bouton "Annuler"
    When I click "Annuler"
    Then I should see the "keywords" datagrid

  @javascript
  Scenario: Access to keywords graph
    Given I am on "keyword/keyword/manage"
    Then I should see the "keywords" datagrid
    When I click "Graphe" in the row 1 of the "keywords" datagrid
    Then I should see "Graphe des mots clés"

  @javascript
  Scenario: Filters on Keyword datagrid
    Given I am on "keyword/keyword/manage"
    Then I should see the "keywords" datagrid
  # Test filtre libellé
    When I open collapse "Filtres"
    And I fill in "keywords_label_filterForm" with "x"
    And I click "Filtrer"
    Then the "keywords" datagrid should contain 0 row
    When I fill in "keywords_label_filterForm" with "charbon"
    And I click "Filtrer"
    Then the "keywords" datagrid should contain 1 row
  # Test bouton "Réinitialiser"
    When I click "Réinitialiser"
    Then I should see "processus"
  # Test filtre identifiant
    When I open collapse "Filtres"
    And I fill in "keywords_ref_filterForm" with "x"
    And I click "Filtrer"
    Then the "keywords" datagrid should contain 0 row
    When I fill in "keywords_ref_filterForm" with "charbon"
    And I click "Filtrer"
    Then the "keywords" datagrid should contain 1 row

  @javascript
  Scenario: Edition of a keyword
    Given I am on "keyword/keyword/manage"
    Then I should see the "keywords" datagrid
  # Modification de l'identifiant et du libellé d'un mot clé, saisie correcte
    When I set "charbon modifié" for column "label" of row 2 of the "keywords" datagrid with a confirmation message
    And I set "charbon_modifie" for column "ref" of row 2 of the "keywords" datagrid with a confirmation message
    Then the row 2 of the "keywords" datagrid should contain:
      | label            | ref            |
      | charbon modifié | charbon_modifie |
  # Modification de l'identifiant, identifiant vide
    When I set "" for column "ref" of row 2 of the "keywords" datagrid
    Then the following message is shown and closed: "Merci de renseigner ce champ."
  # Modification de l'identifiant, identifiant avec des caractères non autorisés
    When I set "bépo" for column "ref" of row 2 of the "keywords" datagrid
    Then the following message is shown and closed: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Modification de l'identifiant, identifiant déjà utilisé
    When I set "combustible" for column "ref" of row 2 of the "keywords" datagrid
    Then the following message is shown and closed: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Modification de l'identifiant, identifiant égal à "this"
    When I set "this" for column "ref" of row 2 of the "keywords" datagrid
    Then the following message is shown and closed: "Merci de ne pas choisir « this » comme identifiant de mot clé, ce terme est réservé pour l'écriture des requêtes sémantiques."

  @javascript
  Scenario: Suppression of a keyword
    Given I am on "keyword/keyword/manage"
    Then I should see the "keywords" datagrid
    And the row 1 of the "keywords" datagrid should contain:
      | nbRelations |
      | 0           |
    And the row 2 of the "keywords" datagrid should contain:
      | nbRelations |
      | 1           |
  # Suppression d'un mot clé avec des relations
    When I click "Supprimer" in the row 2 of the "keywords" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
  # Suppression d'un mot clé sans relations
    When I click "Supprimer" in the row 1 of the "keywords" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."

