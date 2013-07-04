@dbOneOrganizationWithAxes
Feature: orgaRole

  Background:
    Given I am logged in

  @javascript
  Scenario: orgaRole1
  # Édition des administrateur d'organisation
  # TODO : tester format email lors de l'ajout
  # Accès à l'onglet "Rôles"
    Given I am on "orga/cell/details/idCell/1"
    And I open tab "Rôles"
    And I open collapse "Administrateurs d'organisation"
    Then I should see the "organizationACL1" datagrid
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un administrateur d'organisation (création d'un nouvel utilisateur ou attribution du rôle à un utilisateur existant)"
  # Tentative d'ajout, email vide
    When I click "Valider"
    Then the field "organizationACL1_userEmail_addForm" should have error: "Merci de renseigner ce champ."
  # Tentative d'ajout, format email non respecté (pas d'erreur à ce jour…)
  # Ajout, format email correct, utilisateur non existant
    When I fill in "organizationACL1_userEmail_addForm" with "emmanuel.risler.pro@gmail.com"
    And I click "Valider"
    Then the following message is shown and closed: "Un compte utilisateur a été créé pour cette adresse e-mail. Un e-mail contenant les identifiants de connexion a été envoyé à cette même adresse."
    And the row 2 of the "organizationACL1" datagrid should contain:
      | userEmail          |
      | emmanuel.risler.pro@gmail.com |
  # Ajout, format email correct, le rôle existe déjà pour cet utilisateur
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un administrateur d'organisation (création d'un nouvel utilisateur ou attribution du rôle à un utilisateur existant)"
    When I fill in "organizationACL1_userEmail_addForm" with "emmanuel.risler.pro@gmail.com"
    And I click "Valider"
    Then the field "organizationACL1_userEmail_addForm" should have error: "Ce rôle est déjà attribué à l'utilisateur indiqué."
  # Suppression
    When I click element "#organizationACL1_addPanel .btn:contains('Annuler')"
    And I click "Supprimer" in the row 2 of the "organizationACL1" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click element "#organizationACL1_deletePanel .btn:contains('Confirmer')"
    Then the following message is shown and closed: "Suppression effectuée. L'utilisateur en sera informé par e-mail."
  # Ajout, format email correct, utilisateur existant
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un administrateur d'organisation (création d'un nouvel utilisateur ou attribution du rôle à un utilisateur existant)"
    When I fill in "organizationACL1_userEmail_addForm" with "emmanuel.risler.pro@gmail.com"
    And I click "Valider"
    Then the following message is shown and closed: "L'adresse e-mail saisie correspond à un compte utilisateur existant, auquel le rôle indiqué a été attribué. L'utilisateur en sera informé par e-mail."
    And the row 2 of the "organizationACL1" datagrid should contain:
      | userEmail          |
      | emmanuel.risler.pro@gmail.com |

  @javascript
  Scenario: orgaRole2
  # Édition des administrateur de cellule (cellule globale)
  # TODO : tester format email lors de l'ajout
  # Accès au datagrid et au popup
    Given I am on "orga/cell/details/idCell/1"
    And I open tab "Rôles"
    And I open collapse "Niveau organisationnel global"
    Then I should see the "granularityACL1" datagrid
    When I click element "#collapse_granularityACL1_wrapper .btn:contains('Ajouter')"
    Then I should see the popup "Création d'un utilisateur ou attribution d'un rôle à un utilisateur existant"
  # Ajout, rôle vide
    When I fill in "granularityACL1_userEmail_addForm" with "emmanuel.risler.pro@gmail.com"
    And I click element "#granularityACL1_addPanel .btn:contains('Valider')"
    Then the field "granularityACL1_userRole_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout, e-mail vide
    When I fill in "granularityACL1_userEmail_addForm" with ""
    And I select "Administrateur" from "granularityACL1_userRole_addForm"
    And I click element "#granularityACL1_addPanel .btn:contains('Valider')"
    Then the field "granularityACL1_userEmail_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout, saisie correcte, utilisateur non existant
    When I fill in "granularityACL1_userEmail_addForm" with "emmanuel.risler.pro@gmail.com"
    And I click element "#granularityACL1_addPanel .btn:contains('Valider')"
    Then the following message is shown and closed: "Un compte utilisateur a été créé pour cette adresse e-mail. Un e-mail contenant les identifiants de connexion a été envoyé à cette même adresse."
    And the row 2 of the "granularityACL1" datagrid should contain:
      | userEmail                      | userRole |
      | emmanuel.risler.pro@gmail.com  | Administrateur |
  # Ajout, format email correct, le rôle existe déjà pour cet utilisateur
    When I click element "#collapse_granularityACL1_wrapper .btn:contains('Ajouter')"
    Then I should see the popup "Création d'un utilisateur ou attribution d'un rôle à un utilisateur existant"
    When I fill in "granularityACL1_userEmail_addForm" with "emmanuel.risler.pro@gmail.com"
    And I select "Administrateur" from "granularityACL1_userRole_addForm"
    And I click element "#granularityACL1_addPanel .btn:contains('Valider')"
    Then the field "granularityACL1_userRole_addForm" should have error: "Ce rôle est déjà attribué à l'utilisateur indiqué."
  # Suppression
    When I click element "#granularityACL1_addPanel .btn:contains('Annuler')"
    And I click "Supprimer" in the row 2 of the "granularityACL1" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click element "#granularityACL1_deletePanel .btn:contains('Confirmer')"
    Then the following message is shown and closed: "Suppression effectuée. L'utilisateur en sera informé par e-mail."
  # Ajout, format email correct, utilisateur existant
    When I click element "#collapse_granularityACL1_wrapper .btn:contains('Ajouter')"
    And I fill in "granularityACL1_userEmail_addForm" with "emmanuel.risler.pro@gmail.com"
    And I select "Contributeur" from "granularityACL1_userRole_addForm"
    And I click element "#granularityACL1_addPanel .btn:contains('Valider')"
    Then the following message is shown and closed: "L'adresse e-mail saisie correspond à un compte utilisateur existant, auquel le rôle indiqué a été attribué. L'utilisateur en sera informé par e-mail."
    And the row 2 of the "granularityACL1" datagrid should contain:
      | userEmail                      | userRole |
      | emmanuel.risler.pro@gmail.com  | Contributeur |