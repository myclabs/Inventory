@dbFull
Feature: AF boolean field feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of a boolean field, correct input
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Champs booléens"
    Then I should see the "checkboxFieldDatagrid" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un champ booléen"
  # Ajout, saisie correcte
    When I fill in "checkboxFieldDatagrid_label_addForm" with "AAA"
    And I fill in "checkboxFieldDatagrid_ref_addForm" with "aaa"
    And I fill in "checkboxFieldDatagrid_help_addForm" with "h1. Blabla"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Champs ordonnés suivant l'ordre de création, vérification des valeurs par défaut
    And the "checkboxFieldDatagrid" datagrid should contain a row:
      | label | ref | isVisible | enabled | defaultValue |
      | AAA   | aaa | Visible   | Activé  | Décoché      |
    When I click "Aide" in the row 3 of the "checkboxFieldDatagrid" datagrid
    Then I should see the popup "Aide"
    And I should see "Blabla" in the "#checkboxFieldDatagrid_help_popup .modal-body h1" element

  @javascript
  Scenario: Creation of a boolean field, incorrect input
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Champs booléens"
    Then I should see the "checkboxFieldDatagrid" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un champ booléen"
  # Ajout, identifiant vide
    When I click "Valider"
  # Then the field "checkboxFieldDatagrid_label_addForm" should have error: "Merci de renseigner ce champ."
    And the field "checkboxFieldDatagrid_ref_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout, identifiant avec caractères non autorisés
    When I fill in "checkboxFieldDatagrid_ref_addForm" with "bépo"
    And I click "Valider"
    Then the field "checkboxFieldDatagrid_ref_addForm" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Ajout, identifiant déjà utilisé
    When I fill in "checkboxFieldDatagrid_ref_addForm" with "c_n"
    And I click "Valider"
    Then the field "checkboxFieldDatagrid_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."

  @javascript
  Scenario: Edition of a boolean field, correct input
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Champs booléens"
    Then I should see the "checkboxFieldDatagrid" datagrid
  # Contenu initial
    And the row 1 of the "checkboxFieldDatagrid" datagrid should contain:
      | label     | ref | isVisible | enabled | defaultValue |
      | Champ booléen | c_b | Visible   | Activé  | Coché        |
  # Modification du libellé
    When I set "Champ booléen modifié" for column "label" of row 1 of the "checkboxFieldDatagrid" datagrid with a confirmation message
  # Modification de l'identifiant, saisie correcte
    When I set "c_b_modifie" for column "ref" of row 1 of the "checkboxFieldDatagrid" datagrid with a confirmation message
  # Modification de l'aide
    When I set "h1. Aide modifiée" for column "help" of row 1 of the "checkboxFieldDatagrid" datagrid with a confirmation message
  # Modification des autres attributs
    When I set "Masqué" for column "isVisible" of row 1 of the "checkboxFieldDatagrid" datagrid with a confirmation message
    When I set "Désactivé" for column "enabled" of row 1 of the "checkboxFieldDatagrid" datagrid with a confirmation message
    When I set "Décoché" for column "defaultValue" of row 1 of the "checkboxFieldDatagrid" datagrid with a confirmation message
  # Vérification que les modifications on bien été prises en compte au niveau du datagrid
    Then the row 1 of the "checkboxFieldDatagrid" datagrid should contain:
      | label                 | ref                   | isVisible | enabled   | defaultValue |
      | Champ booléen modifié | c_b_modifie | Masqué    | Désactivé | Décoché      |
    When I click "Aide" in the row 1 of the "checkboxFieldDatagrid" datagrid
    Then I should see the popup "Aide"
    And I should see a "#checkboxFieldDatagrid_help_popup .modal-body h1:contains('Aide modifiée')" element

  @javascript
  Scenario: Edition of a boolean field, incorrect input
  # Modification de l'identifiant, identifiant vide
    When I set "" for column "ref" of row 1 of the "checkboxFieldDatagrid" datagrid
    Then the following message is shown and closed: "Merci de renseigner ce champ."
  # Modification de l'identifiant, identifiant avec caractères non autorisés
    When I set "bépo" for column "ref" of row 1 of the "checkboxFieldDatagrid" datagrid
    Then the following message is shown and closed: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Modification de l'identifiant, identifiant déjà utilisé
    When I set "c_n" for column "ref" of row 1 of the "checkboxFieldDatagrid" datagrid
    Then the following message is shown and closed: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."

  @javascript
  Scenario: Deletion of a boolean field
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Champs booléens"
    Then I should see the "checkboxFieldDatagrid" datagrid
    And the "checkboxFieldDatagrid" datagrid should contain 2 row
  # Suppression sans obstacle
    When I click "Supprimer" in the row 1 of the "checkboxFieldDatagrid" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    And the "checkboxFieldDatagrid" datagrid should contain 1 row
