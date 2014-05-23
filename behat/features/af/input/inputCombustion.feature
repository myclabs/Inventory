@dbFull @readOnly
Feature: Combustion input feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Combustion correct input scenario
  # Accès au formulaire
    Given I am on "af/af/test/id/1"
    And I wait for the page to finish loading
  # Saisie
    And I select "Charbon" from "nature_combustible"
    And I fill in "quantite_combustible" with "10"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Enregistrement effectué (saisie complète)."
  # Vérification contenu onglet détails calculs
    When I open tab "Détails calculs"
    And I open collapse "Formulaire maître"
  # Vérification libellés collapses (identifiant + libellé algo)
    Then I should see "emissions_combustion Émissions liées à la combustion"
  # Vérification contenu pour un algo de type "Expression numérique"
    When I open collapse "emissions_amont"
    Then I should see "Type : Expression"
    And I should see "quantite_combustible * fe_amont"
    When I click element "#collapse_1__emissions_amont .fa-search"
    Then I should see the popup "emissions_amont (Émissions liées aux processus amont de la combustion)"
    And I should see "quantite_combustible * fe_amont"
    And I should see "Produit"
    When I click "×"
    Then I should see "Valeur : 2,54 t équ. CO2 ± 20 %"
  # Vérification contenu pour un algo de type "paramètre"
    When I open collapse "fe_amont"
    Then I should see "Type : Paramètre"
    And I should see "Processus : Amont combustion"
    And I should see "Valeur : 254 kg équ. CO2/t ± 20 %"
    When I click element "#collapse_1__emissions_amont__fe_amont a:contains('Combustion de combustible, mesuré en unité de masse')"
    Then I should see "Il n'y a aucune documentation pour cette famille."
