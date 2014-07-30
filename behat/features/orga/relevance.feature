@dbFull
Feature: Organizational relevance tab feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Cell relevance scenario
  # Accès au volet "Pertinence"
    Given I am on "orga/organization/edit/workspace/1"
    And I wait for the page to finish loading
    And I open tab "Pertinence"
  # Ouverture volet "Zone|Marque"
    And I open collapse "Zone | Marque"
    Then I should see the "datagridCellRelevance3" datagrid
    And the row 1 of the "datagridCellRelevance3" datagrid should contain:
      | zone   | marque   | relevant   |
      | Europe | Marque A | Pertinente |
  # Édition pertinence "Europe|Marque A"
    When I set "Non pertinente" for column "relevant" of row 1 of the "datagridCellRelevance3" datagrid with a confirmation message
    Then the row 1 of the "datagridCellRelevance3" datagrid should contain:
      | zone   | marque   | relevant       |
      | Europe | Marque A | Non pertinente |
  # On fait l'inverse, on rend à nouveau pertinente la cellule parente
    When I set "Pertinente" for column "relevant" of row 1 of the "datagridCellRelevance3" datagrid with a confirmation message
    Then the row 1 of the "datagridCellRelevance3" datagrid should contain:
      | zone   | marque   | relevant   |
      | Europe | Marque A | Pertinente |