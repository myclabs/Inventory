@dbFull
Feature: Organization role feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation/deletion of a role of workspace administrator, correct input
  # Accès au datagrid
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Rôles"
    And I open collapse "Administrateurs de workspace"
    Then I should see the "organizationACL1" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un administrateur de workspace (création d'un nouvel utilisateur ou attribution du rôle à un utilisateur existant)"
  # Ajout, format email correct, utilisateur non existant
    When I fill in "organizationACL1_userEmail_addForm" with "emmanuel.risler.abo@gmail.com"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
    And the row 3 of the "organizationACL1" datagrid should contain:
      | userEmail                     |
      | emmanuel.risler.abo@gmail.com |
  # Suppression
    When I click "Supprimer" in the row 3 of the "organizationACL1" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
  # Ajout, format email correct, utilisateur existant
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un administrateur de workspace (création d'un nouvel utilisateur ou attribution du rôle à un utilisateur existant)"
    When I fill in "organizationACL1_userEmail_addForm" with "emmanuel.risler.abo@gmail.com"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
    And the row 3 of the "organizationACL1" datagrid should contain:
      | userEmail                     |
      | emmanuel.risler.abo@gmail.com |

  @javascript
  Scenario: Creation/deletion of a role of workspace administrator, incorrect input
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Rôles"
    And I open collapse "Administrateurs de workspace"
    And I click "Ajouter"
  # Tentative d'ajout, email vide
    And I click "Valider"
    Then the field "organizationACL1_userEmail_addForm" should have error: "Merci de renseigner ce champ."
  # Tentative d'ajout, format email non respecté
    When I fill in "organizationACL1_userEmail_addForm" with "auie"
    And I click "Valider"
    Then the field "organizationACL1_userEmail_addForm" should have error: "Merci de saisir une adresse e-mail valide."
  # Tentative d'ajout, format email non respecté (2)
    When I fill in "organizationACL1_userEmail_addForm" with "auie@auie"
    And I click "Valider"
    Then the field "organizationACL1_userEmail_addForm" should have error: "Merci de saisir une adresse e-mail valide."
  # Tentative d'ajout, format email correct, le rôle existe déjà pour cet utilisateur
    When I fill in "organizationACL1_userEmail_addForm" with "administrateur.workspace@toto.com"
    And I click "Valider"
    Then the field "organizationACL1_userEmail_addForm" should have error: "Ce rôle est déjà attribué à l'utilisateur indiqué."
  # Tentative d'ajout, format email correct, extension incorrecte
    When I fill in "organizationACL1_userEmail_addForm" with "auie@auie.auie"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."

  @javascript
  Scenario: Creation/deletion of a role of (global) cell administrator, correct input
  # Accès au datagrid et au popup
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Rôles"
    And I open collapse "Niveau organisationnel global"
    Then I should see the "granularityACL1" datagrid
    When I click "Ajouter"
    Then I should see the popup "Création d'un utilisateur ou attribution d'un rôle à un utilisateur existant"
  # Ajout, saisie correcte, utilisateur non existant
    When I fill in "granularityACL1_userEmail_addForm" with "emmanuel.risler.abo@gmail.com"
    And I select "Administrateur" from "Rôle"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
    And the "granularityACL1" datagrid should contain a row:
      | userEmail                      | userRole       |
      | emmanuel.risler.abo@gmail.com  | Administrateur |
  # Suppression
    When I click "Supprimer" in the row 3 of the "granularityACL1" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
  # Ajout, format email correct, utilisateur existant
    When I click "Ajouter"
    And I fill in "granularityACL1_userEmail_addForm" with "emmanuel.risler.abo@gmail.com"
    And I select "Contributeur" from "granularityACL1_userRole_addForm"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
    And the "granularityACL1" datagrid should contain a row:
      | userEmail                      | userRole     |
      | emmanuel.risler.abo@gmail.com  | Contributeur |

  @javascript
  Scenario: Creation/deletion of a role of site contributor, incorrect input
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Rôles"
    And I open collapse "Site — par utilisateur"
    And I click "Ajouter"
  # Tentative de validation, adresse email vide, rôle vide
    And I click "Valider"
    Then the field "granularityUserACL3_userEmail_addForm" should have error: "Merci de renseigner ce champ."
    And the field "granularityUserACL3_userRole_addForm" should have error: "Merci de renseigner ce champ."
  # Tentative d'ajout, format email non respecté
    When I fill in "granularityUserACL3_userRole_addForm" with "Contributeur"
    When I fill in "granularityUserACL3_userEmail_addForm" with "auie"
    And I click "Valider"
    Then the field "granularityUserACL3_userEmail_addForm" should have error: "Merci de saisir une adresse e-mail valide."
  # Tentative d'ajout, format email non respecté (2)
    When I fill in "granularityUserACL3_userEmail_addForm" with "auie@auie"
    And I click "Valider"
    Then the field "granularityUserACL3_userEmail_addForm" should have error: "Merci de saisir une adresse e-mail valide."
  # Tentative d'ajout, format email correct, le rôle existe déjà pour cet utilisateur
    When I fill in "granularityUserACL3_userEmail_addForm" with "contributeur.site@toto.com"
    And I click "Valider"
    Then the field "granularityUserACL3_userEmail_addForm" should have error: "Ce rôle est déjà attribué à l'utilisateur indiqué."
  # Tentative d'ajout, format email correct, extension incorrecte
    When I fill in "granularityUserACL3_userEmail_addForm" with "auie@auie.auie"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."