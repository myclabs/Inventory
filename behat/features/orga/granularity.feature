@dbFull
Feature: Organization granularity feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of a granularity, correct input
  # Accès à l'onglet "Niveaux"
    Given I am on "orga/organization/edit/idOrganization/1"
    And I wait for the page to finish loading
    And I open tab "Niveaux"
    Then I should see the "granularity1" datagrid
  # Ajout d'une granularité non déjà existante
  # Toutes les options cochées
    And I click "Ajouter"
    Then I should see the popup "Ajout d'un niveau organisationnel"
    When I additionally select "Zone" from "granularity1_axes_addForm"
    And I select "Oui" in radio "Pertinence"
    And I select "Oui" in radio "Analyses"
    And I select "Oui" in radio "Rôles"
    And I click "Valider"
  # Nécessité d'une attente longue du fait de la présence du masque de chargement, pour que le scénario passe en local (affichage du message de confirmation).
  # Test effectué en local le 06/09/2013 : 1'05 pour le traitement. Ci-dessous temps d'attente de 1'40 pour avoir de la marge.
#    And I wait 100 seconds
    And I wait 10 seconds
    Then the following message is shown and closed: "Ajout effectué."
    And the row 2 of the "granularity1" datagrid should contain:
      | axes | relevance | acl | reports  |
      | Zone | Oui       | Oui | Oui      |

  @javascript
  Scenario: Creation of a granularity, incorrect input
  # Accès à l'onglet "Niveaux"
    Given I am on "orga/organization/edit/idOrganization/1"
    And I wait for the page to finish loading
    And I open tab "Niveaux"
    Then I should see the "granularity1" datagrid
  # Ajout d'une granularité déjà existante
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un niveau organisationnel"
    When I additionally select "Année" from "granularity1_axes_addForm"
    And I click "Valider"
    Then the field "granularity1_axes_addForm" should have error: "Il existe déjà un niveau organisationnel correspondant à cette combinaison d'axes."
    When I click "Annuler"
  # Ajout d'une granularité déjà existante, bis
    And I click "Ajouter"
    Then I should see the popup "Ajout d'un niveau organisationnel"
    When I additionally select "Année" from "granularity1_axes_addForm"
    And I additionally select "Site" from "granularity1_axes_addForm"
    And I additionally select "Catégorie" from "granularity1_axes_addForm"
    And I click "Valider"
    Then the field "granularity1_axes_addForm" should have error: "Il existe déjà un niveau organisationnel correspondant à cette combinaison d'axes."
    When I click "Annuler"
  # Ajout, axes non deux à deux transverses
    And I click "Ajouter"
    Then I should see the popup "Ajout d'un niveau organisationnel"
    When I additionally select "Zone" from "granularity1_axes_addForm"
    And I additionally select "Site" from "granularity1_axes_addForm"
    And I click "Valider"
    Then the field "granularity1_axes_addForm" should have error: "Merci de sélectionner des axes deux à deux non hiérarchiquement reliés."

  @javascript
  Scenario: Edition of a granularity
  # Accès à l'onglet "Niveaux"
    Given I am on "orga/organization/edit/idOrganization/1"
    And I wait for the page to finish loading
    And I open tab "Niveaux"
    Then I should see the "granularity1" datagrid
  # Contenu initial de la granularité "Année"
    And the row 4 of the "granularity1" datagrid should contain:
      | axes  | relevance | acl | reports  |
      | Année | Non       | Non | Non      |
  # Édition des contenus des différentes colonnes
    And I set "Oui" for column "relevance" of row 4 of the "granularity1" datagrid with a confirmation message
    And I set "Oui" for column "reports" of row 4 of the "granularity1" datagrid with a confirmation message
    And I set "Oui" for column "acl" of row 4 of the "granularity1" datagrid with a confirmation message
    Then the row 4 of the "granularity1" datagrid should contain:
      | axes  | relevance | acl | reports  |
      | Année | Oui       | Oui | Oui      |

  @javascript
  Scenario: Attribute 'with roles' of a granularity cannot be changed to 'No' if roles exist for cells at this granularity
  # Accès à l'onglet "Niveaux"
    Given I am on "orga/organization/edit/idOrganization/1"
    And I wait for the page to finish loading
    And I open tab "Niveaux"
    Then I should see the "granularity1" datagrid
  # Tentative de modifier de "oui" à "non" l'attribut "with roles" de la granularité "Site" (pour laquelle il existe des rôles)
    And the row 3 of the "granularity1" datagrid should contain:
      | axes |
      | Site |
    When I set "Non" for column "acl" of row 3 of the "granularity1" datagrid
    Then the following message is shown and closed: "Cette modification ne peut pas être effectuée, car il existe au moins un rôle associé à une unité organisationnelle de ce niveau organisationnel."
  # À l'inverse, pour la granularité "Année", pas de pb
    And the row 4 of the "granularity1" datagrid should contain:
      | axes  | acl |
      | Année | Non |
    When I set "Oui" for column "acl" of row 4 of the "granularity1" datagrid with a confirmation message
    Then the row 4 of the "granularity1" datagrid should contain:
      | axes  | acl |
      | Année | Oui |
    When I set "Non" for column "acl" of row 4 of the "granularity1" datagrid with a confirmation message
    Then the row 4 of the "granularity1" datagrid should contain:
      | axes  | acl |
      | Année | Non |


  @javascript
  Scenario: Deletion of a granularity (test on existing granularities)
  # Accès à l'onglet "Niveaux"
    Given I am on "orga/organization/edit/idOrganization/1"
    And I wait for the page to finish loading
    And I open tab "Niveaux"
    Then I should see the "granularity1" datagrid
  # Suppression, granularité de saisie  (suppression interdite)
    And the "granularity1" datagrid should contain 8 row
    And the row 8 of the "granularity1" datagrid should contain:
      | axes                    |
      | Année, Site, Catégorie  |
    When I click "Supprimer" in the row 8 of the "granularity1" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Ce niveau organisationnel ne peut pas être supprimé, car il est utilisé"
    And the "granularity1" datagrid should contain 8 row
  # Suppression, granularité de choix des formulaires comptables  (suppression interdite)
    And the row 5 of the "granularity1" datagrid should contain:
      | axes              |
      | Année, Catégorie  |
    When I click "Supprimer" in the row 5 of the "granularity1" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Ce niveau organisationnel ne peut pas être supprimé, car il est utilisé"
    And the "granularity1" datagrid should contain 8 row
  # Suppression, granularité du statut des collectes (suppression interdite)
    And the row 6 of the "granularity1" datagrid should contain:
      | axes                |
      | Année, Zone, Marque |
    When I click "Supprimer" in the row 6 of the "granularity1" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Ce niveau organisationnel ne peut pas être supprimé, car il est utilisé"
    And the "granularity1" datagrid should contain 8 row
  # Suppression sans obstacle (granularité "Année")
    And the row 4 of the "granularity1" datagrid should contain:
      | axes  |
      | Année |
    When I click "Supprimer" in the row 4 of the "granularity1" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    And the "granularity1" datagrid should contain 7 row
  # Suppression, granularité avec des rôles
    And the row 3 of the "granularity1" datagrid should contain:
      | axes |
      | Site |
    When I click "Supprimer" in the row 3 of the "granularity1" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Ce niveau organisationnel ne peut pas être supprimé, car il est utilisé"
    And the "granularity1" datagrid should contain 7 row

  @javascript
  Scenario: Deletion of a granularity 'inventory status'
  # Accès à l'onglet "Niveaux"
    Given I am on "orga/organization/edit/idOrganization/1"
    And I wait for the page to finish loading
    And I open tab "Niveaux"
  # Ajout d'une granularité
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un niveau organisationnel"
    When I additionally select "Axe vide" from "granularity1_axes_addForm"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
    And the row 2 of the "granularity1" datagrid should contain:
      | axes     |
      | Axe vide |
  # On choisit cette nouvelle granularité pour le statut des inventaires
    When I open tab "Informations générales"
    And I select "Axe vide" from "Niveau organisationnel des collectes"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Modification effectuée."
  # Tentative de suppression de la granularité
    When I open tab "Niveaux"
    And I click "Supprimer" in the row 2 of the "granularity1" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Ce niveau organisationnel ne peut pas être supprimé, car il est utilisé"

  @javascript
  Scenario: Deletion of a granularity 'with roles'
  # Accès à l'onglet "Niveaux"
    Given I am on "orga/organization/edit/idOrganization/1"
    And I wait for the page to finish loading
    And I open tab "Niveaux"
  # Ajout d'une granularité
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un niveau organisationnel"
    When I additionally select "Axe vide" from "granularity1_axes_addForm"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
    And the row 2 of the "granularity1" datagrid should contain:
      | axes     |
      | Axe vide |
  # On change l'attribut "cellsWithRoles" à "oui"
    When I set "Oui" for column "acl" of row 2 of the "granularity1" datagrid with a confirmation message
  # Tentative de suppression de la granularité
    When I click "Supprimer" in the row 2 of the "granularity1" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Ce niveau organisationnel ne peut pas être supprimé, car il est utilisé"

  @javascript
  Scenario: Deletion of a granularity 'with DW'
    #6300 : La suppression d'une granularité associée à des DWs entraîne une erreur
  # Suppression des rôles associés à la granularité "Site"
    Given I am on "orga/organization/edit/idOrganization/1"
    And I wait for the page to finish loading
    And I open tab "Niveaux"
    Then the row 3 of the "granularity1" datagrid should contain:
      | axes |
      | site |
    When I set "Non" for column "acl" of row 3 of the "granularity1" datagrid with a confirmation message
    And I click "Supprimer" in the row 3 of the "granularity1" datagrid
    And I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    And the "granularity1" datagrid should contain 7 row