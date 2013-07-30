# Valeur par défaut : true
ALTER TABLE  `AF_InputSet_Primary`
  ADD `calculationComplete` TINYINT( 1 ) NOT NULL DEFAULT '1';
# Puis supprime la valeur par défaut
ALTER TABLE  `AF_InputSet_Primary`
  CHANGE `calculationComplete` `calculationComplete` TINYINT( 1 ) NOT NULL;
