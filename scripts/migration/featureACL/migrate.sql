-- User_Autorisation

UPDATE User_Authorization
SET `action` = 'User\\Domain\\ACL\\Action\\DefaultAction::1'
WHERE `action` = 'User_Model_Action_Default::1';

UPDATE User_Authorization
SET `action` = 'User\\Domain\\ACL\\Action\\DefaultAction::2'
WHERE `action` = 'User_Model_Action_Default::2';

UPDATE User_Authorization
SET `action` = 'User\\Domain\\ACL\\Action\\DefaultAction::4'
WHERE `action` = 'User_Model_Action_Default::4';

UPDATE User_Authorization
SET `action` = 'User\\Domain\\ACL\\Action\\DefaultAction::8'
WHERE `action` = 'User_Model_Action_Default::8';

UPDATE User_Authorization
SET `action` = 'User\\Domain\\ACL\\Action\\DefaultAction::16'
WHERE `action` = 'User_Model_Action_Default::16';

UPDATE User_Authorization
SET `action` = 'User\\Domain\\ACL\\Action\\DefaultAction::128'
WHERE `action` = 'User_Model_Action_Default::128';

-- ACL_Filter

UPDATE ACL_Filter
SET `action` = 'User\\Domain\\ACL\\Action\\DefaultAction::1'
WHERE `action` = 'User_Model_Action_Default::1';

UPDATE ACL_Filter
SET `action` = 'User\\Domain\\ACL\\Action\\DefaultAction::2'
WHERE `action` = 'User_Model_Action_Default::2';

UPDATE ACL_Filter
SET `action` = 'User\\Domain\\ACL\\Action\\DefaultAction::4'
WHERE `action` = 'User_Model_Action_Default::4';

UPDATE ACL_Filter
SET `action` = 'User\\Domain\\ACL\\Action\\DefaultAction::8'
WHERE `action` = 'User_Model_Action_Default::8';

UPDATE ACL_Filter
SET `action` = 'User\\Domain\\ACL\\Action\\DefaultAction::16'
WHERE `action` = 'User_Model_Action_Default::16';

UPDATE ACL_Filter
SET `action` = 'User\\Domain\\ACL\\Action\\DefaultAction::128'
WHERE `action` = 'User_Model_Action_Default::128';

-- User_Resource

UPDATE User_Resource
SET entityName = 'User\\Domain\\User'
WHERE entityName = 'User_Model_User';

UPDATE User_Resource
SET entityName = 'User\\Domain\\ACL\\Role'
WHERE entityName = 'User_Model_Role';
