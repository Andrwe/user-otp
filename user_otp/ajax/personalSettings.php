<?php
/**
 * ownCloud - One Time Password plugin
 *
 * @package user_otp
 * @author Frank Bongrand
 * @copyright 2013 Frank Bongrand fbongrand@free.fr
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU AFFERO GENERAL PUBLIC
 * License along with this library. If not, see <http://www.gnu.org/licenses/>.
 * Displays <a href="http://opensource.org/licenses/AGPL-3.0">GNU AFFERO GENERAL PUBLIC LICENSE</a>
 * @license http://opensource.org/licenses/AGPL-3.0 GNU AFFERO GENERAL PUBLIC LICENSE
 *
 */

include_once("user_otp/lib/utils.php");
include_once("user_otp/lib/multiotpdb.php");

$l=OC_L10N::get('settings');

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('user_otp');
OCP\JSON::callCheck();

if (!isset($_POST)) {
	OCP\JSON::error(array("data" => array( "message" => $l->t('No POST data found') )));
}

if (
	!empty($_POST["uid"]) &&
	$_PSOT['uid'] !== OCP\User::getUser()
) {
	OCP\JSON::checkSubAdminUser();
	$uid = $_POST["uid"];
}else{
	$uid = OCP\User::getUser();
}

function deleteOtp($uid) {
	if ($mOtp->CheckUserExists($uid)) {
		if($mOtp->DeleteUser($uid)){
			OCP\JSON::success(array('data' => array( 'message' => $l->t('OTP deleted') )));
		}else{
			OCP\JSON::error(array('data' => array( 'message' => $l->t('check apps folder rights') )));
		}
	}
}

function createOtp($uid) {
	if($mOtp->CheckUserExists($uid)){
		OCP\JSON::error(array('data' => array( 'message' => $l->t('OTP already exists') )));
		return;
	}
	  
	// format token seedll :
	if(
		!isset($_POST['UserTokenSeed']) ||
		$_POST['UserTokenSeed'] === ''
	){
		$UserTokenSeed = generateRandomString(16,64,8,_OTP_VALID_CHARS_);
	}else{
		$UserTokenSeed = $_POST['UserTokenSeed'];
	}
	if(
		isset($_POST['UserPin'])
	){
		$UserPin = $_POST['UserPin'];
	}else{
		$UserPin = '';
	}
	$UserTokenSeed=bin2hex($UserTokenSeed);
	$result = $mOtp->CreateUser(
		$uid,
		(OCP\Config::getAppValue('user_otp','UserPrefixPin','0')?1:0),
		OCP\Config::getAppValue('user_otp','UserAlgorithm','TOTP'),
		$UserTokenSeed,
		$UserPin,
		OCP\Config::getAppValue('user_otp','UserTokenNumberOfDigits','6'),
		OCP\Config::getAppValue('user_otp','UserTokenTimeIntervalOrLastEvent','30')
	);
	if($result){
	    OCP\JSON::success(array('data' => array( 'message' => $l->t('OTP Changed') )));
	}else{
	    OCP\JSON::error(array('data' => array( 'message' => $l->t('check apps folder rights') )));
	}
}

// Get data
$mOtp =  new MultiOtpDb(OCP\Config::getAppValue(
	'user_otp','EncryptionKey','DefaultCliEncryptionKey')
);
$mOtp->EnableVerboseLog();

if (isset($_POST['otp_action'])) {
	$action = $_POST['otp_action'];
} else {
	OCP\JSON::error(array("data" => array( "message" => $l->t("Invalid request") )));
}

switch ($action) {
	case 'delete_otp':
		deleteOtp($uid);
		break;
	case 'create_otp':
		createOtp($uid);
		break;
	case 'replace_otp':
		deleteOtp($uid);
		createOtp($uid);
		break;
	case 'send_email_otp':
		break;
	default:
		OCP\JSON::error(array("data" => array( "message" => $l->t("Invalid request") )));
		break;
}

function sendOtpEmail($uid) {
	if ($mOtp->CheckUserExists($uid)) {

		$mOtp->SetUser($uid);
		
		$UserTokenSeed = hex2bin($mOtp->GetUserTokenSeed());    
	    
		$key = 'email';
		$mail ="";
		$query=OC_DB::prepare('SELECT `configvalue` FROM `*PREFIX*preferences` WHERE `configkey` = ? AND `userid`=?');
		$result=$query->execute(array($key, $uid));
		if(!OC_DB::isError($result)) {
			$row=$result->fetchRow();
			$mail = $row['configvalue'];
		}
	
		$txtmsg = '<html><p>Hi, '.$uid.', <br><br>';
		$txtmsg .= '<p>find your OTP Configuration<br>';
		$txtmsg .= 'User Algorithm : '.$mOtp->GetUserAlgorithm().'<br>';
		if($mOtp->GetUserPrefixPin()){
			$txtmsg .= 'User Pin : '.$mOtp->GetUserPin().'<br>';
		}
		$txtmsg .= 'User Token Seed : '.$UserTokenSeed."<br>";
		$txtmsg .= 'User Token Time Interval Or Last Event : '.(strtolower($mOtp->GetUserAlgorithm())==='htop'?$mOtp->GetUserTokenLastEvent():$mOtp->GetUserTokenTimeInterval())."<br>";
		$txtmsg .= 'Token Url Link : '.$mOtp->GetUserTokenUrlLink()."<br>";
		$txtmsg .= 'With android token apps select base32 before input seed<br>';
		$txtmsg .= '<img src="data:image/png;base64,'.base64_encode($mOtp->GetUserTokenQrCode($mOtp->GetUser(),'','binary')).'"/><br><br>';
	
		$txtmsg .= $l->t('<p>This e-mail is automatic, please, do not reply to it.</p></html>');
		if ($mail !== NULL) {
			try{
				$result = OC_Mail::send($mail, $uid, '['.getenv('SERVER_NAME')."] - OTP", $txtmsg, 'Mail_Notification@'.getenv('SERVER_NAME'), 'Owncloud', 1 );	
				OCP\JSON::success(array("data" => array( "message" => $l->t("email sent to ".$mail) )));
			}catch(Exception $e){
				 OCP\JSON::error(array("data" => array( "message" => $l->t($e->getMessage()) )));
			}
		}else{
			//echo "Email address error<br>";
			OCP\JSON::error(array("data" => array( "message" => $l->t("Email address error : ".$mail) )));
		}
	}
}
