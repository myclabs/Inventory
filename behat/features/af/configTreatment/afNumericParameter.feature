@dbFull
Feature: AF numeric parameter algo feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of an algo numeric parameter scenario, correct input
    Given I am on "af/edit/menu/id/4/onglet/traitement"
    And I wait for the page to finish loading
    And I open collapse "Algorithmes numériques"
    And I open collapse "Paramètres"
    Then I should see the "algoNumericParameter" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un algorithme numérique de type « paramètre »"
  # TODO : ordre entre les familles…
  # Ajout, identifiant valide
    When I fill in "algoNumericParameter_label_addForm" with "AAA"
    And I fill in "algoNumericParameter_ref_addForm" with "aaa"
    And I select "Masse volumique de combustible" from "algoNumericParameter_family_addForm"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Algos ordonnés suivant l'ordre alphabétique des identifiants ?
    And the "algoNumericParameter" datagrid should contain a row:
      | label | ref  | family         |
      | AAA   | aaa  | Masse volumique de combustible |

  @javascript
  Scenario: Creation of an algo numeric parameter scenario, incorrect input
    Given I am on "af/edit/menu/id/4/onglet/traitement"
    And I wait for the page to finish loading
    And I open collapse "Algorithmes numériques"
    And I open collapse "Paramètres"
    Then I should see the "algoNumericParameter" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un algorithme numérique de type « paramètre »"
  # Ajout, identifiant vide, famille vide
    When I click "Valider"
    Then the field "algoNumericParameter_ref_addForm" should have error: "Merci de renseigner ce champ."
    And the field "algoNumericParameter_family_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout, famille renseignée, identifiant avec caractères non autorisés
    When I fill in "algoNumericParameter_label_addForm" with "Test"
    And I fill in "algoNumericParameter_ref_addForm" with "bépo"
    And I select "Masse volumique de combustible" from "algoNumericParameter_family_addForm"
    And I click "Valider"
    Then the field "algoNumericParameter_ref_addForm" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Ajout, identifiant déjà utilisé
    When I fill in "algoNumericParameter_ref_addForm" with "c_n"
    And I click "Valider"
    Then the field "algoNumericParameter_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."

  @javascript
  Scenario: Edition of an algo numeric parameter scenario, correct input
  # Accès au datagrid
    Given I am on "af/edit/menu/id/4/onglet/traitement"
    And I wait for the page to finish loading
    And I open collapse "Algorithmes numériques"
    And I open collapse "Paramètres"
    Then I should see the "algoNumericParameter" datagrid
  # Vérification affichage contenu
    And the row 1 of the "algoNumericParameter" datagrid should contain:
      | label     | ref       | family                                              |
      | Paramètre | parametre | Combustion de combustible, mesuré en unité de masse |
  # Modification du libellé
    When I set "Paramètre modifié" for column "label" of row 1 of the "algoNumericParameter" datagrid with a confirmation message
  # Modification de l'identifiant, saisie correcte
    When I set "parametre_modifie" for column "ref" of row 1 of the "algoNumericParameter" datagrid with a confirmation message
  # Modification de la famille, saisie correcte
    When I set "Masse volumique de combustible" for column "family" of row 1 of the "algoNumericParameter" datagrid with a confirmation message
    Then the row 1 of the "algoNumericParameter" datagrid should contain:
      | label     | ref       | family                         |
      | Paramètre | parametre | Masse volumique de combustible |

  @javascript
  Scenario: Edition of an algo numeric parameter scenario, incorrect input
  # Accès au datagrid
    Given I am on "af/edit/menu/id/4/onglet/traitement"
    And I wait for the page to finish loading
    And I open collapse "Algorithmes numériques"
    And I open collapse "Paramètres"
    Then I should see the "algoNumericParameter" datagrid
  # Modification de l'identifiant, identifiant vide
    When I set "" for column "ref" of row 1 of the "algoNumericParameter" datagrid
    Then the following message is shown and closed: "Merci de renseigner ce champ."
  # Modification de l'identifiant, identifiant avec caractères non autorisés
    When I set "bépo" for column "ref" of row 1 of the "algoNumericParameter" datagrid
    Then the following message is shown and closed: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Modification de l'identifiant, identifiant déjà utilisé
    When I set "c_n" for column "ref" of row 1 of the "algoNumericParameter" datagrid
    Then the following message is shown and closed: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Modification de la famille, famille vide
    When I set "" for column "family" of row 1 of the "algoNumericParameter" datagrid
    Then the following message is shown and closed: "Merci de renseigner ce champ."
    And the row 1 of the "algoNumericParameter" datagrid should contain:
      | family                                              |
      | Combustion de combustible, mesuré en unité de masse |

  @javascript
  Scenario: Edition of coordinates of an algo numeric parameter scenario
  # Accès au datagrid des algos
    Given I am on "af/edit/menu/id/4/onglet/traitement"
    And I wait for the page to finish loading
    And I open collapse "Algorithmes numériques"
    And I open collapse "Paramètres"
    Then I should see the "algoNumericParameter" datagrid
  # Vérification coordonnées initiales
    When I click "Coordonnées" in the row 1 of the "algoNumericParameter" datagrid
    Then I should see the "coordinatesFixed" datagrid
    And the row 1 of the "coordinatesFixed" datagrid should contain:
      | dimension   | member  |
      | combustible | charbon |
    And I should see the "coordinatesAlgo" datagrid
    And the row 1 of the "coordinatesAlgo" datagrid should contain:
      | dimension | algo                                      |
      | processus | expression_sel_coord_param |
  # Modification élément coordonnée fixée
    When I set "Gaz naturel" for column "member" of row 1 of the "coordinatesFixed" datagrid
  # On ferme le popup pour aller fermer le message en arrière-plan
    And I click element "#algoNumericParameter_coordinates_popup .close:contains('×')"
    Then the following message is shown and closed: "Modification effectuée."
    When I click "Coordonnées" in the row 1 of the "algoNumericParameter" datagrid
    Then the row 1 of the "coordinatesFixed" datagrid should contain:
      | dimension   | member      |
      | combustible | gaz naturel |
  # Modification algo coordonnée déterminée par algorithme
    When I set "c_s_s" for column "algo" of row 1 of the "coordinatesAlgo" datagrid
  # On ferme le popup pour aller fermer le message en arrière-plan
    And I click element "#algoNumericParameter_coordinates_popup .close:contains('×')"
    Then the following message is shown and closed: "Modification effectuée."
    When I click "Coordonnées" in the row 1 of the "algoNumericParameter" datagrid
    Then the row 1 of the "coordinatesAlgo" datagrid should contain:
      | dimension | algo                   |
      | processus | c_s_s |
  # Suppression coordonnée fixée
    When I click "Supprimer" in the row 1 of the "coordinatesFixed" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    When I click "Coordonnées" in the row 1 of the "algoNumericParameter" datagrid
    And the "coordinatesFixed" datagrid should contain 0 row
  # Suppression coordonnée algo
    When I click "Supprimer" in the row 1 of the "coordinatesAlgo" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    When I click "Coordonnées" in the row 1 of the "algoNumericParameter" datagrid
    And the "coordinatesAlgo" datagrid should contain 0 row
  # Ajout coordonnée fixée
    When I click element ".btn:contains('Ajouter')[data-target='#coordinatesFixed_addPanel']"
    Then I should see the popup "Ajout d'une coordonnée fixée"
    When I click "Valider"
    Then the field "coordinatesFixed_dimension_addForm" should have error: "Merci de renseigner ce champ."
    When I select "Processus" from "coordinatesFixed_dimension_addForm"
  # Le élément lui-même n'est pas déterminé dans le popup (car dépend dynamiquement du choix de la dimension).
    And I click "Valider"
  # On ferme le popup pour aller fermer le message en arrière-plan
    And I click element "#algoNumericParameter_coordinates_popup .close:contains('×')"
    Then the following message is shown and closed: "Ajout effectué."
    When I click "Coordonnées" in the row 1 of the "algoNumericParameter" datagrid
    Then the "coordinatesFixed" datagrid should contain 1 row
    Then the row 1 of the "coordinatesFixed" datagrid should contain:
      | dimension | member |
      | Processus |        |
  # Ajout coordonnée algo
    When I click element ".btn:contains('Ajouter')[data-target='#coordinatesAlgo_addPanel']"
    Then I should see the popup "Ajout d'une coordonnée déterminée par algorithme"
    When I click "Valider"
    Then the field "coordinatesAlgo_dimension_addForm" should have error: "Merci de renseigner ce champ."
    And the field "coordinatesAlgo_algo_addForm" should have error: "Merci de renseigner ce champ."
    When I select "Combustible" from "coordinatesAlgo_dimension_addForm"
    And I select "expression_sel_coord_param" from "coordinatesAlgo_algo_addForm"
    And I click "Valider"
  # On ferme le popup pour aller fermer le message en arrière-plan
    And I click element "#algoNumericParameter_coordinates_popup .close:contains('×')"
    Then the following message is shown and closed: "Ajout effectué."
    When I click "Coordonnées" in the row 1 of the "algoNumericParameter" datagrid
    Then the "coordinatesAlgo" datagrid should contain 1 row
    Then the row 1 of the "coordinatesAlgo" datagrid should contain:
      | dimension   | algo                                      |
      | Combustible | expression_sel_coord_param |

  @javascript
  Scenario: Influence of a change of family on coordinates of an algo numeric parameter scenario
  # Accès au datagrid des algos
    Given I am on "af/edit/menu/id/4/onglet/traitement"
    And I wait for the page to finish loading
    And I open collapse "Algorithmes numériques"
    And I open collapse "Paramètres"
    Then I should see the "algoNumericParameter" datagrid
  # Les coordonnées initiales sont remplies, cf scénario précédent
  # On modifie la famille
    When I set "Masse volumique de combustible" for column "family" of row 1 of the "algoNumericParameter" datagrid with a confirmation message
  # On vérifie que les coordonnées ont été supprimées (même celles éventuellement communes)
    When I click "Coordonnées" in the row 1 of the "algoNumericParameter" datagrid
    Then the "coordinatesFixed" datagrid should contain 0 row
    And the "coordinatesAlgo" datagrid should contain 0 row
  # On revient à la famille précédente
    When I click element "#algoNumericParameter_coordinates_popup .close:contains('×')"
    And I set "Combustion de combustible, mesuré en unité de masse" for column "family" of row 1 of the "algoNumericParameter" datagrid with a confirmation message
    And I click "Coordonnées" in the row 1 of the "algoNumericParameter" datagrid
    Then the "coordinatesFixed" datagrid should contain 0 row
    And the "coordinatesAlgo" datagrid should contain 0 row

  @javascript
  Scenario: Deletion of an algo numeric parameter scenario
    Given I am on "af/edit/menu/id/4/onglet/traitement"
    And I wait for the page to finish loading
    And I open collapse "Algorithmes numériques"
    And I open collapse "Paramètres"
    Then I should see the "algoNumericParameter" datagrid
    And the "algoNumericParameter" datagrid should contain 2 row
  # Suppression sans obstacle
    When I click "Supprimer" in the row 1 of the "algoNumericParameter" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    And the "algoNumericParameter" datagrid should contain 1 row
