<?php
/**
 * Copyright (c) 2011, Robin Appelman <icewind1991@gmail.com>
 * This file is licensed under the Affero General Public License version 3 or later.
 * See the COPYING-README file.
 */

include_once("user_otp/lib/multiotpdb.php");

OC_Util::checkSubAdminUser();

// We have some javascript foo!
OC_Util::addScript( 'user_otp', 'list_users' );
OC_Util::addScript('core', 'jquery.inview');
OC_Util::addStyle( 'settings', 'settings' );

$users = array();
// preparation for group based user listing
//$groups = array();

$isadmin = OC_User::isAdminUser(OC_User::getUser());

if($isadmin) {
	$accessiblegroups = OC_Group::getGroups();
	$accessibleusers = OC_User::getDisplayNames('', 30);
	$subadmins = OC_SubAdmin::getAllSubAdmins();
} else {
	$accessiblegroups = OC_SubAdmin::getSubAdminsGroups(OC_User::getUser());
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
	$userLocked     = '';
	$userErrorCount = '';
	$userAlgorithm  = '';
	$userPin        = '';
	$userPrefixPin  = '';
	$otpExist       = $mOtp->CheckUserExists($uid);

	if($otpExist){
		$mOtp->SetUser($uid);
		$userLocked = $mOtp->GetUserLocked();
		$userErrorCount = $mOtp->GetUserErrorCounter();
		$userAlgorithm = strtoupper($mOtp->GetUserAlgorithm());
		if (intval($mOtp->GetUserPrefixPin()) === 0) {
			$userPin = 'none';
		} elseif ($mOtp->GetUserPin() === OCP\Config::getAppValue('user_otp','UserPrefixPin','')) {
			$userPin = 'default';
		} else {
			$userPin = 'self defined';
		}
		$userPrefixPin = $mOtp->GetUserPrefixPin();
	}

	$users[] = array(
		'name' => $uid,
		'displayName' => $displayName,
		'OtpExist' => $otpExist,
		'UserLocked' => $userLocked,
		'UserErrorCount' => $userErrorCount,
		'UserAlgorithm' => $userAlgorithm,
		'UserPin' => $userPin,
		'UserPrefixPin' => $userPrefixPin,
	);
}

// preparation for group based user listing
//foreach( $accessiblegroups as $i ) {
//	// Do some more work here soon
//	$groups[] = array( "name" => $i );
//}

$tmpl = new OC_Template( "user_otp", "list_users", "user" );
$tmpl->assign('PrefixPin', OCP\Config::getAppValue('user_otp','UseUserPrefixPin','0'));
$tmpl->assign( 'users', $users );
$tmpl->assign('enableAvatars', \OC_Config::getValue('enable_avatars', true));
$tmpl->printPage();

