@dbFull
Feature: Expression interpreter feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Arithmetic expression interpreter scenario
    # TODO : tester bouton "Réinitialiser"
    Given I am on "tec/expression/test"
    And I wait for the page to finish loading
  # Ouverture du popup d'aide
    When I open collapse "Aide"
    Then I should see "Caractères autorisés pour les opérandes : \"a..z\", \"0..9\", et \"_\"."
  # Expression vide
    When I click "Interpréter"
    Then I should see "L'expression n'a pas pu être interprétée."
    And I should see "Il manque un opérateur dans l'expression « »."
  # Expression correcte "simple"
    When I fill in "input" with "a+b"
    And I click "Interpréter"
    Then I should see "L'expression est syntaxiquement correcte."
    And I should see "a + b"
  # Expression correcte "simple" avec des parenthèses surnuméraires
    When I fill in "input" with "(a+b)"
    And I click "Interpréter"
    Then I should see "L'expression est syntaxiquement correcte."
    And I should see "a + b"
  # Expression correcte "compliquée"
    When I fill in "input" with "a/b/c+d-e*f/(g-h)"
    And I click "Interpréter"
    Then I should see "(a / (b * c)) + d - (e * f / (g - h))"
  # Parenthésage
    When I fill in "input" with "a-(b+c+d)"
    And I click "Interpréter"
    Then I should see "a - (b + c + d)"
    When I fill in "input" with "a/(b*c*d)"
    And I click "Interpréter"
    Then I should see "a / (b * c * d)"
  # Expression correcte mais pas arithmétique
    When I fill in "input" with "a&b"
    And I click "Interpréter"
    Then I should see "Il manque un opérateur dans l'expression « a&b »."
    When I fill in "input" with "a:b"
    And I click "Interpréter"
    Then I should see "Il manque un opérateur dans l'expression « a:b »."

  @javascript
  Scenario: Arithmetic wrong expression interpreter scenario
  # Divers messages d'erreurs qui peuvent être renvoyés
    Given I am on "tec/expression/test"
    And I wait for the page to finish loading
    When I fill in "input" with "a+(b+(c+d)"
    And I click "Interpréter"
    Then I should see "Au moins une parenthèse ouvrante n'est associée à aucune parenthèse fermante."
    When I fill in "input" with "a+(b+(c+d)))"
    And I click "Interpréter"
    Then I should see "Au moins une parenthèse fermante n'est associée à aucune parenthèse ouvrante."
    When I fill in "input" with "a+b c"
    And I click "Interpréter"
    Then I should see "Il manque un opérateur dans l'expression « b c »."
    When I fill in "input" with "a+b+"
    And I click "Interpréter"
    Then I should see "L'expression contient une opérande vide."
    When I fill in "input" with "bépo+a"
    And I click "Interpréter"
    Then I should see "L'opérande « bépo » contient des caractères non autorisés."

  @javascript
  Scenario: Boolean expression interpreter scenario
    Given I am on "tec/expression/test"
    And I wait for the page to finish loading
    And I check "Booléenne"
  # Expression vide
    When I click "Interpréter"
    Then I should see "L'expression n'a pas pu être interprétée."
    And I should see "Il manque un opérateur dans l'expression « »."
  # Expression correcte "simple"
    When I fill in "input" with "a&b"
    And I click "Interpréter"
    Then I should see "L'expression est syntaxiquement correcte."
    And I should see "a & b"
  # Expression correcte "simple" avec des parenthèses surnuméraires
    When I fill in "input" with "(a&b)"
    And I click "Interpréter"
    Then I should see "L'expression est syntaxiquement correcte."
    And I should see "a & b"
  # Expression correcte "compliquée"
    When I fill in "input" with "a&(b|c)&d"
    And I click "Interpréter"
    Then I should see "a & (b | c) & d"
  # Expression correcte mais pas booléenne
    When I fill in "input" with "a+b"
    And I click "Interpréter"
    Then I should see "Il manque un opérateur dans l'expression « a+b »."
    When I fill in "input" with "a:b"
    And I click "Interpréter"
    Then I should see "Il manque un opérateur dans l'expression « a:b »."

  @javascript
  Scenario: Boolean wrong expression interpreter scenario
  # Divers messages d'erreurs qui peuvent être renvoyés
    Given I am on "tec/expression/test"
    And I wait for the page to finish loading
    And I check "Booléenne"
    When I fill in "input" with "a|(b|(c|d)"
    And I click "Interpréter"
    Then I should see "Au moins une parenthèse ouvrante n'est associée à aucune parenthèse fermante."
    When I fill in "input" with "a|(b|(c|d)))"
    And I click "Interpréter"
    Then I should see "Au moins une parenthèse fermante n'est associée à aucune parenthèse ouvrante."
    When I fill in "input" with "a | b c"
    And I click "Interpréter"
    Then I should see "Il manque un opérateur dans l'expression « b c »."
    When I fill in "input" with "a|b|"
    And I click "Interpréter"
    Then I should see "L'expression contient une opérande vide."
    When I fill in "input" with "bépo|a"
    And I click "Interpréter"
    Then I should see "L'opérande « bépo » contient des caractères non autorisés."

  @javascript
  Scenario: Selection expression interpreter scenario
    Given I am on "tec/expression/test"
    And I wait for the page to finish loading
    And I check "Sélection"
  # Expression vide
    When I click "Interpréter"
    Then I should see "L'expression n'a pas pu être interprétée."
    And I should see "Il manque un opérateur dans l'expression « »."
  # Expression correcte "simple"
    When I fill in "input" with "a:b"
    And I click "Interpréter"
    Then I should see "L'expression est syntaxiquement correcte."
    And I should see "a : b"
  # Expression correcte "compliquée"
    When I fill in "input" with "a:(b:(c:d;e:(f:g;:h)))"
    And I click "Interpréter"
    Then I should see "a : (b : (c : d ; e : (f : g ; : h)))"
  # Expression correcte mais pas sélection
    When I fill in "input" with "a+b"
    And I click "Interpréter"
    Then I should see "Il manque un opérateur dans l'expression « a+b »."
    When I fill in "input" with "a&b"
    And I click "Interpréter"
    Then I should see "Il manque un opérateur dans l'expression « a&b »."

  @javascript
  Scenario: Selection wrong expression interpreter scenario
  # Divers messages d'erreurs qui peuvent être renvoyés
    Given I am on "tec/expression/test"
    And I wait for the page to finish loading
    And I check "Sélection"
    When I fill in "input" with "a:(b:(c:d)"
    And I click "Interpréter"
    Then I should see "Au moins une parenthèse ouvrante n'est associée à aucune parenthèse fermante."
    When I fill in "input" with ":()"
    And I click "Interpréter"
    Then I should see "Dans l'expression « : », l'élément à sélectionner est vide."
    When I fill in "input" with "a:b:c"
    And I click "Interpréter"
    Then I should see "L'élément à sélectionner « b:c » a une expression invalide."
    When I fill in "input" with "a: ; b:c"
    And I click "Interpréter"
    Then I should see "Dans l'expression « a: », l'élément à sélectionner est vide."
    When I fill in "input" with "a: b c"
    And I click "Interpréter"
    Then I should see "L'élément à sélectionner « b c » a une expression invalide."
    When I fill in "input" with "a:bépo"
    And I click "Interpréter"
    Then I should see "L'élément à sélectionner « bépo » a une expression invalide."