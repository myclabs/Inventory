@dbFull
Feature: Family element tab edit feature

  Background:
    Given I am logged in

  @javascript @readOnly
  Scenario: Family edit, display elements
    Given I am on "parameter/family/edit/id/5"
    And I wait for the page to finish loading
  # Séparateur décimal en français
  # Arrondi à trois chiffres significatifs
  # Séparateur de milliers en français
  # En-têtes de dimensions commencent par une majuscule
    And I should see "0,123 ± 15 %"
    And I should see "12 300 ± 15 %"
    And I should see "Combustible"
    And I should see "Processus"

  @javascript
  Scenario: Family edit, creation of an element scenario, correct input
    Given I am on "parameter/family/edit/id/5"
    And I wait for the page to finish loading
  # Ajout d'un élément
    When I click element "#elements-gaz_naturel-combustion a"
    Then I should see the popup "Paramètre"
    When I fill in "Valeur" with "1234,56789"
    And I fill in "Incertitude" with "12,34"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Modification effectuée."
    And I should see "1 230 ± 12 %" in the "#elements-gaz_naturel-combustion" element
  # Ajout d'un élément puis annulation sans enregistrement
  # TODO : actuellement l'élément est tout de même créé.

  @javascript @readOnly
  Scenario: Family edit, creation of an element scenario, incorrect input
    Given I am on "parameter/family/edit/id/5"
    And I wait for the page to finish loading
  # Ajout d'un élément
    When I click element "#elements-gaz_naturel-combustion a"
    Then I should see the popup "Paramètre"
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
    Given I am on "parameter/family/edit/id/5"
    And I wait for the page to finish loading
  # Édition d'un élément
    When I click element "#elements-charbon-combustion a"
    Then I should see the popup "Paramètre"
  # Valeur et incertitude valides
    When I fill in "Valeur" with "1234,56789"
    And I fill in "Incertitude" with "12,89"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Modification effectuée."
    And I should see "1 230 ± 12 %" in the "#elements-charbon-combustion" element

  @javascript @readOnly
  Scenario: Family edit, edition of an element scenario, incorrect input
    Given I am on "parameter/family/edit/id/5"
    And I wait for the page to finish loading
  # Édition d'un élément
    When I click element "#elements-charbon-combustion a"
    Then I should see the popup "Paramètre"
  # Valeur et incertitude vides
    When I fill in "Valeur" with ""
    And I fill in "Incertitude" with ""
    And I click "Enregistrer"
    Then the field "Valeur" should have error: "Merci de renseigner ce champ."
  # Valeur et incertitude invalides
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
