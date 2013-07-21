@dbFull
Feature: AF group feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of an AF group
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
    When I fill in "groupDatagrid_ref_addForm" with "champ_numerique"
    And I click "Valider"
    Then the field "groupDatagrid_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Ajout, saisie correcte
    When I fill in "groupDatagrid_label_addForm" with "AAA"
    And I fill in "groupDatagrid_ref_addForm" with "aaa"
    And I fill in "groupDatagrid_help_addForm" with "h1. Blabla"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
    # TODO : ordonner les lignes suivant l'ordre alphabétique des identifiants ?
    And the row 2 of the "groupDatagrid" datagrid should contain:
      | label | ref | isVisible | foldaway  |
      | AAA   | aaa | Visible   | Repliable |
    When I click "Aide" in the row 2 of the "groupDatagrid" datagrid
    Then I should see the popup "Aide"
    And I should see a "#groupDatagrid_help_popup .modal-body h1:contains('Blabla')" element

