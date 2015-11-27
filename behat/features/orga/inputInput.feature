@dbFull
Feature: Organization input input feature

  Background:
    Given I am logged in

  @javascript @readOnly
  Scenario: Global administrator direct access to input scenario
    Given I am on "orga/cell/input/cell/39/fromCell/1"
    And I wait for the page to finish loading
    Then I should see "Saisie 2012 | Annecy | Énergie"

  @javascript @readOnly
  Scenario: Display of existing input with correct values and uncertainties scenario
    Given I am on "orga/cell/view/cell/1"
    And I wait for the page to finish loading
  # On doit fermer le premier volet sinon le clic suivant est trop bas dans la page
    And I click "Année | Site Fonctionnalités disponibles à ce niveau : Suivi des collectes, Saisies"
  # Accès à la saisie
    And I go input the "/1-annee:1-2012/&/1-zone:europe/1-pays:france/2-site:annecy/&/2-marque:marque_a/2-site:annecy/&/3-categorie:1-energie/" cell
    And I switch to the new tab
    Then I should see "Saisie 2012 | Annecy | Énergie"
    And the "quantite_combustible" field should contain "10"
    And the "percentquantite_combustible" field should contain "15"
  # Vérification dans l'onglet "Détails calculs" que c'est bien cette valeur et cette incertitude qu'on devrait voir
    When I open tab "Détails calculs"
  # Ajout attente pour que le test passe en local sur machine Emmanuel
    And I wait 5 seconds
    And I open collapse "Formulaire maître"
    And I open collapse "emissions_combustion"
    And I open collapse "quantite_combustible"
    Then I should see "Valeur : 10 t ± 15 %"


  @javascript @readOnly
  Scenario: Display of existing input for a closed inventory scenario
    Given I am on "orga/cell/view/cell/1"
    And I wait for the page to finish loading
  # Cliquer sur le bouton "Réinitialiser" pour la granularité Année|Site, pour faire apparaître les collectes clôturées
    And I click element "div[id='granularity8'] button.reset"
  # Vérification contenu datagrid
    Then the "/1-annee:1-2012/&/1-zone:europe/1-pays:france/2-site:grenoble/&/2-marque:marque_b/2-site:grenoble/" cell input status should be "statusFinished"
  # Accès à la saisie, inventaire clôturé
    When I go input the "/1-annee:1-2012/&/1-zone:europe/1-pays:france/2-site:grenoble/&/2-marque:marque_b/2-site:grenoble/" cell
    And I switch to the new tab
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
    And I fill in "newComment" with "h1. Un _chouette_ commentaire."
    And I click "Ajouter un commentaire"
    And I wait for 2 seconds
    Then I should see "Un chouette commentaire."
  # Retour à l'onglet "Saisie" et quitter la page
    When I open tab "Saisie"
    And I click "Quitter"
    Then I should see "Workspace avec données"
    And I should see "Vue globale"

