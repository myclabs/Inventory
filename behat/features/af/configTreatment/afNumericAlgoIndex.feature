@dbFull
Feature: AF indexes of a numeric algo feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Edit indexes of a numeric algo scenario
    Given I am on "af/edit/menu/id/4/onglet/traitement"
    And I wait for the page to finish loading
    And I open collapse "Algorithmes numériques"
  # L'algorithme numérique indexé dans le populate est une saisie de champ numérique
    And I open collapse "Saisies de champs numériques"
    Then I should see the "algoNumericInput" datagrid
  # Vérification du contenu
    And the row 1 of the "algoNumericInput" datagrid should contain:
      | contextIndicator |
      | GES - Général    |
    When I click "Indexation" in the row 1 of the "algoNumericInput" datagrid
    Then I should see the popup "Indexation"
    And I should see the "algoResultIndexes" datagrid
    And the row 1 of the "algoResultIndexes" datagrid should contain:
      | axis | type         | value |
      | Gaz  | Valeur fixée | CO2   |
    And the row 2 of the "algoResultIndexes" datagrid should contain:
      | axis             | type       | value                     |
      | Poste article 75 | Algorithme | expression_sel_index_algo |
  # Édition de la colonne "Valeur" pour une valeur fixée
    When I set "CH4" for column "value" of row 1 of the "algoResultIndexes" datagrid
    And I click element "#algoNumericInput_resultIndex_popup .close:contains('×')"
    Then the following message is shown and closed: "Modification effectuée."
    When I click "Indexation" in the row 1 of the "algoNumericInput" datagrid
    Then the row 1 of the "algoResultIndexes" datagrid should contain:
      | axis | type         | value |
      | Gaz  | Valeur fixée | CH4   |
  # Édition de la colonne "Valeur" pour un algorithme
    When I set "c_s_s" for column "value" of row 2 of the "algoResultIndexes" datagrid
    And I click element "#algoNumericInput_resultIndex_popup .close:contains('×')"
    Then the following message is shown and closed: "Modification effectuée."
    When I click "Indexation" in the row 1 of the "algoNumericInput" datagrid
    Then the row 2 of the "algoResultIndexes" datagrid should contain:
      | axis             | type       | value                  |
      | Poste article 75 | Algorithme | c_s_s |
  # Édition de la colonne "Mode de détermination" (modif pour "Algorithme")
    When I set "Algorithme" for column "type" of row 1 of the "algoResultIndexes" datagrid
    Then the row 1 of the "algoResultIndexes" datagrid should contain:
      | axis | type       | value |
      | Gaz  | Algorithme |       |
    When I click element "#algoNumericInput_resultIndex_popup .close:contains('×')"
    Then the following message is shown and closed: "Modification effectuée."
    And I click "Indexation" in the row 1 of the "algoNumericInput" datagrid
  # Édition de la colonne "Mode de détermination" (modif pour "Valeur fixée")
    When I set "Valeur fixée" for column "type" of row 2 of the "algoResultIndexes" datagrid
    Then the row 2 of the "algoResultIndexes" datagrid should contain:
      | axis              | type         | value |
      | Poste article 75  | Valeur fixée |       |
    And I click element "#algoNumericInput_resultIndex_popup .close:contains('×')"
    Then the following message is shown and closed: "Modification effectuée."

  @javascript
  Scenario: Influence of a change of indicator on indexes of a numeric algo scenario
    Given I am on "af/edit/menu/id/4/onglet/traitement"
    And I wait for the page to finish loading
    And I open collapse "Algorithmes numériques"
  # L'algorithme numérique indexé dans le populate est une saisie de champ numérique
    And I open collapse "Saisies de champs numériques"
    Then I should see the "algoNumericInput" datagrid
  # D'apres le scénario précédent cet algo est associé à l'indicateur contextualisé "GES - Général" et indexé suivant les deux axes de cet indicateur
  # On modifie l'indicateur
    When I set "Chiffre d'affaires - Général" for column "contextIndicator" of row 1 of the "algoNumericInput" datagrid with a confirmation message
  # On vérifie que l'indexation est vide
    And I click "Indexation" in the row 1 of the "algoNumericInput" datagrid
    Then the "algoResultIndexes" datagrid should contain 0 row
    And I click "×"
  # On revient à l'ancien indicateur
    When I set "GES - Général" for column "contextIndicator" of row 1 of the "algoNumericInput" datagrid with a confirmation message
  # On vérifie que l'indexation est encore vide
    And I click "Indexation" in the row 1 of the "algoNumericInput" datagrid
    Then I should not see "Valeur fixée"
    # And I should not see "CO2" (présent ailleurs dans la page)
    # And I should not see "Algorithme" (présent ailleurs dans la page)
    And I should not see "expression_sel_index_algo"
