@dbFull
Feature: Organization inventory tab, involved users column feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Organization inventory tab, involved users column scenario, cell 2012 Europe Marque A
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Collectes"
    And I open collapse "Année | Zone | Marque"
    Then I should see the "inventories6" datagrid
    And the row 1 of the "inventories6" datagrid should contain:
      | annee | zone   | marque   |
      | 2012  | Europe | Marque A |
    When I click "Intervenants" in the row 1 of the "inventories6" datagrid
  # Rôles associés à l'organisation
    Then I should see "admin@myc-sense.com"
    And I should see "administrateur.application@toto.com"
  # Rôles associés à la cellule globale
    And I should see "administrateur.global@toto.com"
    And I should see "contributeur.global@toto.com"
    And I should see "observateur.global@toto.com"
  # Rôles associés à la cellule "2012 | Europe"
    And I should see "administrateur.zone-marque@toto.com"
    And I should see "contributeur.zone-marque@toto.com"
    And I should see "observateur.zone-marque@toto.com"
  # Rôles associés aux sous-cellules
  # Rôles associés à Annecy
    And I should see "administrateur.site@toto.com"
    And I should see "contributeur.site@toto.com"
    And I should see "observateur.site@toto.com"
  # Rôles associés à Chambéry : les mêmes

  @javascript
  Scenario: Organization inventory tab, involved users column scenario, cell 2012 Annecy
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Collectes"
    And I open collapse "Année | Site"
    Then I should see the "inventories7" datagrid
    And the row 1 of the "inventories7" datagrid should contain:
      | annee | site   |
      | 2012  | Annecy |
    When I click "Intervenants" in the row 1 of the "inventories7" datagrid
  # Rôles associés à l'organisation
    Then I should see "admin@myc-sense.com"
    And I should see "administrateur.application@toto.com"
  # Rôles associés à la cellule globale
    And I should see "administrateur.global@toto.com"
    And I should see "contributeur.global@toto.com"
    And I should see "observateur.global@toto.com"
  # Rôles associés à la cellule "2012 | Europe"
    And I should see "administrateur.zone-marque@toto.com"
    And I should see "contributeur.zone-marque@toto.com"
    And I should see "observateur.zone-marque@toto.com"
  # Rôles associés à Annecy
    And I should see "administrateur.site@toto.com"
    And I should see "contributeur.site@toto.com"
    And I should see "observateur.site@toto.com"