@dbFull
Feature: Cell observer feature

  @javascript @readOnly
  Scenario: Observer of a single cell
    Given I am on the homepage
    And I wait for the page to finish loading
  # Login en tant qu'utilisateur connecté
    When I fill in "email" with "observateur.zone-marque@toto.com"
    And I fill in "password" with "observateur.zone-marque@toto.com"
    And I click "connection"
  # On tombe sur la page de la cellule
    Then I should see "Workspace avec données"
    Then I should see "Europe | Marque A"
    And I click element "legend[data-target='#granularity7']"
    Then I should see "Collecte en cours" in the ".cell[data-tag='/1-annee:2012/&/1-zone:europe/&/2-marque:marque_a/']" element
    # And the "inventories6" datagrid should contain 2 row
  # TODO : statut de la collecte non éditable
  # Accès à l'onglet "Analyses"
    When I click element ".current-cell .fa-bar-chart-o"
    Then I should see the popup "Analyses —  Europe | Marque A"
    When I click element "#reports3 .modal-header button"
    When I click element ".current-cell .fa-download"
    Then I should see the popup "Exports —  Europe | Marque A"
    # TODO : accès aux exports

  @javascript @readOnly
  Scenario: Observer of several cells
    Given I am on the homepage
    And I wait for the page to finish loading
  # Login en tant qu'utilisateur connecté
    When I fill in "email" with "observateur.site@toto.com"
    And I fill in "password" with "observateur.site@toto.com"
    And I click "connection"
  # On tombe sur la liste des cellules
    Then I should see "Observateur Annecy"
    And I should see "Observateur Chambéry"
  # Accès à une des cellules
    When I click "Observateur Annecy"
    Then I should see "Workspace avec données"
    Then I should see "2012 | Annecy | Énergie"
