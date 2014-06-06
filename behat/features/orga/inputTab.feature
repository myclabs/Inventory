@dbFull
Feature: Organization input tab feature

  Background:
    Given I am logged in

  @javascript @readOnly
  Scenario: Filter on organization members in Input tab
  # Accès à l'onglet "Saisies"
    Given I am on "orga/cell/view/idCell/1"
    And I wait for the page to finish loading
    Then I should see "4 / 6" in the "#granularity8 span.granularity-info" element
  # Filtre sur le site "Annecy"
    When I select "Annecy" from "granularity8_axissite"
    And I wait 2 seconds
    Then I should see "2 / 6" in the "#granularity8 span.granularity-info" element
  # Bouton "Réinitialiser"
    When I click element "#granularity8 button.reset"
    And I wait 2 seconds
    Then I should see "6 / 6" in the "#granularity8 span.granularity-info" element

  @javascript @readOnly
  Scenario: Display of the various columns (inventory status, input progress, input status)
    Given I am on "orga/cell/view/idCell/1"
    And I wait for the page to finish loading
  # Cas inventaire en cours, saisie complète
    Then I should see the "/1-annee:2012/&/1-zone:europe/1-pays:france/2-site:annecy/&/2-marque:marque_a/2-site:annecy/" cell
    And the "/1-annee:2012/&/1-zone:europe/1-pays:france/2-site:annecy/&/2-marque:marque_a/2-site:annecy/" cell inventory status should be "active"
    And the "/1-annee:2012/&/1-zone:europe/1-pays:france/2-site:annecy/&/2-marque:marque_a/2-site:annecy/" cell input status should be "statusComplete"
  # Cas inventaire en cours, saisie incomplète / saisie terminée
    Then I should see the "/1-annee:2012/&/1-zone:europe/1-pays:france/2-site:annecy/&/2-marque:marque_a/2-site:annecy/&/3-categorie:energie/" cell
    And the "/1-annee:2012/&/1-zone:europe/1-pays:france/2-site:annecy/&/2-marque:marque_a/2-site:annecy/&/3-categorie:energie/" cell input status should be "statusFinished"
    Then I should see the "/1-annee:2012/&/1-zone:europe/1-pays:france/2-site:annecy/&/2-marque:marque_a/2-site:annecy/&/3-categorie:test_affichage/" cell
    And the "/1-annee:2012/&/1-zone:europe/1-pays:france/2-site:annecy/&/2-marque:marque_a/2-site:annecy/&/3-categorie:test_affichage/" cell input status should be "statusInputIncomplete"
  # Cas inventaire non lancé, inventaire clôturé
    And I click element "div[id='granularity8'] button.reset"
    Then I should see the "/1-annee:2012/&/1-zone:europe/1-pays:france/2-site:grenoble/&/2-marque:marque_b/2-site:grenoble/" cell
    And the "/1-annee:2012/&/1-zone:europe/1-pays:france/2-site:grenoble/&/2-marque:marque_b/2-site:grenoble/" cell inventory status should be "closed"
    And the "/1-annee:2012/&/1-zone:europe/1-pays:france/2-site:grenoble/&/2-marque:marque_b/2-site:grenoble/" cell input status should be "statusFinished"
    Then I should see the "/1-annee:2013/&/1-zone:europe/1-pays:france/2-site:grenoble/&/2-marque:marque_b/2-site:grenoble/" cell
    And the "/1-annee:2013/&/1-zone:europe/1-pays:france/2-site:grenoble/&/2-marque:marque_b/2-site:grenoble/" cell inventory status should be "notLaunched"
    And the "/1-annee:2013/&/1-zone:europe/1-pays:france/2-site:grenoble/&/2-marque:marque_b/2-site:grenoble/" cell input status should be "statusNotStarted"

