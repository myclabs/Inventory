@dbFull
Feature: AF main algo feature

  Background:
    Given I am logged in

  @javascript
  Scenario: AF main algo scenario
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Traitement"
    And I open collapse "Algorithme d'exécution"
  # Affichage de l'algo (au bon format)
    # TODO une fois le paramétrage terminé.
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
    Then the following message is shown and closed: "L'expression n'a pas pu être interprétée. Il manque un opérateur dans l'expression « de »."