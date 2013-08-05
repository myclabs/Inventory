@dbFull
Feature: AF main algo feature

  Background:
    Given I am logged in

  @javascript
  Scenario: AF main algo scenario
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
  # On ouvre le tab "Traitement" (on ne le fera pas dans les autres tests du même onglet)
    And I open tab "Traitement"
    And I open collapse "Algorithme d'exécution"
  # Affichage de l'algo (au bon format)
    Then the "expression" field should contain ": champ_numerique"
  # Saisie algo vide
    When I fill in "expression" with ""
    And I click "Enregistrer"
    Then the following message is shown and closed: "Modification effectuée."
  # Saisie algo correct
    When I fill in "expression" with "a:b;:c;d:e;"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Modification effectuée."
  # Saisie algo incorrect
    When I fill in "expression" with "a:b;:c;de;"
    And I click "Enregistrer"
    Then the field "expression" should have error: "Il manque un opérateur dans l'expression « de »."
    And I should see "L'expression saisie présente les erreurs de syntaxe suivantes :"