
-- Renommage des classes
UPDATE ext_translations
  SET object_class = 'AF\\Domain\\AF'
  WHERE object_class = 'AF_Model_AF';
UPDATE ext_translations
  SET object_class = 'AF\\Domain\\Category'
  WHERE object_class = 'AF_Model_Category';
UPDATE ext_translations
  SET object_class = 'AF\\Domain\\Component\\Component'
  WHERE object_class = 'AF_Model_Component';
UPDATE ext_translations
  SET object_class = 'AF\\Domain\\Component\\Select\\SelectOption'
  WHERE object_class = 'AF_Model_Component_Select_Option';

UPDATE ext_translations
  SET object_class = 'AF\\Domain\\Algorithm\\Numeric\\NumericConstantAlgo'
  WHERE object_class = 'Algo_Model_Numeric_Constant';
UPDATE ext_translations
  SET object_class = 'AF\\Domain\\Algorithm\\Numeric\\NumericExpressionAlgo'
  WHERE object_class = 'Algo_Model_Numeric_Expression';
UPDATE ext_translations
  SET object_class = 'AF\\Domain\\Algorithm\\Numeric\\NumericInputAlgo'
  WHERE object_class = 'Algo_Model_Numeric_Input';
UPDATE ext_translations
  SET object_class = 'AF\\Domain\\Algorithm\\Numeric\\NumericParameterAlgo'
  WHERE object_class = 'Algo_Model_Numeric_Parameter';
