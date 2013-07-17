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
      | 2012 | Annecy| Énergie |
    And I should not see "Navigation"
  # Filtres
    # TODO : tester filtres
    # When I open collapse "Filtres"
  # Ouverture d'un volet pour une granularité navigable
    When I open collapse "Site"
    Then I should see the "child_c1_g3" datagrid
    And the row 1 of the "child_c1_g3" datagrid should contain:
      | site |  link |
      | Annecy | Aller à |
  # Lien vers cellule
    When I click "Aller à" in the row 1 of the "child_c1_g3" datagrid
    Then I should see "Annecy Organisation avec données"

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
      | 2012 | Grenoble |

  @javascript
  Scenario: Check that nonrelevant cells and cells included in a nonrelevant cell are hidden in the subunits tab
  # État des lieux initial dans l'onglet "Sous-unités"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Organisation"
    And I open tab "Sous-unités"
    When I open collapse "Zone | Marque"
    Then I should see the "child_c1_g2" datagrid
    And the row 1 of the "child_c1_g2" datagrid should contain:
      | zone | marque |
      | Europe | Marque A |
    When I open collapse "Année | Zone | Marque"
    Then I should see the "child_c1_g6" datagrid
    And the row 1 of the "child_c1_g6" datagrid should contain:
      | annee | zone | marque |
      | 2012 | Europe | Marque A |
  # Maintenant on rend "Europe | Marque A" non pertinente
    When I reload the page
    And I wait for the page to finish loading
    And I open tab "Organisation"
    When I open tab "Pertinence"
    And I open collapse "Zone | Marque"
    Then I should see the "relevant_c1_g2" datagrid
    And the row 1 of the "relevant_c1_g2" datagrid should contain:
      | zone | marque |
      | Europe | Marque A |
    When I set "Non pertinente" for column "relevant" of row 1 of the "relevant_c1_g2" datagrid with a confirmation message
  # On vérifie le masquage dans l'onglet "Sous-unités"
    When I open tab "Sous-unités"
    When I open collapse "Zone | Marque"
    Then I should see the "child_c1_g2" datagrid
  # Vérification que les cellules non pertinentes ne sont plus affichées
    And the row 1 of the "child_c1_g2" datagrid should contain:
      | zone | marque |
      | Europe | Marque B |
  # Vérification que les cellules incluses dans des cellules non pertinentes ne sont plus affichées
    When I open collapse "Année | Zone | Marque"
    Then I should see the "child_c1_g6" datagrid
    And the row 1 of the "child_c1_g6" datagrid should contain:
      | annee | zone | marque |
      | 2012 | Europe | Marque B |