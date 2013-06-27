@dbEmpty
Feature: Keyword

  Background:
    Given I am logged in

  @javascript
  Scenario: KeywordPredicate
    Given I am on "keyword/predicate/manage"
    # Ajout d'une paire prédicat/prédicat inverse, messages d'erreur
    When I follow "Ajouter"
    Then I should see the popup "Ajout d'une paire prédicat / prédicat inverse"
    When I fill "predicates_ref_addForm" with "bépo"
    And I press "Valider"
    And I wait for the page to finish loading
    Then the field "predicates_ref_addForm" should have error: "Merci d'utiliser seulement les caractères : "a..z", "0..9", et "_"."
    Then the field "predicates_reverseRef_addForm" should have error: "Merci de renseigner ce champ."