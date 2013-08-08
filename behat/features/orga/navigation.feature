@dbFull
Feature: Organization navigation feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Organization navigation scenario
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
  # Descendre depuis la cellule globale dans une cellule de granularité site
    When I select "Chambéry" from "site"
    And I click element "#goTo3"
    Then I should see "Chambéry Organisation avec données"
  # Vérification qu'on tombe bien sur l'onglet "Saisies"
    When I open collapse "Année | Site | Catégorie"
    Then I should see the "aFGranularity6Input8" datagrid
  # Remonter au niveau zone-marque
    When I click "Europe | Marque A"
    Then I should see "Europe | Marque A Organisation avec données"
  # Vérification qu'on tombe bien sur l'onglet "Saisies"
    When I open collapse "Année | Site | Catégorie"
    Then I should see the "aFGranularity2Input8" datagrid
  # Remonter au niveau global
    When I click "Vue globale"
    Then I should see "Vue globale"
  # Vérification qu'on tombe bien sur l'onglet "Saisies"
    When I open collapse "Année | Site | Catégorie"
    Then I should see the "aFGranularity1Input8" datagrid
  # Descendre au niveau zone|marque
    When I select "Marque sans site" from "marque"
    And I click element "#goTo2"
    Then I should see "Europe | Marque sans site Organisation avec données"
  # Vérification que l'élément "Vue globale" cliquable est présent, pour tester à l'inverse qu'il est absent dans les tests ACL
    And I should see a "#navigationParent a:contains('Vue globale')" element

  @javascript
  Scenario: Try to reach a non relevant cell using navigation tool
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
  # Rendre non pertinente la cellule "Europe | Marque A"
    When I open tab "Organisation"
    And I open tab "Pertinence"
    And I open collapse "Zone | Marque"
    Then I should see the "relevant_c1_g2" datagrid
    And the row 1 of the "relevant_c1_g2" datagrid should contain:
      | zone | marque | relevant | allParentsRelevant |
      | Europe | Marque A | Pertinente | Toutes pertinentes |
  # Édition pertinence "Europe|Marque A"
    When I set "Non pertinente" for column "relevant" of row 1 of the "relevant_c1_g2" datagrid with a confirmation message
    Then the row 1 of the "relevant_c1_g2" datagrid should contain:
      | zone | marque | relevant | allParentsRelevant |
      | Europe | Marque A | Non pertinente | Toutes pertinentes |
  # Essayer d'atteindre la cellule "Europe|Marque A" avec le volet de navigation
    When I select "Europe" from "zone"
    And I select "Marque A" from "marque"
    And I click element "#goTo2"
    Then the following message is shown and closed: "Cette unité organisationnelle n'est pas pertinente, il n'est donc pas possible d'y accéder."
