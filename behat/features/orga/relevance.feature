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
  # Ouverture volet "Année | Site | Catégorie", vérification que les cellules associées à Annecy ont bien toutes leurs cellules parentes pertinentes
    When I open collapse "Année | Site | Catégorie"
    Then I should see the "relevant_c1_g8" datagrid
    And the row 1 of the "relevant_c1_g8" datagrid should contain:
      | site     |
      | Annecy |
  # Ouverture volet "Zone|Marque"
    And I open collapse "Zone | Marque"
    Then I should see the "relevant_c1_g2" datagrid
    And the row 1 of the "relevant_c1_g2" datagrid should contain:
      | zone   | marque   | relevant   |
      | Europe | Marque A | Pertinente |
  # Édition pertinence "Europe|Marque A"
    When I set "Non pertinente" for column "relevant" of row 1 of the "relevant_c1_g2" datagrid with a confirmation message
    Then the row 1 of the "relevant_c1_g2" datagrid should contain:
      | zone   | marque   | relevant       |
      | Europe | Marque A | Non pertinente |
  # Vérification de la mise à jour de l'attribut "AllParentsRelevant" pour la granularité "Année | Site | Catégorie"
    When I close collapse "Année | Site | Catégorie"
    And I wait 1 seconds
    And I open collapse "Année | Site | Catégorie"
    And I wait 1 seconds
    Then I should see the "relevant_c1_g8" datagrid
    And the row 1 of the "relevant_c1_g8" datagrid should not contain:
      | site     |
      | Annecy |
  # On fait l'inverse, on rend à nouveau pertinente la cellule parente
    When I set "Pertinente" for column "relevant" of row 1 of the "relevant_c1_g2" datagrid with a confirmation message
    Then the row 1 of the "relevant_c1_g2" datagrid should contain:
      | zone   | marque   | relevant   |
      | Europe | Marque A | Pertinente |
    When I close collapse "Année | Site | Catégorie"
    And I wait 1 seconds
    And I open collapse "Année | Site | Catégorie"
    And I wait 1 seconds
    Then I should see the "relevant_c1_g8" datagrid
    And the row 1 of the "relevant_c1_g8" datagrid should contain:
      | site     |
      | Annecy |