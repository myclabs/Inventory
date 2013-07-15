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
    Then I should see the "aFGranularity5Input8" datagrid
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

