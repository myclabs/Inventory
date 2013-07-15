@dbFull
Feature: Family tree consult feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Consult family tree
    Given I am on "techno/family/tree"
    And I wait 5 seconds
  # Accès à la page de la famille en consultation
    And I click "Combustion de combustible, mesuré en unité de masse"
    Then I should see "Catégorie : Catégorie contenant une famille | Type : Facteur d'émission | Unité : kg équ. CO2/kg | Tags : Aucun"
  # Accès et contenu onglet "Documentation", documentation vide
    When I open tab "Documentation"
    Then I should see "Il n'y a aucune documentation pour cette famille."
