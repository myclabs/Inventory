@dbEmpty
Feature: keywordKeyword

  Background:
    Given I am logged in

  @javascript
  Scenario: keywordKeyword1
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
    When I fill in "keywords_label_addForm" with "À supprimer"
    And I fill in "keywords_ref_addForm" with "a_supprimer"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Ajout d'un mot clé, identifiant déjà utilisé
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un mot clé"
    When I fill in "keywords_ref_addForm" with "a_supprimer"
    And I click "Valider"
    Then the field "keywords_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Affichage du datagrid
    When I click element "#keywords_addPanel a.btn:contains('Annuler')"
    Then the "keywords" datagrid should contain 1 row
    And the row 1 of the "keywords" datagrid should contain:
      | label       | ref         | nbRelations |
      | À supprimer | a_supprimer | 0           |
  # Suppression
    When I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."

  @javascript
  Scenario: keywordKeyword2
    Given I am on "keyword/keyword/manage"
    Then I should see the "keywords" datagrid
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un mot clé"
  # Ajout d'un mot clé
    When I fill in "keywords_label_addForm" with "Test"
    And I fill in "keywords_ref_addForm" with "test"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Lien "Graphe"
    When I click "Graphe" in the row 1 of the "keywords" datagrid
    Then I should see "Graphe des mots clés"
    And I should see "Test (test)"
  # Retour aux mots clés racines
    When I click "Aller aux mots clés racines"
    Then I should see "Mots clés racines"
  # Clic sur un mot clé
    When I click "Test (test)"
    Then I should see "Sujets"
  # Utilisation du champ "Aller à", saisie incorrecte
    When I fill in "keywordGoTo" with "auie"
    And I click "OK"
    Then the following message is shown and closed: "Le contenu du champ « Aller à » n'a mené à aucun mot clé."
    And I should see "Mots clés racines"
# Utilisation du champ "Aller à", saisie correcte
    When I fill in "keywordGoTo" with "Test"
    And I click "OK"
    Then I should see "Sujets"

  @javascript
  Scenario: keywordKeyword3
    Given I am on "keyword/keyword/manage"
    Then I should see the "keywords" datagrid
  # Ajout d'un mot clé
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un mot clé"
    When I fill in "keywords_label_addForm" with "AAA"
    And I fill in "keywords_ref_addForm" with "aaa"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Test filtre libellé
    When I open collapse "Filtres"
    And I fill in "keywords_label_filterForm" with "x"
    And I click "Filtrer"
    Then the "keywords" datagrid should contain 0 row
    When I fill in "keywords_label_filterForm" with "A"
    And I click "Filtrer"
    Then the "keywords" datagrid should contain 1 row
  # Test filtre identifiant
    When I fill in "keywords_ref_filterForm" with "x"
    And I click "Filtrer"
    Then the "keywords" datagrid should contain 0 row
    When I fill in "keywords_ref_filterForm" with "a"
    And I click "Filtrer"
    Then the "keywords" datagrid should contain 1 row

  @javascript
  Scenario: keywordKeyword4
    Given I am on "keyword/keyword/manage"
    Then I should see the "keywords" datagrid
  # Ajout d'un mot clé
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un mot clé"
    When I fill in "keywords_label_addForm" with "AAA"
    And I fill in "keywords_ref_addForm" with "aaa"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
    And the row 1 of the "keywords" datagrid should contain:
      | label       | ref         | nbRelations |
      | AAA | aaa | 0           |
  # Modification du libellé d'un mot clé
    Then I open the cellEditor for column "label" in the row 1 of the "keywords" datagrid
    Then I fill "aaa_modifie" in the cellEditor
    Then I save and close the cellEditor
    Then the following message is shown and closed: "Modification effectuée."
    And the column "name" of the row 2 of the "users" datagrid should contain "aaa_modifie"