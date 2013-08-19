@dbFull
Feature: AF numeric field feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of a numeric field, correct input
  # Accès au datagrid
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Champs numériques"
    Then I should see the "numericFieldDatagrid" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un champ numérique"
  # Ajout, saisie correcte
    And I fill in "numericFieldDatagrid_label_addForm" with "AAA"
    And I fill in "numericFieldDatagrid_ref_addForm" with "aaa"
    And I fill in "numericFieldDatagrid_unit_addForm" with "kg_co2e.m3^-1"
    And I fill in "numericFieldDatagrid_digitalValue_addForm" with "1000,5"
    And I fill in "numericFieldDatagrid_relativeUncertainty_addForm" with "10,9"
    And I fill in "numericFieldDatagrid_help_addForm" with "h1. Blabla"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Champs ordonnés suivant l'ordre de création
    And the "numericFieldDatagrid" datagrid should contain a row:
      | label | ref | isVisible | enabled | required   | unit           | withUncertainty | digitalValue | relativeUncertainty | defaultValueReminder |
      | AAA   | aaa | Visible   | Activé  | Facultatif | kg équ. CO2/m³ | Affichée        | 1 000,5      | 10                  | Masqué               |
    When I click "Aide" in the row 4 of the "numericFieldDatagrid" datagrid
    Then I should see the popup "Aide"
    And I should see a "#numericFieldDatagrid_help_popup .modal-body h1:contains('Blabla')" element
    When I click element ".close:contains('×')"
  # Vérification de la création de l'algorithme de type "saisie de champ numérique" correspondant
    When I open tab "Traitement"
    And I open collapse "Algorithmes numériques"
    And I open collapse "Saisies de champs numériques"
    Then I should see the "algoNumericInput" datagrid
  # Ordre par ordre alphabétique des identifiants pour le datagrid des algos de type "saisie de champ numérique"
    And the "algoNumericInput" datagrid should contain a row:
      | label | ref | input | unit |
      | AAA   | aaa | AAA   | kg équ. CO2/m³ |

  @javascript
  Scenario: Creation of a numeric field, incorrect input
  # Accès au datagrid
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Champs numériques"
    Then I should see the "numericFieldDatagrid" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un champ numérique"
  # Ajout, identifiant et unité vides
    When I click "Valider"
    Then the field "numericFieldDatagrid_ref_addForm" should have error: "Merci de renseigner ce champ."
    And the field "numericFieldDatagrid_unit_addForm" should have error: "Merci de saisir un identifiant d'unité valide."
  # Ajout, identifiant avec caractères non autorisés, unité invalide
    When I fill in "numericFieldDatagrid_ref_addForm" with "bépo"
    And I fill in "numericFieldDatagrid_unit_addForm" with "auie"
    And I click "Valider"
    Then the field "numericFieldDatagrid_unit_addForm" should have error: "Merci de saisir un identifiant d'unité valide."
  # Ajout, identifiant avec caractères non autorisés, unité valide
    When I fill in "numericFieldDatagrid_unit_addForm" with "kg_co2e.m3^-1"
    And I click "Valider"
    Then the field "numericFieldDatagrid_ref_addForm" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Ajout, identifiant déjà utilisé pour un autre composant
    When I fill in "numericFieldDatagrid_ref_addForm" with "sous_g"
    And I click "Valider"
    Then the field "numericFieldDatagrid_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Ajout, identifiant déjà utilisé pour un autre algorithme numerique
    When I fill in "numericFieldDatagrid_ref_addForm" with "constante"
    And I click "Valider"
    Then the field "numericFieldDatagrid_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Ajout, valeur initiale et incertitude initiale pas nombres
    When I fill in "numericFieldDatagrid_digitalValue_addForm" with "auie"
    And I fill in "numericFieldDatagrid_relativeUncertainty_addForm" with "auie"
    And I click "Valider"
    Then the field "numericFieldDatagrid_digitalValue_addForm" should have error: "La quantité saisie n'a pas pu être interprétée, merci de corriger (en français merci d'utiliser la virgule comme séparateur décimal)."
    And the field "numericFieldDatagrid_relativeUncertainty_addForm" should have error: "La quantité saisie n'a pas pu être interprétée, merci de corriger (en français merci d'utiliser la virgule comme séparateur décimal)."
  # Ajout, valeur initiale et incertitude initiale nombres mais pas au bon format
    When I fill in "numericFieldDatagrid_digitalValue_addForm" with "1000.5"
    And I fill in "numericFieldDatagrid_relativeUncertainty_addForm" with "10.9"
    And I click "Valider"
    Then the field "numericFieldDatagrid_digitalValue_addForm" should have error: "La quantité saisie n'a pas pu être interprétée, merci de corriger (en français merci d'utiliser la virgule comme séparateur décimal)."
    And the field "numericFieldDatagrid_relativeUncertainty_addForm" should have error: "La quantité saisie n'a pas pu être interprétée, merci de corriger (en français merci d'utiliser la virgule comme séparateur décimal)."

  @javascript
  Scenario: Edition of a numeric field, correct input
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Champs numériques"
    Then I should see the "numericFieldDatagrid" datagrid
    And the row 1 of the "numericFieldDatagrid" datagrid should contain:
      | label           | ref | isVisible | enabled | required    | unit           | withUncertainty | digitalValue | relativeUncertainty | defaultValueReminder |
      | Champ numérique | c_n | Visible   | Activé  | Obligatoire | kg équ. CO2/m³ | Affichée        | 1 000,5      | 10                  | Affiché               |
  # Modification du libellé
    When I set "Champ numérique modifié" for column "label" of row 1 of the "numericFieldDatagrid" datagrid with a confirmation message
  # Modification de l'identifiant, saisie correcte
    When I set "c_n_modifie" for column "ref" of row 1 of the "numericFieldDatagrid" datagrid with a confirmation message
  # Modification de l'aide
    When I set "h1. Aide modifiée" for column "help" of row 1 of the "numericFieldDatagrid" datagrid with a confirmation message
  # Modification de la visibilité initiale, de l'activation initiale, du caractère obligatoire
    When I set "Masqué" for column "isVisible" of row 1 of the "numericFieldDatagrid" datagrid with a confirmation message
    When I set "Désactivé" for column "enabled" of row 1 of the "numericFieldDatagrid" datagrid with a confirmation message
    When I set "Facultatif" for column "required" of row 1 of the "numericFieldDatagrid" datagrid with a confirmation message
  # Modification de l'unité, unité valide
    When I set "t" for column "unit" of row 1 of the "numericFieldDatagrid" datagrid with a confirmation message
  # Modification de l'affichage de l'incertitude
    When I set "Masquée" for column "withUncertainty" of row 1 of the "numericFieldDatagrid" datagrid with a confirmation message
  # Modification valeur initiale
    When I set "1,5" for column "digitalValue" of row 1 of the "numericFieldDatagrid" datagrid with a confirmation message
  # Modification incertitude initiale
    When I set "15,9" for column "relativeUncertainty" of row 1 of the "numericFieldDatagrid" datagrid with a confirmation message
  # Modification rappel valeur par défaut
    When I set "Masqué" for column "defaultValueReminder" of row 1 of the "numericFieldDatagrid" datagrid with a confirmation message
  # Vérification que les modifications on bien été prises en compte au niveau du datagrid
    Then the row 1 of the "numericFieldDatagrid" datagrid should contain:
      | label                   | ref         | isVisible | enabled   | required   | unit | withUncertainty | digitalValue | relativeUncertainty | defaultValueReminder |
      | Champ numérique modifié | c_n_modifie | Masqué    | Désactivé | Facultatif | t    | Masquée         | 1,5          | 15                  | Masqué               |
    When I click "Aide" in the row 1 of the "numericFieldDatagrid" datagrid
    Then I should see the popup "Aide"
    And I should see a "#numericFieldDatagrid_help_popup .modal-body h1:contains('Aide modifiée')" element
    When I click element ".close:contains('×')"
  # Vérification que les modifications on bien été prises en compte pour l'algo de type champ numérique correspondant
    When I open tab "Traitement"
    And I open collapse "Algorithmes numériques"
    And I open collapse "Saisies de champs numériques"
    Then I should see the "algoNumericInput" datagrid
  # Ordre par ordre alphabétique des identifiants pour le datagrid des algos de type "saisie de champ numérique"
    And the "algoNumericInput" datagrid should contain a row:
      | label    | ref                     | input                     | unit |
      | Champ numérique | c_n_modifie | Champ numérique modifié   | t    |

  @javascript
  Scenario: Edition of a numeric field, incorrect input
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Champs numériques"
    Then I should see the "numericFieldDatagrid" datagrid
  # Modification de l'identifiant, identifiant vide
    When I set "" for column "ref" of row 1 of the "numericFieldDatagrid" datagrid
    Then the following message is shown and closed: "Merci de renseigner ce champ."
  # Modification de l'identifiant, identifiant avec caractères non autorisés
    When I set "bépo" for column "ref" of row 1 of the "numericFieldDatagrid" datagrid
    Then the following message is shown and closed: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Modification de l'identifiant, identifiant déjà utilisé pour un autre composant
    When I set "sous_g" for column "ref" of row 1 of the "numericFieldDatagrid" datagrid
    Then the following message is shown and closed: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Modification de l'identifiant, identifiant déjà utilisé pour un autre algorithme numérique
    When I set "constante" for column "ref" of row 1 of the "numericFieldDatagrid" datagrid
    Then the following message is shown and closed: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Modification de l'unité, unité vide ou invalide
    When I set "" for column "unit" of row 1 of the "numericFieldDatagrid" datagrid
    Then the following message is shown and closed: "Merci de saisir un identifiant d'unité valide."
    When I set "auie" for column "unit" of row 1 of the "numericFieldDatagrid" datagrid
    Then the following message is shown and closed: "Merci de saisir un identifiant d'unité valide."
  # Modification valeur initiale
    When I set "auie" for column "digitalValue" of row 1 of the "numericFieldDatagrid" datagrid
    Then the following message is shown and closed: "La quantité saisie n'a pas pu être interprétée, merci de corriger (en français merci d'utiliser la virgule comme séparateur décimal)."
    When I set "1.5" for column "digitalValue" of row 1 of the "numericFieldDatagrid" datagrid
    Then the following message is shown and closed: "La quantité saisie n'a pas pu être interprétée, merci de corriger (en français merci d'utiliser la virgule comme séparateur décimal)."
  # Modification incertitude initiale
    When I set "auie" for column "relativeUncertainty" of row 1 of the "numericFieldDatagrid" datagrid
    Then the following message is shown and closed: "La quantité saisie n'a pas pu être interprétée, merci de corriger (en français merci d'utiliser la virgule comme séparateur décimal)."
    When I set "15.9" for column "relativeUncertainty" of row 1 of the "numericFieldDatagrid" datagrid
    Then the following message is shown and closed: "La quantité saisie n'a pas pu être interprétée, merci de corriger (en français merci d'utiliser la virgule comme séparateur décimal)."

  @javascript
  Scenario: Deletion of a numeric field
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Champs numériques"
    Then I should see the "numericFieldDatagrid" datagrid
    And the "numericFieldDatagrid" datagrid should contain 3 row
    And the row 1 of the "numericFieldDatagrid" datagrid should contain:
      | label           |
      | Champ numérique |
    And the row 2 of the "numericFieldDatagrid" datagrid should contain:
      | label                            |
      | Champ numérique cible activation |
    And the row 3 of the "numericFieldDatagrid" datagrid should contain:
      | label                          |
      | Champ numérique cible setvalue |
  # Suppression des champs cibles d'actions
    When I open tab "Interactions"
    And I open collapse "Modifications de l'état de composants"
    Then I should see the "actionsSetState" datagrid
    And the "actionsSetState" datagrid should contain 1 row
    When I open collapse "Assignations de valeurs à des champs"
    Then I should see the "actionsSetValue" datagrid
    And the "actionsSetValue" datagrid should contain 3 row
    When I open tab "Composants"
    # And I open collapse "Champs numériques" (déjà ouvert !)
    When I click "Supprimer" in the row 3 of the "numericFieldDatagrid" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    When I click "Supprimer" in the row 2 of the "numericFieldDatagrid" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    And the "numericFieldDatagrid" datagrid should contain 1 row
  # Vérification que les actions ayant pour cibles les deux champs supprimés ont également été supprimées
    When I open tab "Interactions"
  # On ferme et on rouvre pour rafraîchir le contenu
    And I close collapse "Modifications de l'état de composants"
    And I open collapse "Modifications de l'état de composants"
    Then I should see the "actionsSetState" datagrid
    And the "actionsSetState" datagrid should contain 0 row
  # On ferme et on rouvre pour rafraîchir le contenu
    When I close collapse "Assignations de valeurs à des champs"
    And I open collapse "Assignations de valeurs à des champs"
    Then I should see the "actionsSetValue" datagrid
    And the "actionsSetValue" datagrid should contain 2 row
  # Suppression sans obstacle
    When I open tab "Composants"
  # And I open collapse "Champs numériques" (déjà ouvert !)
    When I click "Supprimer" in the row 1 of the "numericFieldDatagrid" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    And the "numericFieldDatagrid" datagrid should contain 0 row
  # Vérification que les suppressions ont bien été prises en compte pour les algos de type sélection d'identifiant correspondants
    When I open tab "Traitement"
    And I open collapse "Algorithmes numériques"
    And I open collapse "Saisies de champs numériques"
    Then I should see the "algoNumericInput" datagrid
    And the "algoNumericInput" datagrid should contain 0 row