@dbFull
Feature: Organization input tab feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Filter on organization members in Input tab
  # Accès à l'onglet "Saisies"
    Given I am on "orga/cell/details/idCell/1"
    And I open tab "Saisies"
    And I open collapse "Année | Site"
    Then I should see the "aFGranularity1Input7" datagrid
    And the "aFGranularity1Input7" datagrid should contain 8 row
  # Filtre sur le site "Annecy"
    When I open collapse "Filtres"
    And I select "Annecy" from "aFGranularity1Input7_site_filterForm"
    And I click "Filtrer"
    Then the "aFGranularity1Input7" datagrid should contain 2 row
  # Bouton "Réinitialiser"
    When I open collapse "Filtres"
    And I click "Réinitialiser"
    Then the "aFGranularity1Input7" datagrid should contain 8 row

  @javascript
  Scenario: Display of input tab when the inventory granularity has not been defined
    Given I am on "orga/organization/manage"
    And I wait for the page to finish loading
    Then I should see the "organizations" datagrid
  # Ajout d'une organisation
    When I click "Ajouter"
    Then I should see the popup "Ajout d'une organisation"
    When I fill in "Libellé" with "Test"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps ainsi qu'un rechargement de la page."
    And the row 2 of the "organizations" datagrid should contain:
      | label  |
      | Test   |
  # Lien vers le détail de l'organisation
    When I click "Cliquer pour accéder" in the row 2 of the "organizations" datagrid
    Then I should see "Vue globale Test"
    # TODO : ajouter message pour indiquer qu'aucune granularité n'a été associée à des saisies.

  @javascript
  Scenario: Access to an input which is associated to the current cell
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
  # Saisie associée à la cellule globale
    And I open collapse "Niveau organisationnel global"
    Then I should see the "aFGranularity1Input1" datagrid
    And the "aFGranularity1Input1" datagrid should contain 1 row
  # Saisie associée à une cellule non globale (Europe|Marque A)
    When I click element "#goTo2"
    And I open collapse "Zone | Marque"
    Then I should see the "aFGranularity2Input2" datagrid
    And the "aFGranularity2Input2" datagrid should contain 1 row

  @javascript
  Scenario: Display of the various columns (inventory status, input progress, input status)
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
  # Descendre depuis la cellule globale dans une cellule de granularité site
    When I select "Annecy" from "site"
    And I click element "#goTo3"
  # Cas inventaire en cours, saisie complète
    When I open collapse "Année | Site"
    Then the "aFGranularity5Input7" datagrid should contain a row:
      | annee | inventoryStatus | advancementInput | stateInput      |
      | 2012  | Ouvert          | 100%             | Saisie complète |
  # Cas inventaire en cours, saisie incomplète / saisie terminée
    When I close collapse "Année | Site"
    When I open collapse "Année | Site | Catégorie"
    Then the "aFGranularity5Input8" datagrid should contain a row:
      | annee | categorie | inventoryStatus | advancementInput | stateInput      |
      | 2012  | Énergie   | Ouvert          | 100%             | Saisie terminée |
    And the "aFGranularity5Input8" datagrid should contain a row:
      | annee | categorie      | inventoryStatus | advancementInput | stateInput        |
      | 2012  | Test affichage | Ouvert          | 14%              | Saisie incomplète |
  # Cas inventaire non lancé, inventaire clôturé
    When I click "Vue globale"
    And I select "Europe" from "zone"
    And I select "Marque B" from "marque"
    And I click element "#goTo2"
    And I open collapse "Année | Site"
    Then the "aFGranularity3Input7" datagrid should contain a row:
      | annee | site     | inventoryStatus | advancementInput | stateInput      |
      | 2012  | Grenoble | Fermé           | 100%             | Saisie terminée |
    And the "aFGranularity3Input7" datagrid should contain a row:
      | annee | site     | inventoryStatus | advancementInput | stateInput      |
      | 2013  | Grenoble | Non lancé       |                  |                 |

