<?php
/**
 * Copyright (c) 2011, Robin Appelman <icewind1991@gmail.com>
 * This file is licensed under the Affero General Public License version 3 or later.
 * See the COPYING-README file.
 */

include_once("user_otp/lib/multiotpdb.php");

OC_Util::checkSubAdminUser();

// We have some javascript foo!
//OC_Util::addScript( 'settings', 'users/users' );
OC_Util::addScript( 'user_otp', 'list_users' );
//OC_Util::addScript( 'core', 'multiselect' );
//OC_Util::addScript( 'core', 'singleselect' );
OC_Util::addScript('core', 'jquery.inview');
OC_Util::addStyle( 'settings', 'settings' );
//OC_App::setActiveNavigationEntry( 'core_users' );

$users = array();
$groups = array();

$isadmin = OC_User::isAdminUser(OC_User::getUser());

//var_dump(__LINE__);
//var_dump($isadmin);
//exit();

if($isadmin) {
	$accessiblegroups = OC_Group::getGroups();
	$accessibleusers = OC_User::getDisplayNames('', 30);
	$subadmins = OC_SubAdmin::getAllSubAdmins();
} else {
	$accessiblegroups = OC_SubAdmin::getSubAdminsGroups(OC_User::getUser());
var_dump(__LINE__);
var_dump($accessiblegroups);
exit();
	$accessibleusers = OC_Group::displayNamesInGroups($accessiblegroups, '', 30);
	$subadmins = false;
}

$mOtp =  new MultiOtpDb(OCP\Config::getAppValue(
            'user_otp','EncryptionKey','DefaultCliEncryptionKey')
        );

// load users and quota
foreach($accessibleusers as $uid => $displayName) {

	$name = $displayName;
	if ( $displayName !== $uid ) {
		$name = $name . ' ('.$uid.')';
	}
	$UserTokenSeed="";
	$UserLocked="";
	$UserAlgorithm="";
	$UserPin="";
	$UserPrefixPin="";
	//get otp information :
	$OtpExist = $mOtp->CheckUserExists($uid);
	if($OtpExist){
		$mOtp->SetUser($uid);
		$UserTokenSeed=base32_encode(hex2bin($mOtp->GetUserTokenSeed()));
		$UserLocked=$mOtp->GetUserLocked();
		$UserAlgorithm=strtoupper($mOtp->GetUserAlgorithm());
		$UserPin=$mOtp->GetUserPin();
		$UserPrefixPin=$mOtp->GetUserPrefixPin();
	}

	$users[] = array(
		"name" => $uid,
		"displayName" => $displayName,
//		"groups" => OC_Group::getUserGroups($uid),
//		'subadmin' => OC_SubAdmin::getSubAdminsGroups($uid),
		'OtpExist' => $OtpExist,
		'UserTokenSeed' => $UserTokenSeed,
		'UserLocked'=>$UserLocked,
		'UserAlgorithm'=>$UserAlgorithm,
		'UserPin'=>$UserPin,
		'UserPrefixPin'=>$UserPrefixPin,
	);
}

foreach( $accessiblegroups as $i ) {
	// Do some more work here soon
	$groups[] = array( "name" => $i );
}

$tmpl = new OC_Template( "user_otp", "list_users", "user" );
$tmpl->assign('PrefixPin',(OCP\Config::getAppValue('user_otp','UserPrefixPin','0')?1:0));
$tmpl->assign( 'users', $users );
//$tmpl->assign( 'groups', $groups );
//$tmpl->assign( 'isadmin', (int) $isadmin);
//$tmpl->assign( 'subadmins', $subadmins);
//$tmpl->assign( 'numofgroups', count($accessiblegroups));
$tmpl->assign('enableAvatars', \OC_Config::getValue('enable_avatars', true));
$tmpl->printPage();

