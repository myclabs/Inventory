@dbFull
Feature: AF list edit feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of an AF, correct input
    Given I am on "af/library/view/id/1"
    And I wait for the page to finish loading
    Then I should see the "listAF" datagrid
    And the row 1 of the "listAF" datagrid should contain:
      | category                          | label                                               | ref                                |
      | Catégorie contenant un formulaire | Combustion de combustible, mesuré en unité de masse | combustion_combustible_unite_masse |
  # Ajout, saisie correcte
    When I click "Ajouter un formulaire"
    Then I should see the popup "Ajout d'un formulaire"
    When I select "Catégorie contenant un formulaire" from "listAF_category_addForm"
    And I fill in "listAF_label_addForm" with "Test"
    And I fill in "listAF_ref_addForm" with "test"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
    And the "listAF" datagrid should contain a row:
      | category                          | label | ref  |
      | Catégorie contenant un formulaire | Test  | test |

  @javascript @readOnly
  Scenario: Creation of an AF, incorrect input
    Given I am on "af/library/view/id/1"
    And I wait for the page to finish loading
    When I click "Ajouter un formulaire"
    Then I should see the popup "Ajout d'un formulaire"
  # Aucun champ rempli
    When I click "Valider"
  # La catégorie est remplie par défaut
  # Then the field "listAF_category_addForm" should have error: "Merci de renseigner ce champ."
    Then the field "listAF_label_addForm" should have error: "Merci de renseigner ce champ."
    And the field "listAF_ref_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout, identifiant caractères non autorisés
    When I select "Catégorie contenant un formulaire" from "listAF_category_addForm"
    And I fill in "listAF_label_addForm" with "Test"
    And I fill in "listAF_ref_addForm" with "bépo"
    And I click "Valider"
    Then the field "listAF_ref_addForm" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Ajout, identifiant déjà utilisé
    When I fill in "listAF_ref_addForm" with "combustion_combustible_unite_masse"
    And I click "Valider"
    Then the field "listAF_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."

  @javascript
  Scenario: Edition of an AF in AF list, correct input
    Given I am on "af/library/view/id/1"
    And I wait for the page to finish loading
    Then I should see the "listAF" datagrid
    And the row 1 of the "listAF" datagrid should contain:
      | category                          | label                                               | ref                                |
      | Catégorie contenant un formulaire | Combustion de combustible, mesuré en unité de masse | combustion_combustible_unite_masse |
  # Modifications (catégorie, libellé, identifiant)
    When I set "Catégorie vide" for column "category" of row 1 of the "listAF" datagrid with a confirmation message
    And I set "Libellé modifié" for column "label" of row 1 of the "listAF" datagrid with a confirmation message
    And I set "identifiant_modifie" for column "ref" of row 1 of the "listAF" datagrid with a confirmation message
    Then the row 1 of the "listAF" datagrid should contain:
      | category       | label           | ref                 |
      | Catégorie vide | Libellé modifié | identifiant_modifie |

  @javascript @readOnly
  Scenario: Edition of an AF in AF list, incorrect input
    Given I am on "af/library/view/id/1"
    And I wait for the page to finish loading
    Then I should see the "listAF" datagrid
  # Modification du libellé, libellé vide
    When I set "" for column "label" of row 1 of the "listAF" datagrid
    Then the following message is shown and closed: "Merci de renseigner ce champ."
  # Modification de l'identifiant, identifiant vide
    When I set "" for column "ref" of row 1 of the "listAF" datagrid
    Then the following message is shown and closed: "Merci de renseigner ce champ."
  # Modification de l'identifiant, identifiant avec caractères non autorisés
    When I set "bépo" for column "ref" of row 1 of the "listAF" datagrid
    Then the following message is shown and closed: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Modification de l'identifiant, identifiant déjà utilisé
    When I set "formulaire_test" for column "ref" of row 1 of the "listAF" datagrid
    Then the following message is shown and closed: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
    And the row 1 of the "listAF" datagrid should contain:
      | label                                               | ref                                |
      | Combustion de combustible, mesuré en unité de masse | combustion_combustible_unite_masse |

  @javascript @readOnly
  Scenario: Links towards configuration and test views, from AF list
    Given I am on "af/library/view/id/1"
    And I wait for the page to finish loading
    Then I should see the "listAF" datagrid
    And the row 1 of the "listAF" datagrid should contain:
      | category                          | label                                               |
      | Catégorie contenant un formulaire | Combustion de combustible, mesuré en unité de masse |
  # Clic sur "Informations générales"
    When I click "Configuration" in the row 1 of the "listAF" datagrid
  # Vérification qu'on est bien sur la page "Configuration"
    And I open tab "Contrôle"
    Then I should see "Combustion de combustible, mesuré en unité de masse"
  # Retour à la liste des formulaires, clic sur "Test"
    When I am on "af/af/list"
    And I wait for the page to finish loading
    And I click "Test" in the row 1 of the "listAF" datagrid
  # Vérification qu'on est bien sur la page "Test"
    Then I should see "Nature du combustible"

  @javascript @readOnly
  Scenario: Filters on AF list
    Given I am on "af/library/view/id/1"
    And I wait for the page to finish loading
    Then I should see the "listAF" datagrid
    And the "listAF" datagrid should contain 8 row
    And I open collapse "Filtres"
  # Filtre sur le libellé
    And I fill in "listAF_label_filterForm" with "Formulaire vide"
    And I click "Filtrer"
    Then the "listAF" datagrid should contain 1 row
    And the row 1 of the "listAF" datagrid should contain:
      | label           | ref             |
      | Formulaire vide | formulaire_vide |
  # Clic sur "Réinitialiser"
    When I open collapse "Filtres"
    And I click "Réinitialiser"
    Then the "listAF" datagrid should contain 8 row
  # Filtre sur l'identifiant
    When I open collapse "Filtres"
    And I fill in "listAF_ref_filterForm" with "_test"
    And I click "Filtrer"
    Then the "listAF" datagrid should contain 1 row
    And the row 1 of the "listAF" datagrid should contain:
      | label           | ref             |
      | Formulaire test | formulaire_test |
  # Filtre sur les deux combinés
    When I open collapse "Filtres"
    And I click "Réinitialiser"
    And I open collapse "Filtres"
    And I fill in "listAF_label_filterForm" with "Formulaire"
    And I fill in "listAF_ref_filterForm" with "_vide"
    And I click "Filtrer"
    Then the "listAF" datagrid should contain 1 row
  # Alors que…
    When I open collapse "Filtres"
    And I click "Réinitialiser"
    And I open collapse "Filtres"
    And I fill in "listAF_label_filterForm" with "Formulaire"
    And I click "Filtrer"
    Then the "listAF" datagrid should contain 5 row

  @javascript
  Scenario: Deletion of an AF form from AF list
    #6193 	Dans le jeu de données "full.sql", impossible de supprimer le formulaire "Formulaire test"
    Given I am on "af/library/view/id/1"
    And I wait for the page to finish loading
    Then I should see the "listAF" datagrid
    And the row 1 of the "listAF" datagrid should contain:
      | label             |
      | Combustion de combustible, mesuré en unité de masse |
    And the row 2 of the "listAF" datagrid should contain:
      | label             |
      | Données générales |
    And the row 3 of the "listAF" datagrid should contain:
      | label                                               |
      | Formulaire avec sous-formulaires |
    And the row 4 of the "listAF" datagrid should contain:
      | label                                               |
      | Formulaire test |
    And the row 5 of the "listAF" datagrid should contain:
      | label                                               |
      | Formulaire avec tout type de champ |
    And the row 6 of the "listAF" datagrid should contain:
      | label                                               |
      | Formulaire avec sous-formulaire répété contenant tout type de champ |
    And the row 7 of the "listAF" datagrid should contain:
      | label                                               |
      | Formulaire vide |
    And the row 8 of the "listAF" datagrid should contain:
      | label                                               |
      | Forfait émissions en fonction de la marque |
  # Tentative de suppression, formulaire avec saisies
    When I click "Supprimer" in the row 1 of the "listAF" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Ce formulaire ne peut pas être supprimé car des saisies y sont associées."
  # Tentative de suppression, formulaire avec saisies
    When I click "Supprimer" in the row 2 of the "listAF" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Ce formulaire ne peut pas être supprimé car des saisies y sont associées."
  # 3 et 4 peuvent être supprimés (voir plus loin)
  # Tentative de suppression, formulaire utilisé comme sous-formulaire (répété)
    When I click "Supprimer" in the row 5 of the "listAF" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Ce formulaire ne peut pas être supprimé, car il est appelé en tant que sous-formulaire par un autre formulaire."
  # Tentative de suppression, formulaire utilisé pour des organisations
    When I click "Supprimer" in the row 6 of the "listAF" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Ce formulaire ne peut pas être supprimé car il est utilisé par des organisations."
  # 7 peut être supprimé (voir plus loin)
  # Tentative de suppression, formulaire utilisé pour des organisations
    When I click "Supprimer" in the row 8 of the "listAF" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Ce formulaire ne peut pas être supprimé car il est utilisé par des organisations."
  # Suppression sans obstacle, formulaire vide
    When I click "Supprimer" in the row 7 of the "listAF" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
  # Suppression sans obstacle, formulaire test (plein)
    When I click "Supprimer" in the row 4 of the "listAF" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
  # Suppression sans obstacle, formulaire avec sous-formulaires
    When I click "Supprimer" in the row 3 of the "listAF" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
