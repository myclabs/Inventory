
-- Renommage des classes
UPDATE ext_translations
  SET object_class = 'Techno\\Domain\\Category'
  WHERE object_class = 'Techno_Model_Category';

UPDATE ext_translations
  SET object_class = 'Techno\\Domain\\Component'
  WHERE object_class = 'Techno_Model_Component';

UPDATE ext_translations
  SET object_class = 'Techno\\Domain\\Family\\Family'
  WHERE object_class = 'Techno_Model_Family';

UPDATE ext_translations
  SET object_class = 'Techno\\Domain\\Family\\Family'
  WHERE object_class = 'Techno_Model_Family';
