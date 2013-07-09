@dbFull
Feature: classifControl

  Background:
    Given I am logged in

  @javascript
  Scenario: classifControl1
    When I am on "classif/consistency/check"
    Then I should see the "coherence" datagrid
    And the row 1 of the "coherence" datagrid should contain:
      | control       | diag         | fail    |
      | Axe sans membre | NOT OK | gaz, poste_article_75, scope, perimetre_fret, vecteur_energetique  |
    And the row 2 of the "coherence" datagrid should contain:
      | control       | diag         | fail    |
      | Membre sans enfant d'un axe non situé à la racine | OK |   |
    And the row 3 of the "coherence" datagrid should contain:
      | control       | diag         | fail    |
      | Membre pour lequel manque un membre parent | OK |   |
    And the row 4 of the "coherence" datagrid should contain:
      | control       | diag         | fail    |
      | Indicateur sans unité ou dont l'unité n'a pas été reconnue | OK |   |
    And the row 5 of the "coherence" datagrid should contain:
      | control       | diag         | fail    |
      | Indicateur dont l'unité et l'unité pour ratio ne sont pas compatibles | OK |   |
    And the row 6 of the "coherence" datagrid should contain:
      | control       | diag         | fail    |
      | Indicateur contextualisé dont certains des axes sont hiérarchiquement reliés | OK |   |