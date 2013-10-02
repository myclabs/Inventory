@dbFull
Feature: AF single selection field feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of a single selection field scenario, correct input
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Champs de sélection simple"
    Then I should see the "selectSingleFieldDatagrid" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un champ de sélection simple"
  # Ajout, saisie correcte
    When I fill in "selectSingleFieldDatagrid_label_addForm" with "AAA"
    And I fill in "selectSingleFieldDatagrid_ref_addForm" with "aaa"
    And I fill in "selectSingleFieldDatagrid_help_addForm" with "h1. Blabla"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Champs ordonnés suivant l'ordre de création, vérification des valeurs par défaut
    And the row 5 of the "selectSingleFieldDatagrid" datagrid should contain:
      | label | ref | isVisible | enabled | required    | type             |
      | AAA   | aaa | Visible   | Activé  | Facultatif  | Liste déroulante |
    When I click "Aide" in the row 5 of the "selectSingleFieldDatagrid" datagrid
    Then I should see the popup "Aide"
    And I should see a "#selectSingleFieldDatagrid_help_popup .modal-body h1:contains('Blabla')" element
    When I click "×"
  # On ferme le collapse pour pouvoir accéder à l'onglet traitement sans scroller (sinon invisible)
    # And I close collapse "Champs de sélection simple"
  # Vérification de la création de l'algorithme de type "sélection d’identifiant à partir d'une saisie de champ de sélection simple" correspondant
    When I open tab "Traitement"
    And I open collapse "Algorithmes de sélection d’identifiant"
    And I open collapse "À partir d'une saisie de champ de sélection simple"
    Then I should see the "algoSelectionTextkeyInput" datagrid
  # Ordre par ordre alphabétique des identifiants pour le datagrid des algos de type "sélection d'identifiant à partir d'une saisie de champ de sélection simple"
    And the "algoSelectionTextkeyInput" datagrid should contain a row:
      | ref | input |
      | aaa | AAA   |

  @javascript
  Scenario: Creation of a single selection field scenario, incorrect input
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Champs de sélection simple"
    Then I should see the "selectSingleFieldDatagrid" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un champ de sélection simple"
  # Ajout, identifiant vide
    When I click "Valider"
  # Then the field "selectSingleFieldDatagrid_label_addForm" should have error: "Merci de renseigner ce champ."
    And the field "selectSingleFieldDatagrid_ref_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout, identifiant avec caractères non autorisés
    When I fill in "selectSingleFieldDatagrid_ref_addForm" with "bépo"
    And I click "Valider"
    Then the field "selectSingleFieldDatagrid_ref_addForm" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Ajout, identifiant déjà utilisé pour un autre composant
    When I fill in "selectSingleFieldDatagrid_ref_addForm" with "c_n"
    And I click "Valider"
    Then the field "selectSingleFieldDatagrid_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Ajout, identifiant déjà utilisé pour un autre algorithme de sélection d'identifiant
    When I fill in "selectSingleFieldDatagrid_ref_addForm" with "expression_sel"
    And I click "Valider"
    Then the field "selectSingleFieldDatagrid_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."

  @javascript
  Scenario: Edition of a single selection field scenario, correct input
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Champs de sélection simple"
    Then I should see the "selectSingleFieldDatagrid" datagrid
    And the row 1 of the "selectSingleFieldDatagrid" datagrid should contain:
      | label                  | ref                    | isVisible | enabled | required    | defaultValue | type             |
      | Champ sélection simple | c_s_s | Visible   | Activé  | Obligatoire |              | Liste déroulante |
  # Modification du libellé
    When I set "Champ sélection simple modifié" for column "label" of row 1 of the "selectSingleFieldDatagrid" datagrid with a confirmation message
  # Modification de l'identifiant, saisie correcte
    When I set "c_s_s_modifie" for column "ref" of row 1 of the "selectSingleFieldDatagrid" datagrid with a confirmation message
  # Modification de l'aide
    When I set "h1. Aide modifiée" for column "help" of row 1 of the "selectSingleFieldDatagrid" datagrid with a confirmation message
  # Modification des autres attributs
    When I set "Masqué" for column "isVisible" of row 1 of the "selectSingleFieldDatagrid" datagrid with a confirmation message
    When I set "Désactivé" for column "enabled" of row 1 of the "selectSingleFieldDatagrid" datagrid with a confirmation message
    When I set "Facultatif" for column "required" of row 1 of the "selectSingleFieldDatagrid" datagrid with a confirmation message
    When I set "Option 1" for column "defaultValue" of row 1 of the "selectSingleFieldDatagrid" datagrid with a confirmation message
    When I set "Boutons radio" for column "type" of row 1 of the "selectSingleFieldDatagrid" datagrid with a confirmation message
  # Vérification que les modifications on bien été prises en compte au niveau du datagrid
    Then the row 1 of the "selectSingleFieldDatagrid" datagrid should contain:
      | label                          | ref                       | isVisible | enabled    | required | defaultValue | type          |
      | Champ sélection simple modifié | c_s_s_modifie | Masqué    | Désactivé | Facultatif | Option 1 | Boutons radio |
    When I click "Aide" in the row 1 of the "selectSingleFieldDatagrid" datagrid
    Then I should see the popup "Aide"
    And I should see a "#selectSingleFieldDatagrid_help_popup .modal-body h1:contains('Aide modifiée')" element
    When I click "×"
  # Vérification que les modifications on bien été prises en compte pour l'algo de type sélection d'identifiant correspondant
    When I open tab "Traitement"
    And I open collapse "Algorithmes de sélection d’identifiant"
    And I open collapse "À partir d'une saisie de champ de sélection simple"
    Then I should see the "algoSelectionTextkeyInput" datagrid
  # Ordre par ordre alphabétique des identifiants pour le datagrid des algos de type "sélection d'identifiant à partir d'une saisie de champ de sélection simple"
    And the "algoSelectionTextkeyInput" datagrid should contain a row:
      | ref           | input                          |
      | c_s_s_modifie | Champ sélection simple modifié |

  @javascript
  Scenario: Edition of a single selection field scenario, incorrect input
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Champs de sélection simple"
    Then I should see the "selectSingleFieldDatagrid" datagrid
  # Modification de l'identifiant, identifiant vide
    When I set "" for column "ref" of row 1 of the "selectSingleFieldDatagrid" datagrid
    Then the following message is shown and closed: "Merci de renseigner ce champ."
  # Modification de l'identifiant, identifiant avec caractères non autorisés
    When I set "bépo" for column "ref" of row 1 of the "selectSingleFieldDatagrid" datagrid
    Then the following message is shown and closed: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Modification de l'identifiant, identifiant déjà utilisé pour un autre composant
    When I set "c_n" for column "ref" of row 1 of the "selectSingleFieldDatagrid" datagrid
    Then the following message is shown and closed: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Modification de l'identifiant, identifiant déjà utilisé pour un autre algorithme de sélection d'identifiant
    When I set "expression_sel" for column "ref" of row 1 of the "selectSingleFieldDatagrid" datagrid
    Then the following message is shown and closed: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."

  @javascript
  Scenario: Deletion of a single selection field scenario, 1
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Champs de sélection simple"
    Then I should see the "selectSingleFieldDatagrid" datagrid
    And the "selectSingleFieldDatagrid" datagrid should contain 4 row
    And the row 1 of the "selectSingleFieldDatagrid" datagrid should contain:
      | label                  |
      | Champ sélection simple |
    And the row 2 of the "selectSingleFieldDatagrid" datagrid should contain:
      | label                                                                                   |
      | Champ sélection simple utilisé par une condition élémentaire de l'onglet "Interactions" |
    And the row 3 of the "selectSingleFieldDatagrid" datagrid should contain:
      | label                                                                                 |
      | Champ sélection simple utilisé par une condition élémentaire de l'onglet "Traitement" |
    And the row 4 of the "selectSingleFieldDatagrid" datagrid should contain:
      | label                                                |
      | Champ sélection simple cible d'une action "setValue" |
  # Suppression, algo utilisé par une condition élémentaire de l'onglet "Interactions"
    When I click "Supprimer" in the row 2 of the "selectSingleFieldDatagrid" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Ce champ ne peut pas être supprimé, car une (au moins) des conditions élémentaires de l'onglet « Interactions » porte sur lui."
    And the "selectSingleFieldDatagrid" datagrid should contain 4 row
  # Suppression, algo utilisé par une condition élémentaire de l'onglet "Traitement"
    When I click "Supprimer" in the row 3 of the "selectSingleFieldDatagrid" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Ce champ ne peut pas être supprimé, car une (au moins) des conditions élémentaires de l'onglet « Traitement » porte sur lui."
    And the "selectSingleFieldDatagrid" datagrid should contain 4 row
  # Suppression, algo cible d'une action (en l'occurrence une action setValue)
  # La suppression est bien permise, et donne lieu à la suppression de l'action en question
    When open tab "Interactions"
    And I open collapse "Assignations de valeurs à des champs"
    Then I should see the "actionsSetValue" datagrid
    And the "actionsSetValue" datagrid should contain 3 row
    When I open tab "Composants"
    # And I open collapse "Champs de sélection simple" (déjà ouvert !)
    Then I should see the "selectSingleFieldDatagrid" datagrid
    And the "selectSingleFieldDatagrid" datagrid should contain 4 row
    When I click "Supprimer" in the row 4 of the "selectSingleFieldDatagrid" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    And the "selectSingleFieldDatagrid" datagrid should contain 3 row
  # On vérifie que l'action correspondante a bien été supprimée
    When open tab "Interactions"
    And I close collapse "Assignations de valeurs à des champs"
    And I open collapse "Assignations de valeurs à des champs"
    Then I should see the "actionsSetValue" datagrid
    And the "actionsSetValue" datagrid should contain 2 row
  # Suppression sans obstacle
    When I open tab "Composants"
  # And I open collapse "Champs de sélection simple" (déjà ouvert !)
    When I click "Supprimer" in the row 1 of the "selectSingleFieldDatagrid" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    And the "selectSingleFieldDatagrid" datagrid should contain 2 row
  # Vérification que les suppressions ont bien été prises en compte pour les algos de type sélection d'identifiant correspondanti
    When I open tab "Traitement"
    And I open collapse "Algorithmes de sélection d’identifiant"
    And I open collapse "À partir d'une saisie de champ de sélection simple"
    Then I should see the "algoSelectionTextkeyInput" datagrid
    And the "algoSelectionTextkeyInput" datagrid should contain 2 row

  @javascript
  Scenario: Deletion of a single selection field scenario, 2
  # Algo de sélection simple utilisé comme coordonnée de paramètre ou indexation d'algorithme numérique
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
  # Modifier la config pour que l'algo de sélection simple "c_s_s" soit utilisé comme coordonnée de paramètre
    And I open tab "Traitement"
    And I open collapse "Algorithmes numériques"
    And I open collapse "Paramètres"
    And I click "Coordonnées" in the row 1 of the "algoNumericParameter" datagrid
    And I set "c_s_s" for column "algo" of row 1 of the "coordinatesAlgo" datagrid
    And I click element "#algoNumericParameter_coordinates_popup .close:contains('×')"
    And I wait 5 seconds
    Then the following message is shown and closed: "Modification effectuée."
  # Tentative de suppression du champ de sélection simple
    When I open tab "Composants"
    And I open collapse "Champs de sélection simple"
    Then I should see the "selectSingleFieldDatagrid" datagrid
    And the "selectSingleFieldDatagrid" datagrid should contain 4 row
    When I click "Supprimer" in the row 1 of the "selectSingleFieldDatagrid" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Ce champ ne peut pas être supprimé, car il est utilisé pour l'indexation d'au moins un algorithme numérique ou la détermination d'au moins une coordonnée d'algorithme de type paramètre."
    And the "selectSingleFieldDatagrid" datagrid should contain 4 row

  @javascript
  Scenario: Deletion of a single selection field scenario, 3
  # Algo de sélection simple utilisé comme coordonnée de paramètre ou indexation d'algorithme numérique
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
  # Modifier la config pour que l'algo de sélection simple "c_s_s" soit utilisé comme membre d'axe de classification pour l'indexation d'un algo numérique
    And I open tab "Traitement"
    And I open collapse "Algorithmes numériques"
    And I open collapse "Saisies de champs numériques"
    And I click "Indexation" in the row 1 of the "algoNumericInput" datagrid
    Then I should see the "algoResultIndexes" datagrid
    And I set "c_s_s" for column "value" of row 2 of the "algoResultIndexes" datagrid
    And I click element "#algoNumericInput_resultIndex_popup .close:contains('×')"
    And I wait 5 seconds
    Then the following message is shown and closed: "Modification effectuée."
  # Tentative de suppression du champ de sélection simple
    When I open tab "Composants"
    And I open collapse "Champs de sélection simple"
    Then I should see the "selectSingleFieldDatagrid" datagrid
    And the "selectSingleFieldDatagrid" datagrid should contain 4 row
    When I click "Supprimer" in the row 1 of the "selectSingleFieldDatagrid" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Ce champ ne peut pas être supprimé, car il est utilisé pour l'indexation d'au moins un algorithme numérique ou la détermination d'au moins une coordonnée d'algorithme de type paramètre."
    And the "selectSingleFieldDatagrid" datagrid should contain 4 row




