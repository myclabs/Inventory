@dbFull
Feature: AF selection organizatinal coordinate algo feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of a workspace coordinate algo scenario, correct input
    Given I am on "af/edit/menu/id/4/onglet/traitement"
    And I wait for the page to finish loading
    And I open collapse "Algorithmes de sélection d’identifiant"
    And I open collapse "Coordonnées organisationnelles"
    Then I should see the "algoSelectionTextkeyContextValue" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un algorithme de sélection d’identifiant de type « coordonnée organisationnelle »"
  # Ajout, saisie correcte
    When I fill in "algoSelectionTextkeyContextValue_ref_addForm" with "aaa"
    And I fill in "algoSelectionTextkeyContextValue_name_addForm" with "bbb"
    And I fill in "algoSelectionTextkeyContextValue_defaultValue_addForm" with "ccc"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Vérification contenu datagrid et ajout correctement effectué
    And the "algoSelectionTextkeyContextValue" datagrid should contain a row:
      | ref | name | defaultValue |
      | aaa | bbb  | ccc          |

  @javascript
  Scenario: Creation of a workspace coordinate algo scenario, incorrect input
    Given I am on "af/edit/menu/id/4/onglet/traitement"
    And I wait for the page to finish loading
    And I open collapse "Algorithmes de sélection d’identifiant"
    And I open collapse "Coordonnées organisationnelles"
    And I click "Ajouter"
  # Ajout, identifiant vide
    And I click "Valider"
    Then the field "algoSelectionTextkeyContextValue_ref_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout, identifiant avec caractères non autorisés
    When I fill in "algoSelectionTextkeyContextValue_ref_addForm" with "bépo"
    And I click "Valider"
    Then the field "algoSelectionTextkeyContextValue_ref_addForm" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Ajout, identifiant déjà utilisé
    When I fill in "algoSelectionTextkeyContextValue_ref_addForm" with "c_n"
    And I click "Valider"
    Then the field "algoSelectionTextkeyContextValue_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # TODO : identifiant axe organisationnel et identifiant valeur par défaut obligatoires ?

  @javascript
  Scenario: Edition of a workspace coordinate algo scenario, correct input
    Given I am on "af/edit/menu/id/4/onglet/traitement"
    And I wait for the page to finish loading
    And I open collapse "Algorithmes de sélection d’identifiant"
    And I open collapse "Coordonnées organisationnelles"
  # La vérification du contenu initial est déjà faite dans le test de suppression
  # On modifie l'identifiant en dernier car ça modifie l'ordre entre les lignes
  # Modification de l'axe organisationnel et de la valeur par défaut
    When I set "axis_ref_1_modifie" for column "name" of row 1 of the "algoSelectionTextkeyContextValue" datagrid with a confirmation message
    When I set "dafault_value_1_modifie" for column "defaultValue" of row 1 of the "algoSelectionTextkeyContextValue" datagrid with a confirmation message
  # Modification de l'identifiant, saisie correcte
    When I set "orga_coordinate_modifiee" for column "ref" of row 1 of the "algoSelectionTextkeyContextValue" datagrid with a confirmation message
  # Vérification prise en compte modification
    Then the "algoSelectionTextkeyContextValue" datagrid should contain a row:
      | ref                      | name               | defaultValue            |
      | orga_coordinate_modifiee | axis_ref_1_modifie | dafault_value_1_modifie |

  @javascript
  Scenario: Edition of a workspace coordinate algo scenario, incorrect input
    Given I am on "af/edit/menu/id/4/onglet/traitement"
    And I wait for the page to finish loading
    And I open collapse "Algorithmes de sélection d’identifiant"
    And I open collapse "Coordonnées organisationnelles"
  # Modification de l'identifiant, identifiant vide
    And I set "" for column "ref" of row 1 of the "algoSelectionTextkeyContextValue" datagrid
    Then the following message is shown and closed: "Merci de renseigner ce champ."
  # Modification de l'identifiant, identifiant avec caractères non autorisés
    When I set "bépo" for column "ref" of row 1 of the "algoSelectionTextkeyContextValue" datagrid
    Then the following message is shown and closed: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Modification de l'identifiant, identifiant déjà utilisé
    When I set "c_n" for column "ref" of row 1 of the "algoSelectionTextkeyContextValue" datagrid
    Then the following message is shown and closed: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."

  @javascript
  Scenario: Deletion of a workspace coordinate algo scenario
    Given I am on "af/edit/menu/id/4/onglet/traitement"
    And I wait for the page to finish loading
    And I open collapse "Algorithmes de sélection d’identifiant"
    And I open collapse "Coordonnées organisationnelles"
    Then  the "algoSelectionTextkeyContextValue" datagrid should contain 3 row
    And the row 1 of the "algoSelectionTextkeyContextValue" datagrid should contain:
      | ref            |
      | orga_coordinate |
    And the row 2 of the "algoSelectionTextkeyContextValue" datagrid should contain:
      | ref  |
      | orga_coordinate_coord_param |
    And the row 3 of the "algoSelectionTextkeyContextValue" datagrid should contain:
      | ref  |
      | orga_coordinate_index_algo |
  # Algo utilisé pour la détermination d'une coordonnée de paramètre
    When I click "Supprimer" in the row 2 of the "algoSelectionTextkeyContextValue" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Cet algorithme ne peut pas être supprimé, car il est utilisé pour l'indexation d'au moins un algorithme numérique ou la détermination d'au moins une coordonnée d'algorithme de type paramètre."
    And the "algoSelectionTextkeyContextValue" datagrid should contain 3 row
  # Algo utilisé pour l'indexation d'un algo numérique
    When I click "Supprimer" in the row 3 of the "algoSelectionTextkeyContextValue" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Cet algorithme ne peut pas être supprimé, car il est utilisé pour l'indexation d'au moins un algorithme numérique ou la détermination d'au moins une coordonnée d'algorithme de type paramètre."
    And the "algoSelectionTextkeyContextValue" datagrid should contain 3 row
  # Suppression sans obstacle
    When I click "Supprimer" in the row 1 of the "algoSelectionTextkeyContextValue" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    And the "algoSelectionTextkeyContextValue" datagrid should contain 2 row