# Valeur par défaut : true
ALTER TABLE `AF_InputSet_Primary`
  ADD `calculationComplete` TINYINT( 1 ) NOT NULL DEFAULT '1';
# Puis supprime la valeur par défaut
ALTER TABLE `AF_InputSet_Primary`
  CHANGE `calculationComplete` `calculationComplete` TINYINT( 1 ) NOT NULL;

# Corrige le type de la colonne
ALTER TABLE `Algo_Selection_Main`
  CHANGE `expression` `expression` LONGTEXT;
# Puis restaure les expressions qui ont été tronquées
UPDATE Algo_Selection_Main algo, TEC_Expression expression
  SET algo.expression = expression.expression
  WHERE expression.id = algo.idExpression
