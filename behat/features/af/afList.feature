@dbFull
Feature: AF list edit feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of an accounting form
  # Affichage du datagrid
    Given I am on "af/af/list"
    Then I should see the "listAF" datagrid
    And the row 1 of the "listAF" datagrid should contain:
      | category                          | label                                               | ref                                |
      | Catégorie contenant un formulaire | Combustion de combustible, mesuré en unité de masse | combustion_combustible_unite_masse |
  # Ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un formulaire"
  # Aucun champ rempli
    When I click "Valider"
    Then the field "listAF_category_addForm" should have error: "Merci de renseigner ce champ."
    And the field "listAF_ref_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout, identifiant caractères non autorisés
    When I select "Catégorie contenant un formulaire" from "listAF_category_addForm"
    And I fill in "listAF_label_addForm" with "Test"
    And I fill in "listAF_ref_addForm" with "bépo"
    And I click "Valider"
    Then the field "listAF_ref_addForm" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Ajout, identifiant déjà utilisé
    When I fill in "listAF_ref_addForm" with "combustion_combustible_unite_masse"
    And I click "Valider"
    Then the field "listAF_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Ajout, saisie correcte
    When I fill in "listAF_ref_addForm" with "test"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
    And the row 4 of the "listAF" datagrid should contain:
      | category                          | label | ref  |
      | Catégorie contenant un formulaire | Test  | test |

  @javascript
  Scenario: Edition of an accounting form
    Given I am on "af/af/list"
    Then I should see the "listAF" datagrid
  # Modification catégorie et libellé

