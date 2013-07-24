@dbFull
Feature: AF multiple selection field option feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of a multiple selection field option scenario
  # Accès au popup des options
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Champs de sélection multiple"
    Then I should see the "selectMultiFieldDatagrid" datagrid
    When I click "Options" in the row 1 of the "selectMultiFieldDatagrid" datagrid
    Then I should see the popup "Options"
    And I should see the "optionDatagrid" datagrid
  # Popup d'ajout
    When I click element "#selectMultiFieldDatagrid_options_popup .btn:contains('Ajouter')"
    Then I should see the popup "Ajout d'une option"
  # Ajout, identifiant vide
    When I click "Valider"
  # Then the field "optionDatagrid_label_addForm" should have error: "Merci de renseigner ce champ."
    And the field "optionDatagrid_ref_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout, identifiant avec caractères non autorisés
    When I fill in "optionDatagrid_ref_addForm" with "bépo"
    And I click "Valider"
    Then the field "optionDatagrid_ref_addForm" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Ajout, identifiant déjà utilisé
    When I fill in "optionDatagrid_ref_addForm" with "option_1"
    And I click "Valider"
    Then the field "optionDatagrid_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Ajout, saisie correcte
    When I fill in "optionDatagrid_label_addForm" with "AAA"
    And I fill in "optionDatagrid_ref_addForm" with "aaa"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Option ajoutée apparaît en dernier
    And the row 3 of the "optionDatagrid" datagrid should contain:
      | label | ref | isVisible | enabled | defaultValue     |
      | AAA   | aaa | Visible   | Activé  | Non sélectionnée |

  @javascript
  Scenario: Edition of a multiple selection field option scenario
  # Accès au popup des options, contenu première ligne
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Champs de sélection multiple"
    Then I should see the "selectMultiFieldDatagrid" datagrid
    When I click "Options" in the row 1 of the "selectMultiFieldDatagrid" datagrid
    Then I should see the popup "Options"
    And I should see the "optionDatagrid" datagrid
    And the row 1 of the "optionDatagrid" datagrid should contain:
      | label    | ref      | isVisible | enabled | defaultValue     |
      | Option 1 | option_1 | Visible   | Activée | Non sélectionnée |
  # Modification du libellé
    When I set "Option 1 modifiée" for column "label" of row 1 of the "optionDatagrid" datagrid
    And I click element "#selectMultiFieldDatagrid_options_popup .close:contains('×')"
    Then the following message is shown and closed: "Modification effectuée."
  # Modification de l'identifiant, identifiant vide
    When I click "Options" in the row 1 of the "selectMultiFieldDatagrid" datagrid
    And I set "" for column "ref" of row 1 of the "optionDatagrid" datagrid
    And I click element "#selectMultiFieldDatagrid_options_popup .close:contains('×')"
    Then the following message is shown and closed: "Merci de renseigner ce champ."
  # Modification de l'identifiant, identifiant avec caractères non autorisés
    When I click "Options" in the row 1 of the "selectMultiFieldDatagrid" datagrid
    And I set "bépo" for column "ref" of row 1 of the "optionDatagrid" datagrid
    And I click element "#selectMultiFieldDatagrid_options_popup .close:contains('×')"
    Then the following message is shown and closed: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Modification de l'identifiant, identifiant déjà utilisé
    When I click "Options" in the row 1 of the "selectMultiFieldDatagrid" datagrid
    And I set "option_2" for column "ref" of row 1 of the "optionDatagrid" datagrid
    And I click element "#selectMultiFieldDatagrid_options_popup .close:contains('×')"
    Then the following message is shown and closed: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Modification de l'identifiant, saisie correcte
    When I click "Options" in the row 1 of the "selectMultiFieldDatagrid" datagrid
    And I set "option_1_modifiee" for column "ref" of row 1 of the "optionDatagrid" datagrid
    And I click element "#selectMultiFieldDatagrid_options_popup .close:contains('×')"
    Then the following message is shown and closed: "Modification effectuée."
  # Modification visibilité
    When I click "Options" in the row 1 of the "selectMultiFieldDatagrid" datagrid
    And I set "Masquée" for column "isVisible" of row 1 of the "optionDatagrid" datagrid
    And I click element "#selectMultiFieldDatagrid_options_popup .close:contains('×')"
    Then the following message is shown and closed: "Modification effectuée."
  # Modification activation
    When I click "Options" in the row 1 of the "selectMultiFieldDatagrid" datagrid
    And I set "Désactivée" for column "enabled" of row 1 of the "optionDatagrid" datagrid
    And I click element "#selectMultiFieldDatagrid_options_popup .close:contains('×')"
    Then the following message is shown and closed: "Modification effectuée."
  # Modification activation
    When I click "Options" in the row 1 of the "selectMultiFieldDatagrid" datagrid
    And I set "Sélectionnée" for column "defaultValue" of row 1 of the "optionDatagrid" datagrid
    And I click element "#selectMultiFieldDatagrid_options_popup .close:contains('×')"
    Then the following message is shown and closed: "Modification effectuée."
  # Vérification modifications effectuées
    When I click "Options" in the row 1 of the "selectMultiFieldDatagrid" datagrid
    Then the row 1 of the "optionDatagrid" datagrid should contain:
      | label             | ref               | isVisible | enabled    | defaultValue |
      | Option 1 modifiée | option_1_modifiee | Masquée   | Désactivée | Sélectionnée |

  @javascript
  Scenario: Deletion of a multiple selection field option scenario
  # Accès au datagrid des champs de sélection multiple
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Champs de sélection multiple"
    Then I should see the "selectMultiFieldDatagrid" datagrid
  # Accès au popup des options
    When I click "Options" in the row 1 of the "selectMultiFieldDatagrid" datagrid
    Then I should see the popup "Options"
    And I should see the "optionDatagrid" datagrid
  # Suppression sans obstacle
    When I click "Supprimer" in the row 2 of the "optionDatagrid" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
  # On doit rouvrir le popup des options, car les deux popups sont fermés d'un coup
    When I click "Options" in the row 1 of the "selectMultiFieldDatagrid" datagrid
    Then the "optionDatagrid" datagrid should contain 1 row
