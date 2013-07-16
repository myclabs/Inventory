@dbFull
Feature: Organizational subunits tab feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Global cell subunits tab scenario
  # Accès au volet "Sous-unités"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Organisation"
    And I open tab "Sous-unités"
  # Ouverture d'un volet pour une granularité non navigable
    And I open collapse "Année | Site | Catégorie"
    Then I should see the "child_c1_g8" datagrid
    And the row 1 of the "child_c1_g8" datagrid should contain:
      | annee | site | categorie |
      | 2013 | Annecy| Énergie |
    And I should not see "Navigation"
  # Filtres
    # TODO : tester filtres
    # When I open collapse "Filtres"
  # Ouverture d'un volet pour une granularité navigable
    When I open collapse "Zone | Marque"
    Then I should see the "child_c1_g2" datagrid
    And the row 1 of the "child_c1_g2" datagrid should contain:
      | zone | marque | link |
      | Europe | Marque A | Aller à |
  # Lien vers cellule
    When I click "Aller à" in the row 1 of the "child_c1_g2" datagrid
    Then I should see "Europe | Marque A Organisation avec données"

  @javascript
  Scenario: Nonglobal cell subunits tab scenario
  # Accès à la cellule "Europe / Marque B"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    When I select "Marque B" from "marque"
    And I click element "#goTo2"
    Then I should see "Europe | Marque B Organisation avec données"
  # Accès au volet "Sous-unités" et au collapse "Site"
    When I open tab "Organisation"
    And I open tab "Sous-unités"
    And I wait 2 seconds
    And I open collapse "Année | Site"
    Then I should see the "child_c3_g7" datagrid
    And the "child_c3_g7" datagrid should contain 1 row
    And the row 1 of the "child_c3_g7" datagrid should contain:
      | annee | site    |
      | 2013 | Grenoble |