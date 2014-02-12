ALTER TABLE Algo_ParameterCoordinate_Fixed
  CHANGE refMemberKeyword idMember VARCHAR(255);


-- Family
ALTER TABLE Techno_Family ADD documentation LONGTEXT NULL AFTER position;
ALTER TABLE Techno_Family ADD refBaseUnit VARCHAR(255) NOT NULL AFTER documentation;
ALTER TABLE Techno_Family ADD refUnit VARCHAR(255) NOT NULL AFTER refBaseUnit;

UPDATE Techno_Family
  INNER JOIN Techno_Component
    ON Techno_Family.id = Techno_Component.id
SET
  Techno_Family.documentation = Techno_Component.documentation,
  Techno_Family.refBaseUnit = Techno_Component.refBaseUnit,
  Techno_Family.refUnit = Techno_Component.refUnit;


-- Dimension
ALTER TABLE Techno_Family_Dimension ADD ref VARCHAR(255) NOT NULL AFTER id;
ALTER TABLE Techno_Family_Dimension ADD label VARCHAR(255) NOT NULL AFTER ref;

UPDATE Techno_Family_Dimension
  INNER JOIN Techno_Meaning
    ON Techno_Family_Dimension.idMeaning = Techno_Meaning.id
SET
  Techno_Family_Dimension.ref = Techno_Meaning.keyword;

UPDATE Techno_Family_Dimension
  INNER JOIN Keyword_Keyword
    ON Techno_Family_Dimension.ref = Keyword_Keyword.ref
SET
  Techno_Family_Dimension.label = CONCAT(UPPER(LEFT(Keyword_Keyword.label, 1)), SUBSTRING(Keyword_Keyword.label, 2));


-- Member
ALTER TABLE Techno_Family_Member CHANGE keyword ref VARCHAR(255);
ALTER TABLE Techno_Family_Member ADD label VARCHAR(255) NOT NULL AFTER ref;

UPDATE Techno_Family_Member
  INNER JOIN Keyword_Keyword
    ON Techno_Family_Member.ref = Keyword_Keyword.ref
SET
  Techno_Family_Member.label = Keyword_Keyword.label;


-- Cell
ALTER TABLE Techno_Family_Cell ADD value VARCHAR(255) NULL AFTER membersHashKey;

UPDATE Techno_Family_Cell
  INNER JOIN Techno_Element_Coeff
    ON Techno_Family_Cell.idChosenElement = Techno_Element_Coeff.id
SET
  Techno_Family_Cell.value = Techno_Element_Coeff.value;
