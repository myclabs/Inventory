
-- Suppression de l'ancienne primary key des associations de keyword.
ALTER TABLE 'Keyword_Association' DROP 'id';
ALTER TABLE 'Techno_Meaning' CHANGE 'refKeyword' 'keyword';
