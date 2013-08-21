@dbFull
Feature: Organization granularity feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of a granularity, correct input
  # Accès à l'onglet "Niveaux"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Organisation"
    And I open tab "Niveaux"
    Then I should see the "granularity" datagrid
  # Ajout d'une granularité non déjà existante
  # Toutes les options cochées
    And I click "Ajouter"
    Then I should see the popup "Ajout d'un niveau organisationnel"
    When I additionally select "Zone" from "granularity_axes_addForm"
    And I select "Navigable" in radio "Navigable"
    And I select "Oui" in radio "Organisation"
    And I select "Oui" in radio "Rôles"
    And I select "Oui" in radio "Formulaires"
    And I select "Oui" in radio "Analyses"
    And I select "Oui" in radio "Modèles d'action"
    And I select "Oui" in radio "Actions"
    And I select "Oui" in radio "Documents"
    And I click "Valider"
  # Nécessité d'une attente longue du fait de la présence du masque de chargement, pour que le scénario passe en local (affichage du message de confirmation)
    And I wait 30 seconds
    Then the following message is shown and closed: "Ajout en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps ainsi qu'un rechargement de la page."
    And the row 2 of the "granularity" datagrid should contain:
      | axes | navigable  | orgaTab | aCL | aFTab | dW  | genericActions | contextActions | inputDocuments |
      | Zone | Navigable  | Oui     | Oui | Oui   | Oui | Oui            | Oui            | Oui            |

  @javascript
  Scenario: Creation of a granularity, incorrect input
  # Accès à l'onglet "Niveaux"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Organisation"
    And I open tab "Niveaux"
    Then I should see the "granularity" datagrid
  # Ajout d'une granularité déjà existante
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un niveau organisationnel"
    When I additionally select "Année" from "granularity_axes_addForm"
    And I click "Valider"
    Then the field "granularity_axes_addForm" should have error: "Il existe déjà un niveau organisationnel correspondant à cette combinaison d'axes."
    When I click "Annuler"
  # Ajout d'une granularité déjà existante, bis
    And I click "Ajouter"
    Then I should see the popup "Ajout d'un niveau organisationnel"
    When I additionally select "Année" from "granularity_axes_addForm"
    And I additionally select "Site" from "granularity_axes_addForm"
    And I additionally select "Catégorie" from "granularity_axes_addForm"
    And I click "Valider"
    Then the field "granularity_axes_addForm" should have error: "Il existe déjà un niveau organisationnel correspondant à cette combinaison d'axes."
    When I click "Annuler"
  # Ajout, axes non deux à deux transverses
    And I click "Ajouter"
    Then I should see the popup "Ajout d'un niveau organisationnel"
    When I additionally select "Zone" from "granularity_axes_addForm"
    And I additionally select "Site" from "granularity_axes_addForm"
    And I click "Valider"
    Then the field "granularity_axes_addForm" should have error: "Merci de sélectionner des axes deux à deux non hiérarchiquement reliés."

  @javascript
  Scenario: Edition of a granularity
  # Accès à l'onglet "Niveaux"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Organisation"
    And I open tab "Niveaux"
    Then I should see the "granularity" datagrid
  # Contenu initial de la granularité "Année"
    And the row 4 of the "granularity" datagrid should contain:
      | axes  | navigable      | orgaTab | aCL | aFTab | dW  | genericActions | contextActions | inputDocuments |
      | Année | Non navigable  | Non     | Non | Non   | Non | Non            | Non            | Non            |
  # Édition des contenus des différentes colonnes
    When I set "Navigable" for column "navigable" of row 4 of the "granularity" datagrid with a confirmation message
    And I set "Oui" for column "orgaTab" of row 4 of the "granularity" datagrid with a confirmation message
    And I set "Oui" for column "aCL" of row 4 of the "granularity" datagrid with a confirmation message
    And I set "Oui" for column "aFTab" of row 4 of the "granularity" datagrid with a confirmation message
    And I set "Oui" for column "dW" of row 4 of the "granularity" datagrid with a confirmation message
    And I set "Oui" for column "genericActions" of row 4 of the "granularity" datagrid with a confirmation message
    And I set "Oui" for column "contextActions" of row 4 of the "granularity" datagrid with a confirmation message
    And I set "Oui" for column "inputDocuments" of row 4 of the "granularity" datagrid with a confirmation message
    Then the row 4 of the "granularity" datagrid should contain:
      | axes  | navigable  | orgaTab | aCL | aFTab | dW  | genericActions | contextActions | inputDocuments |
      | Année | Navigable  | Oui     | Oui | Oui   | Oui | Oui            | Oui            | Oui            |

  @javascript
  Scenario: Attribute 'with roles' of a granularity cannot be changed to 'No' if roles exist for cells at this granularity
  # Accès à l'onglet "Niveaux"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Organisation"
    And I open tab "Niveaux"
    Then I should see the "granularity" datagrid
    And the row 3 of the "granularity" datagrid should contain:
      | axes  |
      | Site  |
    When I set "Non" for column "aCL" of row 3 of the "granularity" datagrid
    # Then the following message is shown and closed: ""

  @javascript
  Scenario: Deletion of a granularity (test on existing granularities)
  # Accès à l'onglet "Niveaux"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Organisation"
    And I open tab "Niveaux"
    Then I should see the "granularity" datagrid
  # Suppression, granularité de saisie  (suppression interdite)
    And the "granularity" datagrid should contain 8 row
    And the row 8 of the "granularity" datagrid should contain:
      | axes  |
      | Année, Site, Catégorie  |
    When I click "Supprimer" in the row 8 of the "granularity" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Ce niveau organisationnel ne peut pas être supprimé, car il est utilisé"
    And the "granularity" datagrid should contain 8 row
  # Suppression, granularité de choix des formulaires comptables  (suppression interdite)
    And the row 5 of the "granularity" datagrid should contain:
      | axes  |
      | Année, Catégorie  |
    When I click "Supprimer" in the row 5 of the "granularity" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Ce niveau organisationnel ne peut pas être supprimé, car il est utilisé"
    And the "granularity" datagrid should contain 8 row
  # Suppression, granularité du statut des collectes (suppression interdite)
    And the row 6 of the "granularity" datagrid should contain:
      | axes  |
      | Année, Zone, Marque |
    When I click "Supprimer" in the row 6 of the "granularity" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Ce niveau organisationnel ne peut pas être supprimé, car il est utilisé"
    And the "granularity" datagrid should contain 8 row
  # Suppression sans obstacle (granularité "Année")
    And the row 4 of the "granularity" datagrid should contain:
      | axes  |
      | Année |
    When I click "Supprimer" in the row 4 of the "granularity" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    And the "granularity" datagrid should contain 7 row
  # Suppression, granularité avec des rôles
    And the row 3 of the "granularity" datagrid should contain:
      | axes  |
      | Site |
    When I click "Supprimer" in the row 3 of the "granularity" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Ce niveau organisationnel ne peut pas être supprimé, car il est utilisé"
    And the "granularity" datagrid should contain 7 row

  @javascript
  Scenario: Deletion of a granularity 'inventory status'
  # Accès à l'onglet "Niveaux"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Organisation"
    And I open tab "Niveaux"
  # Ajout d'une granularité
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un niveau organisationnel"
    When I additionally select "Axe vide" from "granularity_axes_addForm"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps ainsi qu'un rechargement de la page."
    And the row 2 of the "granularity" datagrid should contain:
      | axes     |
      | Axe vide |
  # On choisit cette nouvelle granularité pour le statut des inventaires
    When I open tab "Configuration"
    And I select "Axe vide" from "Niveau organisationnel des collectes"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Modification effectuée."
  # Tentative de suppression de la granularité
    When I open tab "Niveaux"
    And I click "Supprimer" in the row 2 of the "granularity" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Ce niveau organisationnel ne peut pas être supprimé, car il est utilisé"

  @javascript
  Scenario: Deletion of a granularity 'with roles'
  # Accès à l'onglet "Niveaux"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Organisation"
    And I open tab "Niveaux"
  # Ajout d'une granularité
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un niveau organisationnel"
    When I additionally select "Axe vide" from "granularity_axes_addForm"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps ainsi qu'un rechargement de la page."
    And the row 2 of the "granularity" datagrid should contain:
      | axes     |
      | Axe vide |
  # On change l'attribut "cellsWithRoles" à "oui"
    When I set "Oui" for column "aCL" of row 2 of the "granularity" datagrid with a confirmation message
  # Tentative de suppression de la granularité
    When I click "Supprimer" in the row 2 of the "granularity" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Ce niveau organisationnel ne peut pas être supprimé, car il est utilisé"

  @javascript @skipped
  Scenario: Deletion of a granularity 'with DW'
    #6300 : La suppression d'une granularité associée à des DWs entraîne une erreur
  # Suppression des rôles associés à la granularité "Site"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
  # Accès à "Annecy"
    And I select "Annecy" from "site"
    And I click element "#goTo3"
    And I open tab "Rôles"
    And I open collapse "Site"
    And I click "Supprimer" in the row 3 of the "granularityACL3" datagrid
    And I click "Confirmer"
    And I click "Supprimer" in the row 2 of the "granularityACL3" datagrid
    And I click "Confirmer"
    And I click "Supprimer" in the row 1 of the "granularityACL3" datagrid
    And I click "Confirmer"
  # Accès à "Chambéry"
    And I click "Vue globale"
    And I select "Chambéry" from "site"
    And I click element "#goTo3"
    And I open tab "Rôles"
    And I open collapse "Site"
    And I click "Supprimer" in the row 3 of the "granularityACL3" datagrid
    And I click "Confirmer"
    And I click "Supprimer" in the row 2 of the "granularityACL3" datagrid
    And I click "Confirmer"
    And I click "Supprimer" in the row 1 of the "granularityACL3" datagrid
    And I click "Confirmer"
  # Modification de l'attribut "with roles" pour la granularité "site"
    And I click "Vue globale"
    And I open tab "Organisation"
    And I open tab "Niveaux"
    Then the row 3 of the "granularity" datagrid should contain:
      | axes |
      | site |
    When I set "Non" for column "aCL" of row 3 of the "granularity" datagrid
    And I click "Supprimer" in the row 3 of the "granularity" datagrid
    And I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    And the "granularity" datagrid should contain 7 row