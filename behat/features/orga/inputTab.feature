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
    Then I should see "2 / 6" in the "#granularity8 span.granularity-info" element
  # Bouton "Réinitialiser"
    When I click element "div[id='granularity8'] button.reset"
    Then I should see "6 / 6" in the "#granularity8 span.granularity-info" element

  @javascript @readOnly
  Scenario: Display of the various columns (inventory status, input progress, input status)
    Given I am on "orga/cell/view/idCell/1"
    And I wait for the page to finish loading
  # Cas inventaire en cours, saisie complète
    Then I should see "2012 | Annecy" in the "div.cell[data-tag='/1-annee:2012/&/1-zone:europe/1-pays:france/2-site:annecy/&/2-marque:marque_a/2-site:annecy/']" element
    And I should see "Collecte en cours" in the "div.cell[data-tag='/1-annee:2012/&/1-zone:europe/1-pays:france/2-site:annecy/&/2-marque:marque_a/2-site:annecy/'] div.inventory-status" element
    And I should see "Saisie complète" in the "div.cell[data-tag='/1-annee:2012/&/1-zone:europe/1-pays:france/2-site:annecy/&/2-marque:marque_a/2-site:annecy/'] div.input-status" element
  # Cas inventaire en cours, saisie incomplète / saisie terminée
    Then I should see "2012 | Annecy | Énergie" in the "div.cell[data-tag='/1-annee:2012/&/1-zone:europe/1-pays:france/2-site:annecy/&/2-marque:marque_a/2-site:annecy/&/3-categorie:energie/']" element
    And I should see "Saisie terminée" in the "div.cell[data-tag='/1-annee:2012/&/1-zone:europe/1-pays:france/2-site:annecy/&/2-marque:marque_a/2-site:annecy/&/3-categorie:energie/'] div.input-status" element
    Then I should see "2012 | Annecy | Test affichage" in the "div.cell[data-tag='/1-annee:2012/&/1-zone:europe/1-pays:france/2-site:annecy/&/2-marque:marque_a/2-site:annecy/&/3-categorie:test_affichage/']" element
    And I should see "Saisie incomplète" in the "div.cell[data-tag='/1-annee:2012/&/1-zone:europe/1-pays:france/2-site:annecy/&/2-marque:marque_a/2-site:annecy/&/3-categorie:test_affichage/'] div.input-status" element
  # Cas inventaire non lancé, inventaire clôturé
    And I click element "div[id='granularity8'] button.reset"
    Then I should see "2012 | Grenoble" in the "div.cell[data-tag='/1-annee:2012/&/1-zone:europe/1-pays:france/2-site:grenoble/&/2-marque:marque_b/2-site:grenoble/']" element
    And I should see "Collecte clôturée" in the "div.cell[data-tag='/1-annee:2012/&/1-zone:europe/1-pays:france/2-site:grenoble/&/2-marque:marque_b/2-site:grenoble/'] div.inventory-status" element
    And I should see "Saisie terminée" in the "div.cell[data-tag='/1-annee:2012/&/1-zone:europe/1-pays:france/2-site:grenoble/&/2-marque:marque_b/2-site:grenoble/'] div.input-status" element
    Then I should see "2013 | Grenoble" in the "div.cell[data-tag='/1-annee:2013/&/1-zone:europe/1-pays:france/2-site:grenoble/&/2-marque:marque_b/2-site:grenoble/']" element
    And I should see "Collecte non lancée" in the "div.cell[data-tag='/1-annee:2013/&/1-zone:europe/1-pays:france/2-site:grenoble/&/2-marque:marque_b/2-site:grenoble/'] div.inventory-status" element
    And I should see "Collecte non lancée" in the "div.cell[data-tag='/1-annee:2013/&/1-zone:europe/1-pays:france/2-site:grenoble/&/2-marque:marque_b/2-site:grenoble/'] div.input-status" element

