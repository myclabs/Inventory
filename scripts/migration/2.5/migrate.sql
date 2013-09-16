
-- Renommage des classes
UPDATE ext_translations
  SET object_class = 'Unit\\Domain\\PhysicalQuantity'
  WHERE object_class = 'Unit_Model_PhysicalQuantity';
UPDATE ext_translations
  SET object_class = 'Unit\\Domain\\Unit\\Unit'
  WHERE object_class = 'Unit_Model_Unit';
UPDATE ext_translations
  SET object_class = 'Unit\\Domain\\UnitExtension'
  WHERE object_class = 'Unit_Model_Unit_Extension';
