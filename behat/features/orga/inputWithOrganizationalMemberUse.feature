@dbFull
Feature: Input with organization member use feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Input with organization member use scenario
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
  # On ouvre l'inventaire 2012|marque B car il est fermé au départ
    When I open tab "Collectes"
    And I open collapse "Année | Zone | Marque"
    Then the row 2 of the "inventories6" datagrid should contain:
      | annee | zone   | marque   | inventoryStatus |
      | 2012  | Europe | Marque B | Fermé           |
    And I set "Ouvert" for column "inventoryStatus" of row 2 of the "inventories6" datagrid with a confirmation message
  # Accès à la saisie voulue
    And I open tab "Saisies"
    And I open collapse "Année | Site | Catégorie"
    Then the row 8 of the "aFGranularity1Input8" datagrid should contain:
      | site     | categorie      |
      | Grenoble | Forfait marque |
    When I click "Cliquer pour accéder" in the row 8 of the "aFGranularity1Input8" datagrid
  # Aperçu des résultats
    And I click "Aperçu des résultats"
    Then I should see "Total : 2 t équ. CO2"
  # Saisie et enregistrement
    When I fill in "Champ sans effet" with "0"
    And I click "Enregistrer"
    And I open tab "Résultats"
    Then I should see "Total : 2 t équ. CO2"
  # Détails calculs
    When I open tab "Détails calculs"
    And I open collapse "Formulaire maître"
    And I open collapse "algo_numerique_forfait_marque"
    Then I should see "Marque : marque B"