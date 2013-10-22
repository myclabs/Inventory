@dbFull
Feature: AF copy feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Copy of the combustion form and test of the copied form scenario
    Given I am on "af/af/list"
    And I wait for the page to finish loading
    Then I should see the "listAF" datagrid
    And the "listAF" datagrid should contain 8 row
    And the row 1 of the "listAF" datagrid should contain:
      | label                                               | ref                                |
      | Combustion de combustible, mesuré en unité de masse | combustion_combustible_unite_masse |
    And I click "Dupliquer" in the row 1 of the "listAF" datagrid
    Then the following message is shown and closed: "Ajout effectué"
    And the "listAF" datagrid should contain 9 row
    And the row 9 of the "listAF" datagrid should contain:
      | label                                               | ref                                     |
      | Combustion de combustible, mesuré en unité de masse | combustion_combustible_unite_masse_copy |
    When I click "Test" in the row 9 of the "listAF" datagrid
    And I select "Charbon" from "nature_combustible"
    And I fill in "quantite_combustible" with "10"
  # Formulaire copié : aperçu des résultats
    And I click "Aperçu des résultats"
    Then I should see "Total : 33,3 t équ. CO2"
  # Formulaire copié : enregistrement de la saisie
    When I click "Enregistrer"
    Then the following message is shown and closed: "Enregistrement effectué (saisie complète)."
  # Formulaire copié : accès détails calculs et vérification calculs corrects
    When I open tab "Détails calculs"
    And I open collapse "Formulaire maître"
    Then I should see "emissions_combustion Émissions liées à la combustion"
    When I open collapse "emissions_amont"
    Then I should see "Type : Expression"
    And I should see "quantite_combustible * fe_amont"
    When I click element "#combustion_combustible_unite_masse_copy__emissions_amont_wrapper .icon-zoom-in"
    Then I should see the popup "emissions_amont (Émissions liées aux processus amont de la combustion)"
    And I should see "quantite_combustible * fe_amont"
    And I should see "Produit"
    When I click "×"
    Then I should see "Valeur : 2,54 t équ. CO2 ± 20 %"

  @javascript
  Scenario: Copy of the forfait emission fonction marque form and test of the copied form scenario
    Given I am on "af/af/list"
    And I wait for the page to finish loading
    Then I should see the "listAF" datagrid
    And the "listAF" datagrid should contain 8 row
    And the row 8 of the "listAF" datagrid should contain:
      | label                                      | ref                       |
      | Forfait émissions en fonction de la marque | formulaire_forfait_marque |
    And I click "Dupliquer" in the row 8 of the "listAF" datagrid
    Then the following message is shown and closed: "Ajout effectué"
    And the "listAF" datagrid should contain 9 row
    And the row 9 of the "listAF" datagrid should contain:
      | label                                      | ref                            |
      | Forfait émissions en fonction de la marque | formulaire_forfait_marque_copy |
    When I click "Test" in the row 9 of the "listAF" datagrid
    And I click "Aperçu des résultats"
    Then I should see "Total : 1 t équ. CO2"
  # Saisie et enregistrement
    When I fill in "Champ sans effet" with "0"
    And I click "Enregistrer"
    And I open tab "Résultats"
    Then I should see "Total : 1 t équ. CO2"
  # Détails calculs
    When I open tab "Détails calculs"
    And I open collapse "Formulaire maître"
    And I open collapse "algo_numerique_forfait_marque"
    Then I should see "Marque : marque A"