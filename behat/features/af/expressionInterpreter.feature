@dbFull
Feature: Expression interpreter feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Expression interpreter scenario
    Given I am on "tec/expression/test"
    And I wait for the page to finish loading
  # Ouverture du popup d'aide
    When I open collapse "Aide"
    Then I should see "Caractères autorisés pour les opérandes : \"a..z\", \"0..9\", et \"_\"."
  # Inteprétations expressions arithmétiques
  # Expression vide
    When I click "Interpréter"
    Then I should see "L'expression n'a pas pu être interprétée."
    And I should see "Il manque un opérateur dans l'expression « »."
  # Expression correcte
    When I fill in "input" with "a+b"
    And I click "Interpréter"
    Then I should see "L'expression est syntaxiquement correcte. "
    And I should see "a + b"
