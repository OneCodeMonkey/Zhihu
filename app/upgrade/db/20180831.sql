INSERT INTO `[#DB_PREFIX#]system_setting` (`varname`, `value`) VALUES ('enable_column', 's:1:"N";');
INSERT INTO `[#DB_PREFIX#]system_setting` (`varname`, `value`) VALUES ('weixin_build_account', 's:1:"N";');
UPDATE `[#DB_PREFIX#]system_setting` SET  `varname` = 'sina_akey', `value` = '' WHERE `varname` = 'sina_akey\', NULL';
UPDATE `[#DB_PREFIX#]system_setting` SET  `varname` = 'sina_skey', `value` = '' WHERE `varname` = 'sina_skey\', NULL';