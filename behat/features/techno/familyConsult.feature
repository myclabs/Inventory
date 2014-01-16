@dbFull
Feature: Family consult feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Non empty family consult scenario
    Given I am on "techno/family/details/id/5"
    And I wait for the page to finish loading
    Then I should see "Famille test non vide"
  # Affichage de la catégorie (hiérarchie des catégories en l'occurrence)
    And I should see "Catégorie contenant une famille / Sous-catégorie contenant une famille"
  # Affichage de l'unité
    And I should see "kg équ. CO2/t"
  # Documentation
    And I should see a "h1:contains('Documentation de la famille test')" element
  # En-têtes de dimensions commencent par une majuscule
    And I should see "Combustible"
    And I should see "Processus"
  # Arrondi à trois chiffres significatifs
  # Séparateur décimal en français
    And I should see "0,123 ± 15 %" in the "#elements-charbon-amont_combustion" element
  # Séparateur de milliers en français
    And I should see "12 300 ± 15 %" in the "#elements-charbon-combustion" element
  # Affichage cellules vides
    And I should see "-" in the "#elements-gaz_naturel-amont_combustion" element

  @javascript
  Scenario: Empty family consult scenario
    Given I am on "techno/family/details/id/4"
    And I wait for the page to finish loading
    Then I should see "Famille test vide"
    And I should see "Aucune donnée à afficher."
  # Documentation
    And I should see "Il n'y a aucune documentation pour cette famille."
