@dbFull
Feature: AF group feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of an AF group, correct input
  # Accès au datagrid
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Groupes"
    Then I should see the "groupDatagrid" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un groupe"
  # Ajout, saisie correcte
    When I fill in "groupDatagrid_label_addForm" with "AAA"
    And I fill in "groupDatagrid_ref_addForm" with "aaa"
    And I fill in "groupDatagrid_help_addForm" with "h1. Blabla"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Groupes ordonnés suivant l'ordre de création
    And the row 5 of the "groupDatagrid" datagrid should contain:
      | label | ref | isVisible |
      | AAA   | aaa | Visible   |
    When I click "Aide" in the row 5 of the "groupDatagrid" datagrid
    Then I should see the popup "Aide"
    And I should see a "#groupDatagrid_help_popup .modal-body h1:contains('Blabla')" element


  @javascript
  Scenario: Creation of an AF group, incorrect input
  # Accès au datagrid
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Groupes"
    Then I should see the "groupDatagrid" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un groupe"
  # Ajout, identifiant vide
    When I click "Valider"
  # Then the field "groupDatagrid_label_addForm" should have error: "Merci de renseigner ce champ."
    And the field "groupDatagrid_ref_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout, identifiant avec caractères non autorisés
    When I fill in "groupDatagrid_ref_addForm" with "bépo"
    And I click "Valider"
    Then the field "groupDatagrid_ref_addForm" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Ajout, identifiant déjà utilisé
    When I fill in "groupDatagrid_ref_addForm" with "c_n"
    And I click "Valider"
    Then the field "groupDatagrid_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."

  @javascript
  Scenario: Edition of an AF group, correct input
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Groupes"
    Then I should see the "groupDatagrid" datagrid
  # Modification du libellé
    When I set "Groupe modifié" for column "label" of row 1 of the "groupDatagrid" datagrid with a confirmation message
  # Modification de l'identifiant, saisie correcte
    When I set "groupe_modifie" for column "ref" of row 1 of the "groupDatagrid" datagrid with a confirmation message
  # Modification de l'aide
    When I set "h1. Aide modifiée" for column "help" of row 1 of the "groupDatagrid" datagrid with a confirmation message
  # Modification de la visibilité initiale
    When I set "Masqué" for column "isVisible" of row 1 of the "groupDatagrid" datagrid with a confirmation message
  # Vérification que les modifications on bien été prises en compte au niveau du datagrid
    Then the row 1 of the "groupDatagrid" datagrid should contain:
      | label          | ref            | isVisible |
      | Groupe modifié | groupe_modifie | Masqué    |
    When I click "Aide" in the row 1 of the "groupDatagrid" datagrid
    Then I should see the popup "Aide"
    And I should see a "#groupDatagrid_help_popup .modal-body h1:contains('Aide modifiée')" element

  @javascript
  Scenario: Edition of an AF group, incorrect input
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Groupes"
    Then I should see the "groupDatagrid" datagrid
  # Modification de l'identifiant, identifiant vide
    When I set "" for column "ref" of row 1 of the "groupDatagrid" datagrid
    Then the following message is shown and closed: "Merci de renseigner ce champ."
  # Modification de l'identifiant, identifiant avec caractères non autorisés
    When I set "bépo" for column "ref" of row 1 of the "groupDatagrid" datagrid
    Then the following message is shown and closed: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Modification de l'identifiant, identifiant déjà utilisé
    When I set "c_n" for column "ref" of row 1 of the "groupDatagrid" datagrid
    Then the following message is shown and closed: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."

  @javascript
  Scenario: Deletion of an AF group
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Groupes"
    Then I should see the "groupDatagrid" datagrid
  # Groupe contenant un champ
    And the row 2 of the "groupDatagrid" datagrid should contain:
      | label                     |
      | Groupe contenant un champ |
    When I click "Supprimer" in the row 2 of the "groupDatagrid" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Ce groupe ne peut pas être supprimé, car il contient un ou plusieurs composants."
  # Groupe contenant un sous-groupe
    And the row 3 of the "groupDatagrid" datagrid should contain:
      | label                           |
      | Groupe contenant un sous-groupe |
    When I click "Supprimer" in the row 3 of the "groupDatagrid" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Ce groupe ne peut pas être supprimé, car il contient un ou plusieurs composants."
  # Suppression sans obstacle
    And the row 1 of the "groupDatagrid" datagrid should contain:
      | label       |
      | Groupe vide |
    When I click "Supprimer" in the row 1 of the "groupDatagrid" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."