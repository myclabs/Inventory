@dbFull
Feature: Text field feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of a text field
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Champs texte"
    Then I should see the "textFieldDatagrid" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un champ texte"
  # Ajout, identifiant vide
    When I click "Valider"
  # Then the field "textFieldDatagrid_label_addForm" should have error: "Merci de renseigner ce champ."
    And the field "textFieldDatagrid_ref_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout, identifiant avec caractères non autorisés
    When I fill in "textFieldDatagrid_ref_addForm" with "bépo"
    And I click "Valider"
    Then the field "textFieldDatagrid_ref_addForm" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Ajout, identifiant déjà utilisé
    When I fill in "textFieldDatagrid_ref_addForm" with "champ_numerique"
    And I click "Valider"
    Then the field "textFieldDatagrid_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Ajout, saisie correcte
    When I fill in "textFieldDatagrid_label_addForm" with "AAA"
    And I fill in "textFieldDatagrid_ref_addForm" with "aaa"
    And I fill in "textFieldDatagrid_help_addForm" with "h1. Blabla"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Champs ordonnés suivant l'ordre de création
    And the row 3 of the "textFieldDatagrid" datagrid should contain:
      | label | ref | isVisible |
      | AAA   | aaa | Visible   |
    When I click "Aide" in the row 3 of the "textFieldDatagrid" datagrid
    Then I should see the popup "Aide"
    And I should see a "#textFieldDatagrid_help_popup .modal-body h1:contains('Blabla')" element

  @javascript
  Scenario: Edition of an AF text field
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Champs texte"
    Then I should see the "textFieldDatagrid" datagrid
    And the row 1 of the "textFieldDatagrid" datagrid should contain:
      | label             | ref               | isVisible | enabled | required    | type        |
      | Champ texte court | champ_texte_court | Visible   | Activé  | Obligatoire | Texte court |
  # Modification du libellé
    When I set "Champ texte modifié" for column "label" of row 1 of the "textFieldDatagrid" datagrid with a confirmation message
  # Modification de l'identifiant, identifiant vide
    When I set "" for column "ref" of row 1 of the "textFieldDatagrid" datagrid
    Then the following message is shown and closed: "Merci de renseigner ce champ."
  # Modification de l'identifiant, identifiant avec caractères non autorisés
    When I set "bépo" for column "ref" of row 1 of the "textFieldDatagrid" datagrid
    Then the following message is shown and closed: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Modification de l'identifiant, identifiant déjà utilisé
    When I set "champ_numerique" for column "ref" of row 1 of the "textFieldDatagrid" datagrid
    Then the following message is shown and closed: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Modification de l'identifiant, saisie correcte
    When I set "champ_texte_modifie" for column "ref" of row 1 of the "textFieldDatagrid" datagrid with a confirmation message
  # Modification de l'aide
    When I set "h1. Aide modifiée" for column "help" of row 1 of the "textFieldDatagrid" datagrid with a confirmation message
  # Modification des autres attributs
    When I set "Masqué" for column "isVisible" of row 1 of the "textFieldDatagrid" datagrid with a confirmation message
    When I set "Désactivé" for column "enabled" of row 1 of the "textFieldDatagrid" datagrid with a confirmation message
    When I set "Facultatif" for column "required" of row 1 of the "textFieldDatagrid" datagrid with a confirmation message
    When I set "Texte long" for column "type" of row 1 of the "textFieldDatagrid" datagrid with a confirmation message
  # Vérification que les modifications on bien été prises en compte au niveau du datagrid
    Then the row 1 of the "textFieldDatagrid" datagrid should contain:
      | label               | ref                 | isVisible | enabled   | required   | type       |
      | Champ texte modifié | champ_texte_modifie | Masqué    | Désactivé | Facultatif | Texte long |
    When I click "Aide" in the row 1 of the "textFieldDatagrid" datagrid
    Then I should see the popup "Aide"
    And I should see a "#textFieldDatagrid_help_popup .modal-body h1:contains('Aide modifiée')" element

  @javascript
  Scenario: Deletion of an AF text field
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Champs texte"
    Then I should see the "textFieldDatagrid" datagrid
    And the "textFieldDatagrid" datagrid should contain 2 row
  # Suppression sans obstacle
    When I click "Supprimer" in the row 1 of the "textFieldDatagrid" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    And the "textFieldDatagrid" datagrid should contain 1 row
