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

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('user_otp');
OCP\JSON::callCheck();

define('_OTP_SUCCESS_', 1);
define('_OTP_WARNING_', 3);
define('_OTP_ERROR_', 3);

if (!isset($_POST)) {
	$ajax = new OC_User_OTP_Ajax();
	$ajax->setError(_OTP_ERROR_, 'No POST data found');
	$ajax->sendResponse();
//	OCP\JSON::error(array("data" => array( "message" => OC_L10N::get('settings')->t('No POST data found') )));
}

if (
	!empty($_POST["uid"]) &&
	$_POST['uid'] !== OCP\User::getUser()
) {
	OC_JSON::checkSubAdminUser();
	$uid = $_POST["uid"];
}else{
	$uid = OCP\User::getUser();
}

class OC_User_OTP_Ajax {

	function __construct($uid = '') {
		$this->mOtp = new MultiOtpDb(OCP\Config::getAppValue(
			'user_otp','EncryptionKey','DefaultCliEncryptionKey')
		);
		$this->uid = $uid;
		$this->l = OC_L10N::get('settings');
		$this->error['code'] = _OTP_SUCCESS_;
		$this->error['msg'] = '';
	}

	public function sendResponse() {
		switch ($this->error['code']) {
			case _OTP_SUCCESS_:
				OCP\JSON::success(array('data' => array( 'message' => $this->l->t('OTP deleted') )));
				break;
			case _OTP_WARNING_:
				break;
			case _OTP_ERROR_:
				OCP\JSON::error(array('data' => array( 'message' => $this->l->t('check apps folder rights') )));
				break;
		}		
	}	

	public function setError($code, $msg = '') {
		$this->error['code'] = $code;
		$this->errpr['msg'] = $this->l->t($msg);
	}

	public function deleteOtp() {
		if ($this->mOtp->CheckUserExists($this->uid)) {
			if($this->mOtp->DeleteUser($this->uid)){
				$this->setError(_OTP_SUCCESS_, 'OTP deleted');
			}else{
				$this->setError(_OTP_ERROR_, 'check apps folder rights');
			}
			$this->sendResponse();
		}
	}
	
	public function createOtp() {
		if($this->mOtp->CheckUserExists($this->uid)){
			$this->setError(_OTP_ERROR_, 'OTP already exists');
			$this->sendResponse();
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
		$result = $this->mOtp->CreateUser(
			$this->uid,
			(OCP\Config::getAppValue('user_otp','UserPrefixPin','0')?1:0),
			OCP\Config::getAppValue('user_otp','UserAlgorithm','TOTP'),
			$UserTokenSeed,
			$UserPin,
			OCP\Config::getAppValue('user_otp','UserTokenNumberOfDigits','6'),
			OCP\Config::getAppValue('user_otp','UserTokenTimeIntervalOrLastEvent','30')
		);
		if($result){
			$this->setError(_OTP_SUCCESS_, 'OTP changed');
		}else{
			$this->setError(_OTP_ERROR_, 'check apps folder rights');
		}
		$this->sendResponse();
	}

	public function sendOtpEmail() {
		if ($this->mOtp->CheckUserExists($this->uid)) {
	
			$this->mOtp->SetUser($this->uid);
			
			$UserTokenSeed = hex2bin($this->mOtp->GetUserTokenSeed());    
		    
			$key = 'email';
			$mail ="";
			$query=OC_DB::prepare('SELECT `configvalue` FROM `*PREFIX*preferences` WHERE `configkey` = ? AND `userid`=?');
			$result=$query->execute(array($key, $this->uid));
			if(!OC_DB::isError($result)) {
				$row=$result->fetchRow();
				$mail = $row['configvalue'];
			}
		
			$txtmsg = '<html><p>Hi, '.$this->uid.', <br><br>';
			$txtmsg .= '<p>find your OTP Configuration<br>';
			$txtmsg .= 'User Algorithm : '.$this->mOtp->GetUserAlgorithm().'<br>';
			if($mOtp->GetUserPrefixPin()){
				$txtmsg .= 'User Pin : '.$this->mOtp->GetUserPin().'<br>';
			}
			$txtmsg .= 'User Token Seed : '.$UserTokenSeed."<br>";
			$txtmsg .= 'User Token Time Interval Or Last Event : '.(strtolower($this->mOtp->GetUserAlgorithm())==='htop'?$this->mOtp->GetUserTokenLastEvent():$this->mOtp->GetUserTokenTimeInterval())."<br>";
			$txtmsg .= 'Token Url Link : '.$this->mOtp->GetUserTokenUrlLink()."<br>";
			$txtmsg .= 'With android token apps select base32 before input seed<br>';
			$txtmsg .= '<img src="data:image/png;base64,'.base64_encode($this->mOtp->GetUserTokenQrCode($this->mOtp->GetUser(),'','binary')).'"/><br><br>';
		
			$txtmsg .= $this->l->t('<p>This e-mail is automatic, please, do not reply to it.</p></html>');
			if ($mail !== NULL) {
				try{
					$result = OC_Mail::send($mail, $this->uid, '['.getenv('SERVER_NAME')."] - OTP", $txtmsg, 'Mail_Notification@'.getenv('SERVER_NAME'), 'Owncloud', 1 );	
					$this->setError(_OTP_SUCCESS_, 'email sent to ' . $mail);
				}catch(Exception $e){
					$this->setError(_OTP_ERROR_, $e->getMessage());
				}
			}else{
				$this->setError(_OTP_ERROR_, 'Email address error : ' . $mail);
			}
			$this->sendResponse();
		}
	}

}

if (isset($_POST['otp_action'])) {
	$action = $_POST['otp_action'];
} else {
	$ajax = new OC_User_OTP_Ajax();
	$ajax->setError(_OTP_ERROR_, 'Invalid request');
	$ajax->sendResponse();
//	OCP\JSON::error(array("data" => array( "message" => OC_L10N::get('settings')->t("Invalid request") )));
}

$ajax = new OC_User_OTP_Ajax($uid);

switch ($action) {
	case 'delete_otp':
		$ajax->deleteOtp();
		break;
	case 'create_otp':
		$ajax->createOtp();
		break;
	case 'replace_otp':
		$ajax->deleteOtp();
		$ajax->createOtp();
		break;
	case 'send_email_otp':
		break;
	default:
		$ajax = new OC_User_OTP_Ajax();
		$ajax->setError(_OTP_ERROR_, 'Invalid request');
		$ajax->sendResponse();
//		OCP\JSON::error(array("data" => array( "message" => OC_L10N::get('settings')->t("Invalid request") )));
		break;
}
