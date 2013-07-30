@dbFull
Feature: AF change component value interaction feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of a change component value interaction scenario
    Given I am on "af/edit/menu/id/4/onglet/interaction"
    And I wait for the page to finish loading
    And I open collapse "Assignations de valeurs à des champs"
    Then I should see the "actionsSetValue" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'une action d'assignation de valeur à un champ"
  # Remarque : composant cible et mode de détermination sont pré-remplis, et la condition est facultative (peut être saisie ultérieurement).
  # Donc l'ajout sans rien préciser ne pose pas de problème particulier.
  #
  # Ajout action d'assignation à un champ numérique (disons sans condition renseignée)
    When I select "Champ numérique" from "actionsSetValue_targetComponent_addForm"
    And I select "Valeur fixée" from "actionsSetValue_type_addForm"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
    # TODO : vérifier que la liste déroulante des champs proposés ne propose pas de champs de sélection multiple…
  # Ajout action d'assignation à un champ de sélection simple (disons avec condition renseignée)
    When I select "Champ sélection simple" from "actionsSetValue_targetComponent_addForm"
    And I select "Valeur fixée" from "actionsSetValue_type_addForm"
    And I select "condition_elementaire_interactions_utilisee_action_setvalue" from "actionsSetValue_condition_addForm"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Ajout action d'assignation à un champ booléen (disons avec condition renseignée)
    When I select "Champ booléen" from "actionsSetValue_targetComponent_addForm"
    And I select "Valeur fixée" from "actionsSetValue_type_addForm"
    And I select "condition_elementaire_interactions_utilisee_action_setvalue" from "actionsSetValue_condition_addForm"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Vérification contenu datagrid
    And the row 4 of the "actionsSetValue" datagrid should contain:
      | targetComponent | type         | value | condition |
      | Champ numérique | Valeur fixée |       |           |
    And the row 5 of the "actionsSetValue" datagrid should contain:
      | targetComponent        | type         | value | condition                                                   |
      | Champ sélection simple | Valeur fixée |       | condition_elementaire_interactions_utilisee_action_setvalue |
    And the row 6 of the "actionsSetValue" datagrid should contain:
      | targetComponent | type         | value   | condition                                                   |
      | Champ booléen   | Valeur fixée | Décoché | condition_elementaire_interactions_utilisee_action_setvalue |


  @javascript
  Scenario: Edition of a change component value interaction scenario (change condition)
    Given I am on "af/edit/menu/id/4/onglet/interaction"
    And I wait for the page to finish loading
    And I open collapse "Assignations de valeurs à des champs"
    Then I should see the "actionsSetValue" datagrid
  # Vérification contenu initial
    And the row 1 of the "actionsSetValue" datagrid should contain:
      | targetComponent                | type         | condition                                                   |
      | Champ numérique cible setvalue | Valeur fixée | condition_elementaire_interactions_utilisee_action_setvalue |


  # Modification condition
    When I set "condition_elementaire_interactions_utilisee_action_setState" for column "condition" of row 1 of the "actionsSetValue" datagrid with a confirmation message
    Then the row 1 of the "actionsSetValue" datagrid should contain:
      | targetComponent                | type         | condition                                                   |
      | Champ numérique cible setvalue | Valeur fixée | condition_elementaire_interactions_utilisee_action_setState |

  @javascript
  Scenario: Edition of a change component value interaction scenario (change numeric value)
    Given I am on "af/edit/menu/id/4/onglet/interaction"
    And I wait for the page to finish loading
    And I open collapse "Assignations de valeurs à des champs"
    Then the row 1 of the "actionsSetValue" datagrid should contain:
      | targetComponent                | value             |
      | Champ numérique cible setvalue | 1 234,56789 ± 5 % |
  # Popup d'édition
    When I click "Éditer" in the row 1 of the "actionsSetValue" datagrid
    Then I should see the popup "Édition de la valeur ou de l'algorithme à assigner"
  # Saisie valeur non valide
    When I fill in "numericValue" with "auie"
    And I click "Enregistrer"
    Then the following message is shown and closed: "La quantité saisie n'a pas pu être interprétée, merci de corriger (en français merci d'utiliser la virgule comme séparateur décimal)."
  # Édition pas en Ajax, donc on doit rouvrir le collapse
    When I open collapse "Assignations de valeurs à des champs"
  # Saisie valeur séparateur décimal non valide
    And I click "Éditer" in the row 1 of the "actionsSetValue" datagrid
    And I fill in "numericValue" with "1234.56789"
    And I click "Enregistrer"
    Then the following message is shown and closed: "La quantité saisie n'a pas pu être interprétée, merci de corriger (en français merci d'utiliser la virgule comme séparateur décimal)."
  # Édition pas en Ajax, donc on doit rouvrir le collapse
    When I open collapse "Assignations de valeurs à des champs"
  # Saisie valeur séparateur décimal non valide
    And I click "Éditer" in the row 1 of the "actionsSetValue" datagrid
    And I fill in "numericValue" with "1234,56789"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Modification effectuée."
  # Édition pas en Ajax, donc on doit rouvrir le collapse
    When I open collapse "Assignations de valeurs à des champs"
    Then the row 1 of the "actionsSetValue" datagrid should contain:
      | value       |
      | 1 234,56789 |
    When I click "Éditer" in the row 1 of the "actionsSetValue" datagrid
    And I fill in "numericValue" with ""
    And I fill in "numericUncertainty" with "15.9"
    And I click "Enregistrer"
    Then the following message is shown and closed: "La quantité saisie n'a pas pu être interprétée, merci de corriger (en français merci d'utiliser la virgule comme séparateur décimal)."
  # Édition pas en Ajax, donc on doit rouvrir le collapse
    When I open collapse "Assignations de valeurs à des champs"
    And I click "Éditer" in the row 1 of the "actionsSetValue" datagrid
    And I fill in "numericValue" with ""
    And I fill in "numericUncertainty" with "15,9"
    And I click "Enregistrer"
  # Édition pas en Ajax, donc on doit rouvrir le collapse
    When I open collapse "Assignations de valeurs à des champs"
    Then the row 1 of the "actionsSetValue" datagrid should contain:
      | value       |
      | ± 15 % |

  @javascript
  Scenario: Edition of a change component value interaction scenario (change single option value)
    Given I am on "af/edit/menu/id/4/onglet/interaction"
    And I wait for the page to finish loading
    And I open collapse "Assignations de valeurs à des champs"
    Then the row 3 of the "actionsSetValue" datagrid should contain:
      | targetComponent                                      | value |
      | Champ sélection simple cible d'une action "setValue" |       |
  # Popup d'édition
    When I click "Éditer" in the row 3 of the "actionsSetValue" datagrid
    Then I should see the popup "Édition de la valeur ou de l'algorithme à assigner"
    When I select "Option 1" from "selectOptionValue"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Modification effectuée."
  # Édition pas en Ajax, donc on doit rouvrir le collapse
    When I open collapse "Assignations de valeurs à des champs"
    Then the row 3 of the "actionsSetValue" datagrid should contain:
      | targetComponent                             | value   |
      | Champ booléen cible d'une action "setValue" | Option 1 |

  @javascript
  Scenario: Edition of a change component value interaction scenario (change boolean value)
    Given I am on "af/edit/menu/id/4/onglet/interaction"
    And I wait for the page to finish loading
    And I open collapse "Assignations de valeurs à des champs"
    Then the row 2 of the "actionsSetValue" datagrid should contain:
      | targetComponent                             | value |
      | Champ booléen cible d'une action "setValue" | Coché |
  # Popup d'édition
    When I click "Éditer" in the row 2 of the "actionsSetValue" datagrid
    Then I should see the popup "Édition de la valeur ou de l'algorithme à assigner"
    When I check "Décoché"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Modification effectuée."
  # Édition pas en Ajax, donc on doit rouvrir le collapse
    When I open collapse "Assignations de valeurs à des champs"
    Then the row 2 of the "actionsSetValue" datagrid should contain:
      | targetComponent                             | value   |
      | Champ booléen cible d'une action "setValue" | Décoché |

  @javascript
  Scenario: Deletion of a change component value interaction scenario
    Given I am on "af/edit/menu/id/4/onglet/interaction"
    And I wait for the page to finish loading
    And I open collapse "Assignations de valeurs à des champs"
    Then I should see the "actionsSetValue" datagrid
    And the "actionsSetValue" datagrid should contain 3 row
  # Suppression sans obstacle
    When I click "Supprimer" in the row 1 of the "actionsSetValue" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    And the "actionsSetValue" datagrid should contain 2 row