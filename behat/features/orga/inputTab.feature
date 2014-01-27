@dbFull
Feature: Organization input tab feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Filter on organization members in Input tab
  # Accès à l'onglet "Saisies"
    Given I am on "orga/cell/view/idCell/1"
    And I wait for the page to finish loading
    And I click element "legend[data-target='#granularity7']"
    Then I should see "4 / 6" in the "#granularity7 span.granularity-info" element
  # Filtre sur le site "Annecy"
    When I select "Annecy" from "granularity7_axissite"
    Then I should see "2 / 6" in the "#granularity7 span.granularity-info" element
  # Bouton "Réinitialiser"
    When I click element "i.fa-search-minus"
    Then I should see "6 / 6" in the "#granularity7 span.granularity-info" element

  @javascript
  Scenario: Display of the various columns (inventory status, input progress, input status)
    Given I am on "orga/cell/view/idCell/1"
    And I wait for the page to finish loading
  # Descendre depuis la cellule globale dans une cellule de granularité site
    When I click element ".fa-plus"
    And I select "Annecy" from "site"
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
    When I click element ".fa-plus"
    And I click "Vue globale"
    And I click element ".fa-plus"
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

