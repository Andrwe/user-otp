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
define('_OTP_WARNING_', 2);
define('_OTP_ERROR_', 3);

if (!isset($_POST)) {
	$ajax = new OC_User_OTP_Ajax();
	$ajax->setError(_OTP_ERROR_, 'No POST data found');
	$ajax->sendResponse();
			\OC::$server->getLogger()->warning('No POST.', array('app' => 'user_otp'));
	return;
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

	private function writeLog($msg) {
		\OC::$server->getLogger()->warning($msg, array('app' => 'user_otp'));
	}

	public function sendResponse() {
		switch ($this->error['code']) {
			case _OTP_SUCCESS_:
				OCP\JSON::success(array('data' => array( 'message' => $this->error['msg'] )));
				break;
			case _OTP_WARNING_:
				break;
			case _OTP_ERROR_:
				OCP\JSON::error(array('data' => array( 'message' => $this->error['msg'])));
				break;
		}		
	}	

	public function setError($code, $msg = '') {
		$this->error['code'] = $code;
		$this->error['msg'] = $this->l->t($msg);
	}

	public function deleteOtp() {
		if ($this->mOtp->CheckUserExists($this->uid)) {
			if($this->mOtp->DeleteUser($this->uid)){
				$this->setError(_OTP_SUCCESS_, 'OTP deleted');
				return 1;
			}else{
				$this->setError(_OTP_ERROR_, 'check apps folder rights');
				return 0;
			}
		}
	}
	
	public function createOtp() {
		if($this->mOtp->CheckUserExists($this->uid)){
			$this->setError(_OTP_ERROR_, 'OTP already exists');
			return;
		}
		  
		if(
			!isset($_POST['UserTokenSeed']) ||
			$_POST['UserTokenSeed'] === ''
		){
			$userTokenSeed = generateRandomString(16,64,8,_OTP_VALID_CHARS_);
		} else {
			if (base32_encode(base32_decode($_POST['UserTokenSeed'])) === $_POST['UserTokenSeed']) {
				$userTokenSeed = base32_decode($_POST['UserTokenSeed']);
			} else {
				$userTokenSeed = $_POST['UserTokenSeed'];
			}
		}
		if (
			isset($_POST['UserPin']) &&
			$_POST['UserPin'] !== ''
		){
			$userPin = $_POST['UserPin'];
			$useUserPrefixPin = OCP\Config::getAppValue('user_otp','UseUserPrefixPin','0');
		} else {
			if (OCP\Config::getAppValue('user_otp','UserPrefixPin','') === '') {
				$useUserPrefixPin = 0;
			} else {
				$useUserPrefixPin = 1;
			}
			$userPin = OCP\Config::getAppValue('user_otp','UserPrefixPin','');
		}
		$userTokenSeed = bin2hex($userTokenSeed);
		$result = $this->mOtp->CreateUser(
			$this->uid,
			$useUserPrefixPin,
			OCP\Config::getAppValue('user_otp','UserAlgorithm','TOTP'),
			$userTokenSeed,
			$userPin,
			OCP\Config::getAppValue('user_otp','UserTokenNumberOfDigits','6'),
			OCP\Config::getAppValue('user_otp','UserTokenTimeIntervalOrLastEvent','30')
		);
		if($result){
			$this->setError(_OTP_SUCCESS_, 'OTP changed');
		}else{
			$this->setError(_OTP_ERROR_, 'check apps folder rights');
		}
	}

	public function sendOtpEmail($reason = 'notify') {
		if ($this->mOtp->CheckUserExists($this->uid)) {
	
			$this->mOtp->SetUser($this->uid);

			$defaults = new \OC_Defaults();
			
			$i=0;
			$config[$i]['name'] = 'UserTokenSeed';
			$config[$i]['value'] = hex2bin($this->mOtp->GetUserTokenSeed());
			$config[$i]['type'] = 'text';
			$i++;
			$config[$i]['name'] = 'Algorithm';
			$config[$i]['value'] = strtoupper($this->mOtp->GetUserAlgorithm());
			$config[$i]['type'] = 'text';
			$i++;
			$config[$i]['name'] = 'UserPin';
			$config[$i]['value'] = empty($this->mOtp->GetUserPin()) ? $this->mOtp->GetUserPrefixPin() : $this->mOtp->GetUserPin();
			$config[$i]['type'] = 'text';
			$i++;
			if ($config['Algorithm'] === 'HOTP') {
				$config[$i]['name'] = 'LastEvent';
				$config[$i]['value'] = $this->mOtp->GetUserTokenLastEvent();
			} elseif ($config['Algorithm'] === 'TOTP') {
				$config[$i]['name'] = 'TimeInterval';
				$config[$i]['value'] = $this->mOtp->GetUserTokenTimeInterval();
			}
			$config[$i]['type'] = 'text';
			$i++;
			$config[$i]['name'] = 'TokenUrlLink';
			$config[$i]['value'] = $this->mOtp->GetUserTokenUrlLink();
			$config[$i]['type'] = 'link';
			$i++;
			$config[$i]['name'] = 'TokenQrCode';
			$config[$i]['value'] = base64_encode($this->mOtp->GetUserTokenQrCode($this->mOtp->GetUser(),'','binary'));
			$config[$i]['type'] = 'image';
		    
			$htmlbody = new \OCP\Template('user_otp', 'email', '');
			$htmlbody->assign('uid', $this->uid);
			$htmlbody->assign('fullname', OC_User::getDisplayName($this->uid));
			$htmlbody->assign('url', \OC_Helper::makeURLAbsolute('/'));
			$htmlbody->assign('overwriteL10N', $this->l);
			$htmlbody->assign('config', $config);
			$htmlmail = $htmlbody->fetchPage();

			$plainbody = new \OCP\Template('user_otp', 'email-plain', '');
			$plainbody->assign('uid', $this->uid);
			$plainbody->assign('fullname', OC_User::getDisplayName($this->uid));
			$plainbody->assign('owncloud_installation', \OC_Helper::makeURLAbsolute('/'));
			$plainbody->assign('overwriteL10N', $this->l);
			$plainbody->assign('config', $config);
			$plainmail = $plainbody->fetchPage();

			$toaddress = 'alw@andrwe.org';
			$fromaddress = \OCP\Util::getDefaultEmailAddress('no-reply');
			$fromname = $defaults->getName();
			$subject = '[' . $defaults->getTitle() . '] ' . $this->l->t('OTP Notification');

			if ($toaddress !== NULL) {
				try{
					$result = \OCP\Util::sendMail($toaddress, $this->uid, $subject, $htmlmail, $fromaddress, $fromname, 1, $plainmail );	
					$this->setError(_OTP_SUCCESS_, 'email sent to ' . $toaddress);
				}catch(Exception $e){
					$this->setError(_OTP_ERROR_, $e->getMessage());
				}
			}else{
				$this->setError(_OTP_ERROR_, 'Email address error : ' . $toaddress);
			}
		}
	}

}

if (isset($_POST['otp_action'])) {
	$action = $_POST['otp_action'];
} else {
	$ajax = new OC_User_OTP_Ajax();
	$ajax->setError(_OTP_ERROR_, 'Invalid request');
	$ajax->sendResponse();
	return;
}

$ajax = new OC_User_OTP_Ajax($uid);

switch ($action) {
	case 'delete_otp':
		$ajax->deleteOtp();
		$ajax->sendResponse();
		break;
	case 'create_otp':
		$ajax->createOtp();
		$ajax->sendResponse();
		break;
	case 'replace_otp':
		if ($ajax->deleteOtp()) {
			$ajax->createOtp();
		}
		$ajax->sendResponse();
		break;
	case 'send_email_otp':
		$ajax->sendOtpEmail();
		$ajax->sendResponse();
		break;
	default:
		$ajax->setError(_OTP_ERROR_, 'Invalid request');
		$ajax->sendResponse();
		break;
}
