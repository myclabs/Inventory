@dbFull
Feature: AF numeric constant algo feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of a numeric constant algo scenario
  # Accès au datagrid
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Traitement"
    And I open collapse "Algorithmes numériques"
    And I open collapse "Constantes"
    Then I should see the "algoNumericConstant" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un champ numérique"
  # Ajout, identifiant vide
    When I click "Valider"
    Then the field "algoNumericConstant_ref_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout, identifiant avec caractères non autorisés
    When I fill in "algoNumericConstant_ref_addForm" with "bépo"
    And I click "Valider"
    Then the field "algoNumericConstant_ref_addForm" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Ajout, identifiant déjà utilisé
    When I fill in "algoNumericConstant_ref_addForm" with "champ_numerique"
    And I click "Valider"
    Then the field "algoNumericConstant_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Ajout, identifiant et libellé corrects, unité vide, valeur et incertitudes vides
    When I fill in "algoNumericConstant_label_addForm" with "AAA"
    And I fill in "algoNumericConstant_ref_addForm" with "aaa"
    And I click "Valider"
    Then the field "algoNumericConstant_unit_addForm" should have error: "Merci de saisir un identifiant d'unité valide."
    And the field "algoNumericConstant_value_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout, identifiant et libellé corrects, unité incorrecte, valeur et incertitude vides
    When I fill in "algoNumericConstant_unit_addForm" with "auie"
    And I click "Valider"
    Then the field "algoNumericConstant_unit_addForm" should have error: "Merci de saisir un identifiant d'unité valide."
  # Ajout, identifiant et libellé corrects, unité correcte, incertitude vide, valeur incorrecte, incertitude incorrecte
    When I fill in "algoNumericConstant_unit_addForm" with "t_co2e.passager^-1.km^-1"
    And I fill in "algoNumericConstant_value_addForm" with "12345.6789"
    And I fill in "algoNumericConstant_uncertainty_addForm" with "auie"
    And I click "Valider"
    Then the field "algoNumericConstant_unit_addForm" should have error: "La quantité saisie n'a pas pu être interprétée, merci de corriger (en français merci d'utiliser la virgule comme séparateur décimal)."
    And the field "algoNumericConstant_uncertainty_addForm" should have error: "La quantité saisie n'a pas pu être interprétée, merci de corriger (en français merci d'utiliser la virgule comme séparateur décimal)."
  # Ajout, saisie correcte (incertitude vide)
    When I fill in "algoNumericConstant_value_addForm" with "12345,6789"
    And I fill in "algoNumericConstant_uncertainty_addForm" with ""
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Algos ordonnés suivant l'ordre de création (?)
    And the row 2 of the "algoNumericConstant" datagrid should contain:
      | label | ref | unit                   | value      | uncertainty |
      | AAA   | aaa | t équ. CO2/passager.km | 12345,6789 | 0           |

  @javascript
  Scenario: Edition of an numeric constant algo scenario
  # Accès au datagrid
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Traitement"
    And I open collapse "Algorithmes numériques"
    And I open collapse "Constantes"
    Then I should see the "algoNumericConstant" datagrid
  # Affichage contenu
    And the row 1 of the "algoNumericConstant" datagrid should contain:
      | label     | ref       | unit                   | value       | uncertainty |
      | Constante | constante | t équ. CO2/passager.km | 12 345,6789 | 5           |
  # Modification du libellé
    When I set "Constante modifiée" for column "label" of row 1 of the "algoNumericConstant" datagrid with a confirmation message
  # Modification de l'identifiant, identifiant vide
    When I set "" for column "ref" of row 1 of the "algoNumericConstant" datagrid
    Then the following message is shown and closed: "Merci de renseigner ce champ."
  # Modification de l'identifiant, identifiant avec caractères non autorisés
    When I set "bépo" for column "ref" of row 1 of the "algoNumericConstant" datagrid
    Then the following message is shown and closed: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Modification de l'identifiant, identifiant déjà utilisé
    When I set "champ_numerique" for column "ref" of row 1 of the "algoNumericConstant" datagrid
    Then the following message is shown and closed: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Modification de l'identifiant, saisie correcte
    When I set "constante_modifiee" for column "ref" of row 1 of the "algoNumericConstant" datagrid with a confirmation message
  # Modification de l'unité, saisie vide
    When I set "" for column "unit" of row 1 of the "algoNumericConstant" datagrid
    Then the following message is shown and closed: "Merci de saisir un identifiant d'unité valide."
  # Modification de l'unité, saisie invalide
    When I set "auie" for column "unit" of row 1 of the "algoNumericConstant" datagrid
    Then the following message is shown and closed: "Merci de saisir un identifiant d'unité valide."
  # Modification de l'unité, saisie correcte
    When I set "m" for column "unit" of row 1 of the "algoNumericConstant" datagrid with a confirmation message
  # Modification de la valeur, saisie vide
    When I set "" for column "value" of row 1 of the "algoNumericConstant" datagrid
    Then the following message is shown and closed: "La quantité saisie n'a pas pu être interprétée, merci de corriger (en français merci d'utiliser la virgule comme séparateur décimal)."
  # Modification de la valeur, saisie invalide
    When I set "1" for column "value" of row 1 of the "algoNumericConstant" datagrid
    Then the following message is shown and closed: "La quantité saisie n'a pas pu être interprétée, merci de corriger (en français merci d'utiliser la virgule comme séparateur décimal)."
  # Modification de l'incertitude, saisie incorrecte
    When I set "auieae" for column "uncertainty" of row 1 of the "algoNumericConstant" datagrid
    Then the following message is shown and closed: "La quantité saisie n'a pas pu être interprétée, merci de corriger (en français merci d'utiliser la virgule comme séparateur décimal)."
  # Modification de l'incertitude, saisie vide (correcte)
    When I set "" for column "uncertainty" of row 1 of the "algoNumericConstant" datagrid with a confirmation message
  # Affichage contenu modifié
    Then the row 1 of the "algoNumericConstant" datagrid should contain:
      | label              | ref                | unit | value | uncertainty |
      | Constante modifiée | constante_modifiee | m    | 1     | 0           |
  # Modification de l'incertitude, saisie non vide (correcte)
    When I set "5,9" for column "uncertainty" of row 1 of the "algoNumericConstant" datagrid with a confirmation message
    Then the row 1 of the "algoNumericConstant" datagrid should contain:
      | uncertainty |
      | 5           |

  @javascript
  Scenario: Deletion of an numeric constant algo scenario
  # Accès au datagrid
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Traitement"
    And I open collapse "Algorithmes numériques"
    And I open collapse "Constantes"
    Then I should see the "algoNumericConstant" datagrid
    And the "algoNumericConstant" datagrid should contain 1 row
  # Suppression sans obstacle
    When I click "Supprimer" in the row 1 of the "algoNumericConstant" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    And the "algoNumericConstant" datagrid should contain 0 row
