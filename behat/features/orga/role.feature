@dbFull
Feature: Organization role feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation/deletion of a role of organization administrator
  # Accès au datagrid
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Rôles"
    And I open collapse "Administrateurs d'organisation"
    Then I should see the "organizationACL1" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un administrateur d'organisation (création d'un nouvel utilisateur ou attribution du rôle à un utilisateur existant)"
  # Tentative d'ajout, email vide
    When I click "Valider"
    Then the field "organizationACL1_userEmail_addForm" should have error: "Merci de renseigner ce champ."
  # Tentative d'ajout, format email non respecté (pas d'erreur à ce jour…)
  # TODO : tester format email lors de l'ajout
  # Ajout, format email correct, utilisateur non existant
    When I fill in "organizationACL1_userEmail_addForm" with "emmanuel.risler.abo@gmail.com"
    And I click "Valider"
    Then the following message is shown and closed: "Un compte utilisateur a été créé pour cette adresse e-mail. Un e-mail contenant les identifiants de connexion a été envoyé à cette même adresse."
    And the row 3 of the "organizationACL1" datagrid should contain:
      | userEmail                     |
      | emmanuel.risler.abo@gmail.com |
  # Ajout, format email correct, le rôle existe déjà pour cet utilisateur
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un administrateur d'organisation (création d'un nouvel utilisateur ou attribution du rôle à un utilisateur existant)"
    When I fill in "organizationACL1_userEmail_addForm" with "emmanuel.risler.abo@gmail.com"
    And I click "Valider"
    Then the field "organizationACL1_userEmail_addForm" should have error: "Ce rôle est déjà attribué à l'utilisateur indiqué."
  # Suppression
    When I click "Annuler"
    And I click "Supprimer" in the row 3 of the "organizationACL1" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée. L'utilisateur en sera informé par e-mail."
  # Ajout, format email correct, utilisateur existant
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un administrateur d'organisation (création d'un nouvel utilisateur ou attribution du rôle à un utilisateur existant)"
    When I fill in "organizationACL1_userEmail_addForm" with "emmanuel.risler.abo@gmail.com"
    And I click "Valider"
    Then the following message is shown and closed: "L'adresse e-mail saisie correspond à un compte utilisateur existant, auquel le rôle indiqué a été attribué. L'utilisateur en sera informé par e-mail."
    And the row 3 of the "organizationACL1" datagrid should contain:
      | userEmail                     |
      | emmanuel.risler.abo@gmail.com |

  @javascript
  Scenario: Creation/deletion of a role of (global) cell administrator
  # Accès au datagrid et au popup
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Rôles"
    And I open collapse "Niveau organisationnel global"
    Then I should see the "granularityACL1" datagrid
    When I click "Ajouter"
    Then I should see the popup "Création d'un utilisateur ou attribution d'un rôle à un utilisateur existant"
  # Ajout, rôle vide
    When I fill in "granularityACL1_userEmail_addForm" with "emmanuel.risler.abo@gmail.com"
    And I click "Valider"
    Then the field "Rôle" should have error: "Merci de renseigner ce champ."
  # Ajout, e-mail vide
    When I fill in "granularityACL1_userEmail_addForm" with ""
    And I select "Administrateur" from "Rôle"
    And I click "Valider"
    Then the field "granularityACL1_userEmail_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout, saisie correcte, utilisateur non existant
    When I fill in "granularityACL1_userEmail_addForm" with "emmanuel.risler.abo@gmail.com"
    And I click "Valider"
    Then the following message is shown and closed: "Un compte utilisateur a été créé pour cette adresse e-mail. Un e-mail contenant les identifiants de connexion a été envoyé à cette même adresse."
    And the row 3 of the "granularityACL1" datagrid should contain:
      | userEmail                      | userRole       |
      | emmanuel.risler.abo@gmail.com  | Administrateur |
  # Tentative d'ajout, format email non respecté (pas d'erreur à ce jour…)
  # TODO : tester format email lors de l'ajout
  # Ajout, format email correct, le rôle existe déjà pour cet utilisateur
    When I click "Ajouter"
    Then I should see the popup "Création d'un utilisateur ou attribution d'un rôle à un utilisateur existant"
    When I fill in "granularityACL1_userEmail_addForm" with "emmanuel.risler.abo@gmail.com"
    And I select "Administrateur" from "Rôle"
    And I click "Valider"
    Then the field "Rôle" should have error: "Ce rôle est déjà attribué à l'utilisateur indiqué."
  # Suppression
    When I click "Annuler"
    And I click "Supprimer" in the row 3 of the "granularityACL1" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée. L'utilisateur en sera informé par e-mail."
  # Ajout, format email correct, utilisateur existant
    When I click "Ajouter"
    And I fill in "granularityACL1_userEmail_addForm" with "emmanuel.risler.abo@gmail.com"
    And I select "Contributeur" from "granularityACL1_userRole_addForm"
    And I click "Valider"
    Then the following message is shown and closed: "L'adresse e-mail saisie correspond à un compte utilisateur existant, auquel le rôle indiqué a été attribué. L'utilisateur en sera informé par e-mail."
    And the row 3 of the "granularityACL1" datagrid should contain:
      | userEmail                      | userRole     |
      | emmanuel.risler.abo@gmail.com  | Contributeur |

