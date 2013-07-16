@dbFull
Feature: Organizational relevance tab feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Cell relevance scenario
  # Accès au volet "Pertinence"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Organisation"
    And I open tab "Pertinence"
  # Ouverture volet "Zone|Marque"
    And I open collapse "Zone | Marque"
    Then I should see the "relevant_c1_g2" datagrid
    And the row 1 of the "relevant_c1_g2" datagrid should contain:
      | zone | marque | relevant | allParentsRelevant |
      | Europe | Marque A | Pertinente | Toutes pertinentes |
  # Édition pertinence
    When I set "Non pertinente" for column "relevant" of row 1 of the "relevant_c1_g2" datagrid with a confirmation message
    Then the row 1 of the "relevant_c1_g2" datagrid should contain:
      | zone | marque | relevant | allParentsRelevant |
      | Europe | Marque A | Pertinente | Toutes pertinentes |


