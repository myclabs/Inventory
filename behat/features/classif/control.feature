@dbFull
Feature: Control of classification data

  Background:
    Given I am logged in

  @javascript
  Scenario: Control of classification data scenario
    When I am on "classif/consistency/check"
    Then I should see the "coherence" datagrid
    And the row 1 of the "coherence" datagrid should contain:
      | control                        | diag       | fail      |
      | Axe ne contenant aucun élément | NOT OK     | Axe vide  |
    And the row 2 of the "coherence" datagrid should contain:
      | control                                            | diag         | fail                                  |
      | Élément sans enfant d'un axe non situé à la racine | NOT OK       | scope : { Poste article 75 : [2, 3] } |
    And the row 3 of the "coherence" datagrid should contain:
      | control                                      | diag         | fail                                                |
      | Élément pour lequel manque un élément parent | NOT OK       | Poste article 75 : { scope : [Élément sans parent] } |
    And the row 4 of the "coherence" datagrid should contain:
      | control                                                                      | diag | fail |
      | Indicateur contextualisé dont certains des axes sont hiérarchiquement reliés | OK   |      |