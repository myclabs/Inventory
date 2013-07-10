@dbFull
Feature: Control of classification data

  Background:
    Given I am logged in

  @javascript
  Scenario: Control scenario
    When I am on "classif/consistency/check"
    Then I should see the "coherence" datagrid
    And the row 1 of the "coherence" datagrid should contain:
      | control         | diag       | fail      |
      | Axe sans membre | NOT OK     | axe_vide  |
    And the row 2 of the "coherence" datagrid should contain:
      | control                                           | diag         | fail                                  |
      | Membre sans enfant d'un axe non situé à la racine | NOT OK       | scope : { poste_article_75 : [2, 3] } |
    And the row 3 of the "coherence" datagrid should contain:
      | control                                    | diag         | fail                                                |
      | Membre pour lequel manque un membre parent | NOT OK       | poste_article_75 : { scope : [membre_sans_parent] } |
    And the row 4 of the "coherence" datagrid should contain:
      | control                                                    | diag         | fail    |
      | Indicateur sans unité ou dont l'unité n'a pas été reconnue | OK           |         |
    And the row 5 of the "coherence" datagrid should contain:
      | control                                                               | diag   | fail                                  |
      | Indicateur dont l'unité et l'unité pour ratio ne sont pas compatibles | NOT OK | unites_incompatibles : { t_co2e, kg } |
    And the row 6 of the "coherence" datagrid should contain:
      | control                                                                      | diag | fail                                                            |
      | Indicateur contextualisé dont certains des axes sont hiérarchiquement reliés | OK   | general - unites_incompatibles : { (poste_article_75 - scope) } |