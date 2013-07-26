@dbFull
Feature: Family edit feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Family edit general data scenario, correct input
    Given I am on "techno/family/edit/id/4"
    And I wait for the page to finish loading
    Then I should see "Famille test de processus"
    When I open tab "Général"
  # Vérification du contenu des différents champs du formulaire "Général"
    And the "Libellé" field should contain "Famille test de processus"
    And the "Identifiant" field should contain "famille_test_processus"
    And the "Unité" field should contain "t"
  # Modifications
    When I fill in "Libellé" with "Famille test de processus modifiée"
    And I fill in "Identifiant" with "famille_test_processus_modifiee"
    And I fill in "Unité" with "kg"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Modification effectuée."
    And the "Libellé" field should contain "Famille test de processus"
    And the "Identifiant" field should contain "famille_test_processus_modifiee"
    And the "Unité" field should contain "kg"

  @javascript
  Scenario: Family edit general data scenario, incorrect input
    Given I am on "techno/family/edit/id/4"
    And I wait for the page to finish loading
    When I open tab "Général"
  # Libellé et identifiant et unité vides
    And I fill in "Libellé" with ""
    And I fill in "Identifiant" with ""
    And I fill in "Unité" with ""
    And I click "Enregistrer"
    Then the field "Libellé" should have  error: "Merci de renseigner ce champ."
    And the field "Identifiant" should have  error: "Merci de renseigner ce champ."
    And the field "Unité" should have  error: "Merci de renseigner ce champ."
  # Libellé non vide, identifiant caractères non autorisés, unité invalide
    When I fill in "Libellé" with "Test"
    And I fill in "Identifiant" with "bépo"
    And I fill in "Unité" with "auie"
    And I click "Enregistrer"
    Then the field "Identifiant" should have  error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
    And the field "Unité" should have  error: "Merci de saisir un identifiant d'unité valide."
  # Libellé non vide, identifiant déjà utilisé, unité invalide
    When I fill in "Identifiant" with "combustion_combustible_unite_masse"
    And I fill in "Unité" with "m2"
    And I click "Enregistrer"
    Then the field "Identifiant" should have  error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
    And the field "Unité" should have  error: "Merci de saisir un identifiant d'unité valide."

  @javascript
  Scenario: Family edit, creation of an element scenario, correct input
    Given I am on "techno/family/edit/id/4"
    And I wait for the page to finish loading
  # Ajout d'un élément
    When I click element "#elements-gaz_naturel-combustion .btn:contains('Ajouter')"
    Then I should see the popup "Élément"
    When I fill in "Valeur" with "1234,56789"
    And I fill in "Incertitude" with "12,34"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Modification effectuée."
    And I should see a "#elements_charbon_amont_combustion a:contains('1234,56789 ± 12 %')" element
  # Ajout d'un élément puis annulation sans enregistrement
    # TODO : actuellement l'élément est tout de même créé.

  @javascript
  Scenario: Family edit, creation of an element scenario, incorrect input
    Given I am on "techno/family/edit/id/4"
    And I wait for the page to finish loading
  # Ajout d'un élément
    When I click element "#elements-gaz_naturel-combustion .btn:contains('Ajouter')"
    Then I should see the popup "Élément"
  # Ajout, valeur vide, incertitude vide
    When I click "Enregistrer"
    Then the field "Valeur" should have error: "Merci de renseigner ce champ."
  # Ajout, valeur vide, incertitude vide
    When I fill in "Valeur" with "auie"
    And I fill in "Incertitude" with "auie"
    And I click "Enregistrer"
    Then the field "Valeur" should have error: "La quantité saisie n'a pas pu être interprétée, merci de corriger (en français merci d'utiliser la virgule comme séparateur décimal)."
    And the field "Incertitude" should have error: "La quantité saisie n'a pas pu être interprétée, merci de corriger (en français merci d'utiliser la virgule comme séparateur décimal)."
  # Valeur et incertitude séparateur décimal invalide
    When I fill in "Valeur" with "1234.56789"
    And I fill in "Incertitude" with "12.34"
    And I click "Enregistrer"
    Then the field "Valeur" should have error: "La quantité saisie n'a pas pu être interprétée, merci de corriger (en français merci d'utiliser la virgule comme séparateur décimal)."
    And the field "Incertitude" should have error: "La quantité saisie n'a pas pu être interprétée, merci de corriger (en français merci d'utiliser la virgule comme séparateur décimal)."

  @javascript
  Scenario: Family edit, edition of an element scenario, correct input
    Given I am on "techno/family/edit/id/4"
    And I wait for the page to finish loading
  # Vérification qu'on tombe bien sur l'onglet "Éléments"
  # Séparateur décimal en français
  # Arrondi à trois chiffres significatifs
  # Séparateur de milliers en français
  # En-têtes de dimensions commencent par une majuscule
    And I should see "0,123 ± 16 %"
    And I should see "12 300 ± 16 %"
    And I should see "Combustible"
    And I should see "Processus"
  # Édition d'un élément
    When I click element "#elements-charbon-combustion a"
    Then I should see the popup "Élément"
  # Valeur et incertitude valides
    When I fill in "Valeur" with "1234,56789"
    And I fill in "Incertitude" with "12,89"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Modification effectuée."
    And I should see a "#elements-charbon-amont_combustion a:contains('1 230 ± 12 %')" element

  @javascript
  Scenario: Family edit, edition of an element scenario, incorrect input
    Given I am on "techno/family/edit/id/4"
    And I wait for the page to finish loading
  # Édition d'un élément
    When I click element "#elements-charbon-combustion a"
    Then I should see the popup "Élément"
  # Valeur et incertitude vides
    When I fill in "Valeur" with ""
    And I fill in "Incertitude" with ""
    And I click "Enregistrer"
    Then the field "Valeur" should have  error: "Merci de renseigner ce champ."
    And the field "Incertitude" should have  error: "Merci de renseigner ce champ."
  # Valeur et incertitude invalides
    When I fill in "Valeur" with "auie"
    And I fill in "Incertitude" with "auie"
    And I click "Enregistrer"
    Then the field "Valeur" should have  error: "La quantité saisie n'a pas pu être interprétée, merci de corriger (en français merci d'utiliser la virgule comme séparateur décimal)."
    And the field "Incertitude" should have  error: "La quantité saisie n'a pas pu être interprétée, merci de corriger (en français merci d'utiliser la virgule comme séparateur décimal)."
  # Valeur et incertitude séparateur décimal invalide
    When I fill in "Valeur" with "1234.56789"
    And I fill in "Incertitude" with "12.34"
    And I click "Enregistrer"
    Then the field "Valeur" should have  error: "La quantité saisie n'a pas pu être interprétée, merci de corriger (en français merci d'utiliser la virgule comme séparateur décimal)."
    And the field "Incertitude" should have  error: "La quantité saisie n'a pas pu être interprétée, merci de corriger (en français merci d'utiliser la virgule comme séparateur décimal)."

  @javascript
  Scenario: Family edit documentation scenario
  # À partir d'une documentation vide
    Given I am on "techno/family/edit/id/3"
    And I wait for the page to finish loading
    Then I should see "Famille test vide"
    When I open tab "Documentation"
    And I fill in "documentation" with "h1. Test documentation"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Modification effectuée."
  # Vérification que la documentation est bien affichée en consultation
    Given I am on "techno/family/details/id/3"
    And I wait for the page to finish loading
    And I open tab "Documentation"
    Then I should see a "#container_documentation h1:contains('Test documentation')" element
  # Vérification que la documentation est bien réaffichée en édition
    Given I am on "techno/family/edit/id/3"
    And I open tab "Documentation"
    Then the "documentation" field should contain "h1. Test documentation"