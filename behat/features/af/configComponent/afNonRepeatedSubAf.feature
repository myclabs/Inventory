@dbFull
Feature: AF non repeated subAF feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of a non repeated subAF, correct input
  # Accès au datagrid
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Sous-formulaires non répétés"
    Then I should see the "subAfNotRepeatedDatagrid" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un sous-formulaire non répété"
  # Ajout, saisie correcte
    When I fill in "subAfNotRepeatedDatagrid_label_addForm" with "AAA"
    And I fill in "subAfNotRepeatedDatagrid_ref_addForm" with "aaa"
    And I select "Données générales" from "subAfNotRepeatedDatagrid_targetAF_addForm"
    And I fill in "subAfNotRepeatedDatagrid_help_addForm" with "h1. Blabla"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Sous-formulaires non répétés ordonnés suivant l'ordre de création
    And the row 2 of the "subAfNotRepeatedDatagrid" datagrid should contain:
      | label | ref | targetAF          | isVisible |
      | AAA   | aaa | Données générales | Visible   |
    When I click "Aide" in the row 2 of the "subAfNotRepeatedDatagrid" datagrid
    Then I should see the popup "Aide"
    And I should see a "#subAfNotRepeatedDatagrid_help_popup .modal-body h1:contains('Blabla')" element

  @javascript
  Scenario: Creation of a non repeated subAF, incorrect input
  # Accès au datagrid
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Sous-formulaires non répétés"
    Then I should see the "subAfNotRepeatedDatagrid" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un sous-formulaire non répété"
  # TODO :tester en l'absence complète de formulaire
  # Ajout, identifiant vide
    When I click "Valider"
  # Then the field "subAfNotRepeatedDatagrid_label_addForm" should have error: "Merci de renseigner ce champ."
    And the field "subAfNotRepeatedDatagrid_ref_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout, identifiant avec caractères non autorisés
    When I fill in "subAfNotRepeatedDatagrid_ref_addForm" with "bépo"
    And I click "Valider"
    Then the field "subAfNotRepeatedDatagrid_ref_addForm" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Ajout, identifiant déjà utilisé
    When I fill in "subAfNotRepeatedDatagrid_ref_addForm" with "c_n"
    And I click "Valider"
    Then the field "subAfNotRepeatedDatagrid_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."

  @javascript
  Scenario: Edition of a non repeated subAF, correct input
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Sous-formulaires non répétés"
    Then I should see the "subAfNotRepeatedDatagrid" datagrid
  # Modification du libellé
    When I set "Sous-formulaire non répété modifié" for column "label" of row 1 of the "subAfNotRepeatedDatagrid" datagrid with a confirmation message
  # Modification de l'identifiant, saisie correcte
    When I set "sous_formulaire_non_repete_modifie" for column "ref" of row 1 of the "subAfNotRepeatedDatagrid" datagrid with a confirmation message
  # Modification du formulaire associé
    When I set "Combustion de combustible, mesuré en unité de masse" for column "targetAF" of row 1 of the "subAfNotRepeatedDatagrid" datagrid with a confirmation message
  # Modification de l'aide
    When I set "h1. Aide modifiée" for column "help" of row 1 of the "subAfNotRepeatedDatagrid" datagrid with a confirmation message
  # Modification de la visibilité initiale
    When I set "Masqué" for column "isVisible" of row 1 of the "subAfNotRepeatedDatagrid" datagrid with a confirmation message
  # Vérification que les modifications on bien été prises en compte au niveau du datagrid
    Then the row 1 of the "subAfNotRepeatedDatagrid" datagrid should contain:
      | label                              | ref                                | targetAF                                            | isVisible |
      | Sous-formulaire non répété modifié | sous_formulaire_non_repete_modifie | Combustion de combustible, mesuré en unité de masse | Masqué    |
    When I click "Aide" in the row 1 of the "subAfNotRepeatedDatagrid" datagrid
    Then I should see the popup "Aide"
    And I should see a "#subAfNotRepeatedDatagrid_help_popup .modal-body h1:contains('Blabla')" element

  @javascript
  Scenario: Edition of a non repeated subAF, incorrect input
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Sous-formulaires non répétés"
    Then I should see the "subAfNotRepeatedDatagrid" datagrid
  # Modification de l'identifiant, identifiant vide
    When I set "" for column "ref" of row 1 of the "subAfNotRepeatedDatagrid" datagrid
    Then the following message is shown and closed: "Merci de renseigner ce champ."
  # Modification de l'identifiant, identifiant avec caractères non autorisés
    When I set "bépo" for column "ref" of row 1 of the "subAfNotRepeatedDatagrid" datagrid
    Then the following message is shown and closed: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Modification de l'identifiant, identifiant déjà utilisé
    When I set "c_n" for column "ref" of row 1 of the "subAfNotRepeatedDatagrid" datagrid
    Then the following message is shown and closed: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."

  @javascript
  Scenario: Deletion of a non repeated subAF
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Sous-formulaires non répétés"
    Then I should see the "subAfNotRepeatedDatagrid" datagrid
    And the "subAfNotRepeatedDatagrid" datagrid should contain 1 row
  # Suppression sans obstacle
    When I click "Supprimer" in the row 1 of the "subAfNotRepeatedDatagrid" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    And the "subAfNotRepeatedDatagrid" datagrid should contain 0 row