@dbFull
Feature: Control of classification data

  Background:
    Given I am logged in

  @javascript
  Scenario: Control of classification data scenario
    When I am on "classif/consistency/check"
    Then I should see the "coherence" datagrid
    And the row 1 of the "coherence" datagrid should contain:
      | control         | diag       | fail      |
      | Axe ne contenant aucun membre | NOT OK     | Axe vide  |
    And the row 2 of the "coherence" datagrid should contain:
      | control                                           | diag         | fail                                  |
      | Membre sans enfant d'un axe non situé à la racine | NOT OK       | scope : { Poste article 75 : [2, 3] } |
    And the row 3 of the "coherence" datagrid should contain:
      | control                                    | diag         | fail                                                |
      | Membre pour lequel manque un membre parent | NOT OK       | Poste article 75 : { scope : [Membre sans parent] } |
    # TODO : ligne 6 à changer en 4 dans version 2.3 (anciennes lignes 4 et 5 sautent).
    And the row 6 of the "coherence" datagrid should contain:
      | control                                                                      | diag | fail |
      | Indicateur contextualisé dont certains des axes sont hiérarchiquement reliés | OK   |      |