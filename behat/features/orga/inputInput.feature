@dbFull
Feature: Organization input input feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Display of existing input for a closed inventory
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open collapse "Année | Site"
  # Vérification contenu datagrid
    Then I should see the "aFGranularity1Input7" datagrid
    And the row 3 of the "aFGranularity1Input7" datagrid should contain:
      | annee | site     | inventoryStatus | advancementInput | stateInput      |
      | 2012  | Grenoble | Fermé           | 100%             | Saisie terminée |
  # Accès à la saisie, inventaire clôturé
    When I click "Cliquer pour accéder" in the row 3 of the "aFGranularity1Input7" datagrid
  # TODO : tester le fait que le champ apparaît en consultation.
    Then I should not see "Aperçu des résultats"
    And I should not see "Enregistrer"
  # Accès à l'onglet "Résultats"
    When I open tab "Résultats"
    Then I should see "Chiffre d'affaires"
  # Accès à l'onglet "Détails calculs"
    When I open tab "Détails calculs"
    And I open collapse "Formulaire maître"
    And I open collapse "chiffre_affaire"
    Then I should see "Valeur : 10 k€ ± 15 %"
  # Accès à l'onglet "Commentaires", dépôt d'un commentaire
    When I open tab "Commentaires"
    Then I should see "Aucun commentaire."
    When I click "Ajouter un commentaire"
    And I fill in "content" with "h1. Un _chouette_ commentaire."
    And I click element "#Ajouter"
    Then I should see "Un chouette commentaire."
  # Retour à l'onglet "Saisie" et quitter la page
    When I open tab "Saisie"
    And I click "Quitter"
    Then I should see "Vue globale Organisation avec données"

