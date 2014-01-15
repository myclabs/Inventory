@dbFull
Feature: Family list edit feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of a parameter family, correct input
    Given I am on "techno/family/list-edit"
    Then I should see the "familyDatagrid" datagrid
  # Affichage du datagrid
    And the row 1 of the "familyDatagrid" datagrid should contain:
      | category                        | label                                               | ref                                | unit           |
      | Catégorie contenant une famille | Combustion de combustible, mesuré en unité de masse | combustion_combustible_unite_masse | kg équ. CO2/kg |
  # Ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'une famille"
    When I select "Catégorie contenant une famille" from "familyDatagrid_category_addForm"
    And I fill in "familyDatagrid_label_addForm" with "AAA"
    And I fill in "familyDatagrid_ref_addForm" with "aaa"
    And I fill in "familyDatagrid_unit_addForm" with "m"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
    And the "familyDatagrid" datagrid should contain a row:
      | category                        | label | ref  | unit |
      | Catégorie contenant une famille | AAA   | aaa  | m    |
  # TODO : autoriser la création à la racine

  @javascript
  Scenario: Creation of a parameter family, incorrect input
    Given I am on "techno/family/list-edit"
    Then I should see the "familyDatagrid" datagrid
  # Ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'une famille"
  # Aucun champ rempli
    When I click "Valider"
    Then the field "familyDatagrid_category_addForm" should have error: "Merci de renseigner ce champ."
    And the field "familyDatagrid_label_addForm" should have error: "Merci de renseigner ce champ."
    And the field "familyDatagrid_ref_addForm" should have error: "Merci de renseigner ce champ."
    And the field "familyDatagrid_unit_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout, identifiant caractères non autorisés
    When I select "Catégorie contenant une famille" from "familyDatagrid_category_addForm"
    And I fill in "familyDatagrid_label_addForm" with "Test"
    And I fill in "familyDatagrid_ref_addForm" with "bépo"
    And I fill in "familyDatagrid_unit_addForm" with "m"
    And I click "Valider"
    Then the field "familyDatagrid_ref_addForm" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Ajout, identifiant déjà utilisé
    When I fill in "familyDatagrid_ref_addForm" with "combustion_combustible_unite_masse"
    And I click "Valider"
    Then the field "familyDatagrid_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Ajout, unité invalide
    When I fill in "familyDatagrid_ref_addForm" with "test"
    And I fill in "familyDatagrid_unit_addForm" with "auie"
    And I click "Valider"
    Then the field "familyDatagrid_unit_addForm" should have error: "Merci de saisir un identifiant d'unité valide."

  @javascript
  Scenario: Link to reach a parameter family from the family list edit datagrid
    Given I am on "techno/family/list-edit"
    Then I should see the "familyDatagrid" datagrid
  # Clic sur "Cliquer pour accéder"
    When I click "Cliquer pour accéder" in the row 1 of the "familyDatagrid" datagrid
    Then I should see a "h1:contains('Combustion de combustible, mesuré en unité de masse')" element
