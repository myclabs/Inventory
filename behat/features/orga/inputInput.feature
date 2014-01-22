@dbFull
Feature: Organization input input feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Global administrator direct access to input scenario
    Given I am on "orga/cell/input/idCell/28/fromIdCell/1"
    And I wait for the page to finish loading
    Then I should see "Saisie 2012 | Annecy | Énergie"

  @javascript
  Scenario: Display of existing input with correct values and uncertainties scenario
    Given I am on "orga/cell/view/idCell/1"
    And I wait for the page to finish loading
    And I click element "legend[data-target='#granularity8']"
    And I click "2012 | Annecy | Énergie"
    Then I should see "Saisie 2012 | Annecy | Énergie"
    And the "quantite_combustible" field should contain "10"
    And the "percentquantite_combustible" field should contain "15"
  # Vérification dans l'onglet "Détails calculs" que c'est bien cette valeur et cette incertitude qu'on devrait voir
    When I open tab "Détails calculs"
    And I open collapse "Formulaire maître"
    And I open collapse "emissions_combustion"
    And I open collapse "quantite_combustible"
    Then I should see "Valeur : 10 t ± 15 %"


  @javascript
  Scenario: Display of existing input for a closed inventory scenario
    Given I am on "orga/cell/view/idCell/1"
    And I wait for the page to finish loading
    And I click element "legend[data-target='#granularity7']"myc-53n53
  # Vérification contenu datagrid
    Then I should see "Saisie terminée" in the "div.cell[data-tag='/1-annee:2012/&/1-zone:europe/1-pays:france/2-site:grenoble/&/2-marque:marque_b/2-site:grenoble/']" element
    Then I should see "100 %" in the "div.cell[data-tag='/1-annee:2012/&/1-zone:europe/1-pays:france/2-site:grenoble/&/2-marque:marque_b/2-site:grenoble/'] div.progress-success" element
  # Accès à la saisie, inventaire clôturé
    When I click "2012 | Grenoble"
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
    And I fill in "addContent" with "h1. Un _chouette_ commentaire."
    And I click element "#Ajouter"
    Then I should see "Un chouette commentaire."
  # Retour à l'onglet "Saisie" et quitter la page
    When I open tab "Saisie"
    And I click "Quitter"
    Then I should see "Workspace avec données"
    And I should see "Vue globale"

