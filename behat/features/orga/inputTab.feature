@dbFull
Feature: Organization input tab feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Filter on organization members in Input tab
  # Accès à l'onglet "Saisies"
    Given I am on "orga/cell/view/idCell/1"
    And I wait for the page to finish loading
    And I click element "legend[data-target='#granularity7']"
    Then I should see "4 / 6" in the "#granularity7 span.granularity-info" element
  # Filtre sur le site "Annecy"
    When I select "Annecy" from "granularity7_axissite"
    Then I should see "2 / 6" in the "#granularity7 span.granularity-info" element
  # Bouton "Réinitialiser"
    When I click element "i.fa-search-minus"
    Then I should see "6 / 6" in the "#granularity7 span.granularity-info" element

  @javascript
  Scenario: Display of the various columns (inventory status, input progress, input status)
    Given I am on "orga/cell/view/idCell/1"
    And I wait for the page to finish loading
  # Cas inventaire en cours, saisie complète
    When I open collapse "Année | Site"
    Then I should see "2012 | Annecy" in the "div.cell[data-tag='/1-annee:2012/&/1-zone:europe/1-pays:france/2-site:annecy/&/2-marque:marque_a/2-site:annecy/']" element
    And I should see "Collecte en cours" in the "div.cell[data-tag='/1-annee:2012/&/1-zone:europe/1-pays:france/2-site:annecy/&/2-marque:marque_a/2-site:annecy/'] div.inventory-status" element
    And I should see "Saisie complète" in the "div.cell[data-tag='/1-annee:2012/&/1-zone:europe/1-pays:france/2-site:annecy/&/2-marque:marque_a/2-site:annecy/'] div.input-status" element
    And I should see "100 %" in the "div.cell[data-tag='/1-annee:2012/&/1-zone:europe/1-pays:france/2-site:annecy/&/2-marque:marque_a/2-site:annecy/'] div.progress" element
  # Cas inventaire en cours, saisie incomplète / saisie terminée
    When I close collapse "Année | Site"
    When I open collapse "Année | Site | Catégorie"
    Then I should see "2012 | Annecy | Énergie" in the "div.cell[data-tag='/1-annee:2012/&/1-zone:europe/1-pays:france/2-site:annecy/&/2-marque:marque_a/2-site:annecy/&/3-categorie:energie/']" element
    And I should see "Saisie terminée" in the "div.cell[data-tag='/1-annee:2012/&/1-zone:europe/1-pays:france/2-site:annecy/&/2-marque:marque_a/2-site:annecy/&/3-categorie:energie/'] div.input-status" element
    And I should see "100 %" in the "div.cell[data-tag='/1-annee:2012/&/1-zone:europe/1-pays:france/2-site:annecy/&/2-marque:marque_a/2-site:annecy/&/3-categorie:energie/'] div.progress" element
    Then I should see "2012 | Annecy | Test affichage" in the "div.cell[data-tag='/1-annee:2012/&/1-zone:europe/1-pays:france/2-site:annecy/&/2-marque:marque_a/2-site:annecy/&/3-categorie:test_affichage/']" element
    And I should see "Saisie incomplète" in the "div.cell[data-tag='/1-annee:2012/&/1-zone:europe/1-pays:france/2-site:annecy/&/2-marque:marque_a/2-site:annecy/&/3-categorie:test_affichage/'] div.input-status" element
    And I should see "14 %" in the "div.cell[data-tag='/1-annee:2012/&/1-zone:europe/1-pays:france/2-site:annecy/&/2-marque:marque_a/2-site:annecy/&/3-categorie:test_affichage/'] div.progress" element
  # Cas inventaire non lancé, inventaire clôturé
    When I open close "Année | Site | Catégorie"
    And I click element "legend[data-target='#granularity7']"
    Then I should see "2012 | Grenoble" in the "div.cell[data-tag='/1-annee:2012/&/1-zone:europe/1-pays:france/2-site:grenoble/&/2-marque:marque_b/2-site:grenoble/']" element
    And I should see "Collecte clôturée" in the "div.cell[data-tag='/1-annee:2012/&/1-zone:europe/1-pays:france/2-site:grenoble/&/2-marque:marque_b/2-site:grenoble/'] div.inventory-status" element
    And I should see "Saisie terminée" in the "div.cell[data-tag='/1-annee:2012/&/1-zone:europe/1-pays:france/2-site:grenoble/&/2-marque:marque_b/2-site:grenoble/'] div.input-status" element
    And I should see "100 %" in the "div.cell[data-tag='/1-annee:2012/&/1-zone:europe/1-pays:france/2-site:grenoble/&/2-marque:marque_b/2-site:grenoble/'] div.progress" element
    Then I should see "2013 | Grenoble" in the "div.cell[data-tag='/1-annee:2013/&/1-zone:europe/1-pays:france/2-site:grenoble/&/2-marque:marque_b/2-site:grenoble/']" element
    And I should see "Collecte non lancée" in the "div.cell[data-tag='/1-annee:2013/&/1-zone:europe/1-pays:france/2-site:grenoble/&/2-marque:marque_b/2-site:grenoble/'] div.inventory-status" element
    And I should see "Collecte non lancée" in the "div.cell[data-tag='/1-annee:2013/&/1-zone:europe/1-pays:france/2-site:grenoble/&/2-marque:marque_b/2-site:grenoble/'] div.input-status" element
    And I should see "0 %" in the "div.cell[data-tag='/1-annee:2013/&/1-zone:europe/1-pays:france/2-site:grenoble/&/2-marque:marque_b/2-site:grenoble/'] div.progress" element

