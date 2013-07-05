@dbOneOrganization
Feature: orgaAxis

  Background:
    Given I am logged in

  @javascript
  Scenario: orgaAxis1
  # TODO : modification sans effet
  # TODO : affichage libellé + identifiant
  # Accès à l'onglet "Axes"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Organisation"
    And I open tab "Axes"
  # Ajout d'un axe, identifiant vide
    And I click "Ajouter"
    Then I should see the popup "Ajout d'un axe"
    When I click "Valider"
    Then the field "addAxis_ref" should have error: "Merci de renseigner ce champ."
  # Ajout d'un axe, identifiant avec des caractères non autorisés
    When I fill in "addAxis_ref" with "bépo"
    When I click "Valider"
    Then the field "addAxis_ref" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Ajout d'un axe, saisie correcte
    When I fill in "addAxis_label" with "À modifier"
    And I fill in "addAxis_ref" with "a_modifier"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Ajout d'un axe, identifiant déjà utilisé
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un axe"
    When I fill in "addAxis_ref" with "a_modifier"
    And I click "Valider"
    Then the field "addAxis_ref" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
    When I click "Annuler"
  # Modification du libellé et de l'identifiant d'un axe
    When I click "a_modifier"
    Then I should see the popup "Édition d'un axe"
    When I fill in "editAxis_label" with "À supprimer !"
    And I fill in "editAxis_ref" with "a_supprimer"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Modification de l'identifiant d'un axe, identifiant vide
    When I click "a_supprimer"
    Then I should see the popup "Édition d'un axe"
    When I fill in "editAxis_ref" with ""
    And I click "Confirmer"
    Then the field "editAxis_ref" should have error: "Merci de renseigner ce champ."
  # Modification de l'identifiant d'un axe, identifiant avec des caractères non autorisés
    When I fill in "editAxis_ref" with "bépo_supprimer"
    And I click "Confirmer"
    Then the field "editAxis_ref" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Suppression d'un axe (sans axe parent)
    When I click "Annuler"
    And I click "a_supprimer"
    Then I should see the popup "Édition d'un axe"
    When I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."

  @javascript
  Scenario: orgaAxis2
  # Accès à l'onglet "Axes"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Organisation"
    And I open tab "Axes"
  # Ajout de l'axe "Test"
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un axe"
    When I fill in "addAxis_label" with "Axe test"
    When I fill in "addAxis_ref" with "axe_test"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Ajout d'un membre à l'axe test
    When I open tab "Membres"
    And I open collapse "Axe test"
    Then I should see the "listMembersaxe_test" datagrid
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un membre à l'axe « Axe test »"
    When I fill in "listMembersaxe_test_ref_addForm" with "membre_test"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Dans ce dernier cas le résultat sera visible après rechargement de la page."
  # Tentative de suppression de l'axe
    When I open tab "Axes"
    And I click "axe_test"
    Then I should see the popup "Édition d'un axe"
    When I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Pour pouvoir supprimer cet axe, merci de supprimer auparavant ses membres."
  # Suppression du membre
    When I open tab "Membres"
    And I open collapse "Axe test"
    Then I should see the "listMembersaxe_test" datagrid
    When I click "Supprimer" in the row 1 of the "listMembersaxe_test" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
  # Ajout d'une granularité
    When I open tab "Niveaux"
    Then I should see the "granularity" datagrid
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un niveau organisationnel"
    When I additionally select "Axe test" from "granularity_axes_addForm"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Dans ce dernier cas le résultat sera visible après rechargement de la page."
  # Tentative de suppression de l'axe
    When I open tab "Axes"
    And I click "axe_test"
    Then I should see the popup "Édition d'un axe"
    When I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Cet axe ne peut pas être supprimé, car il intervient dans la définition de (au moins) un niveau organisationnel."

  @javascript
  Scenario: orgaAxis3
  # Scénario qui permet de reconstituer la base "oneOrganizationWithAxes"
  # Accès à l'onglet "Axes"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Organisation"
    And I open tab "Axes"
  # Ajout de l'axe "Année"
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un axe"
    When I fill in "addAxis_label" with "Année"
    When I fill in "addAxis_ref" with "annee"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Ajout de l'axe "Site"
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un axe"
    When I fill in "addAxis_label" with "Site"
    When I fill in "addAxis_ref" with "site"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Ajout de l'axe "Pays"
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un axe"
    When I fill in "addAxis_label" with "Pays"
    When I fill in "addAxis_ref" with "pays"
    And I select "Site" from "addAxis_parent"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Ajout de l'axe "Activité"
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un axe"
    When I fill in "addAxis_label" with "Activité"
    When I fill in "addAxis_ref" with "activite"
    And I select "Site" from "addAxis_parent"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Ajout de l'axe "Société"
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un axe"
    When I fill in "addAxis_label" with "Société"
    When I fill in "addAxis_ref" with "societe"
    And I select "Site" from "addAxis_parent"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Déplacement de l'axe "Activité" en premier
    When I wait 1 seconds
    And I click "activite"
    Then I should see the popup "Édition d'un axe"
    When I check "Premier"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement de l'axe activité après l'axe Pays
    When I wait 3 seconds
    And I click "activite"
    Then I should see the popup "Édition d'un axe"
    When I check "Après"
    And I select "Pays" from "editAxis_selectAfter"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement de l'axe "Activité" en dernier
    When I wait 3 seconds
    And I click "activite"
    Then I should see the popup "Édition d'un axe"
    When I check "Dernier"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
    And I wait 3 seconds
  # Tentative de suppression de l'axe "Site"
    When I click "site"
    Then I should see the popup "Édition d'un axe"
    When I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Cet axe ne peut pas être supprimé, car il est hiérarchiquement relié à (au moins) un axe plus grossier."
  # Ajout de l'axe "Zone"
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un axe"
    When I fill in "addAxis_label" with "Zone"
    When I fill in "addAxis_ref" with "zone"
    And I select "Pays" from "addAxis_parent"
    And I click "Valider"
    When I wait 2 seconds
    Then the following message is shown and closed: "Ajout effectué."
  # Ajout de l'axe "Marque"
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un axe"
    When I fill in "addAxis_label" with "Marque"
    When I fill in "addAxis_ref" with "marque"
    And I select "Société" from "addAxis_parent"
    And I click "Valider"
    When I wait 2 seconds
    Then the following message is shown and closed: "Ajout effectué."
  # Ajout de l'axe "Catégorie"
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un axe"
    When I fill in "addAxis_label" with "Catégorie"
    When I fill in "addAxis_ref" with "categorie"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."