@dbFull
Feature: AF repeated subAF feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of a repeated subAF, correct input
  # Accès au datagrid
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Sous-formulaires répétés"
    Then I should see the "subAfRepeatedDatagrid" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un sous-formulaire répété"
  # Ajout, saisie correcte
    When I fill in "subAfRepeatedDatagrid_label_addForm" with "AAA"
    And I fill in "subAfRepeatedDatagrid_ref_addForm" with "aaa"
    And I select "Combustion de combustible, mesuré en unité de masse" from "subAfRepeatedDatagrid_targetAF_addForm"
    And I fill in "subAfRepeatedDatagrid_help_addForm" with "h1. Blabla"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Sous-formulaires non répétés ordonnés suivant l'ordre de création
    And the row 2 of the "subAfRepeatedDatagrid" datagrid should contain:
      | label | ref | targetAF                                            | isVisible | repetition | hasFreeLabel |
      | AAA   | aaa | Combustion de combustible, mesuré en unité de masse | Visible   | Zéro       | Non          |
    When I click "Aide" in the row 2 of the "subAfRepeatedDatagrid" datagrid
    Then I should see the popup "Aide"
    And I should see a "#subAfRepeatedDatagrid_help_popup .modal-body h1:contains('Blabla')" element

  @javascript
  Scenario: Creation of a repeated subAF, incorrect input
  # Accès au datagrid
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Sous-formulaires répétés"
    Then I should see the "subAfRepeatedDatagrid" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un sous-formulaire répété"
  # TODO :tester en l'absence complète de formulaire
  # Ajout, identifiant vide
    When I click "Valider"
  # Then the field "subAfRepeatedDatagrid_label_addForm" should have error: "Merci de renseigner ce champ."
    And the field "subAfRepeatedDatagrid_ref_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout, identifiant avec caractères non autorisés
    When I fill in "subAfRepeatedDatagrid_ref_addForm" with "bépo"
    And I click "Valider"
    Then the field "subAfRepeatedDatagrid_ref_addForm" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Ajout, identifiant déjà utilisé
    When I fill in "subAfRepeatedDatagrid_ref_addForm" with "c_n"
    And I click "Valider"
    Then the field "subAfRepeatedDatagrid_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."

  @javascript
  Scenario: Edition of a repeated subAF, correct input
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Sous-formulaires répétés"
    Then I should see the "subAfRepeatedDatagrid" datagrid
  # Modification du libellé
    When I set "Sous-formulaire répété modifié" for column "label" of row 1 of the "subAfRepeatedDatagrid" datagrid with a confirmation message
  # Modification de l'identifiant, saisie correcte
    When I set "sous_formulaire_repete_modifie" for column "ref" of row 1 of the "subAfRepeatedDatagrid" datagrid with a confirmation message
  # Modification du formulaire associé
    When I set "Données générales" for column "targetAF" of row 1 of the "subAfRepeatedDatagrid" datagrid with a confirmation message
  # Modification de l'aide
    When I set "h1. Aide modifiée" for column "help" of row 1 of the "subAfRepeatedDatagrid" datagrid with a confirmation message
  # Modification de la visibilité initiale
    When I set "Masqué" for column "isVisible" of row 1 of the "subAfRepeatedDatagrid" datagrid with a confirmation message
  # Vérification que les modifications on bien été prises en compte au niveau du datagrid
    Then the row 1 of the "subAfRepeatedDatagrid" datagrid should contain:
      | label                          | ref                            | targetAF          | isVisible |
      | Sous-formulaire répété modifié | sous_formulaire_repete_modifie | Données générales | Masqué    |
    When I click "Aide" in the row 1 of the "subAfRepeatedDatagrid" datagrid
    Then I should see the popup "Aide"
    And I should see a "#subAfRepeatedDatagrid_help_popup .modal-body h1:contains('Aide modifiée')" element

  @javascript
  Scenario: Edition of a repeated subAF, incorrect input
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Sous-formulaires répétés"
    Then I should see the "subAfRepeatedDatagrid" datagrid
  # Modification de l'identifiant, identifiant vide
    When I set "" for column "ref" of row 1 of the "subAfRepeatedDatagrid" datagrid
    Then the following message is shown and closed: "Merci de renseigner ce champ."
  # Modification de l'identifiant, identifiant avec caractères non autorisés
    When I set "bépo" for column "ref" of row 1 of the "subAfRepeatedDatagrid" datagrid
    Then the following message is shown and closed: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Modification de l'identifiant, identifiant déjà utilisé
    When I set "c_n" for column "ref" of row 1 of the "subAfRepeatedDatagrid" datagrid
    Then the following message is shown and closed: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."

  @javascript
  Scenario: Deletion of a repeated subAF
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Sous-formulaires répétés"
    Then I should see the "subAfRepeatedDatagrid" datagrid
    And the "subAfRepeatedDatagrid" datagrid should contain 1 row
  # Suppression sans obstacle
    When I click "Supprimer" in the row 1 of the "subAfRepeatedDatagrid" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    And the "subAfRepeatedDatagrid" datagrid should contain 0 row