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
				$this->sendOtpEmail('delete');
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
			$this->sendOtpEmail('create');
			$this->setError(_OTP_SUCCESS_, 'OTP created');
		}else{
			$this->setError(_OTP_ERROR_, 'check apps folder rights');
		}
	}

	public function sendOtpEmail($template = 'resent') {
		if ($this->mOtp->CheckUserExists($this->uid)) {

			// FIXME: find a safer way to get mail address
			$toaddress = \OCP\Config::getUserValue($this->uid, 'settings', 'email');
			if ($toaddress === NULL) {
				// if uid is a valid mail address (e.g. for IMAP backend) use it as recipient
				if (OC_Mail::validateAddress($this->uid)) {
					$toaddress = $this->uid;
				}
			}

			if ($toaddress !== NULL) {

				$this->mOtp->SetUser($this->uid);

				$defaults = new \OC_Defaults();

				$config[] = array(
						'name'  => 'Token seed',
						'value' => base32_encode(hex2bin($this->mOtp->GetUserTokenSeed())),
						'type'  => 'text',
						'description' => $this->l->t('Private password to generate OTP tokens'),
					);
				$config[] = array(
						'name' => 'Algorithm',
						'value' => strtoupper($this->mOtp->GetUserAlgorithm()),
						'type' => 'text',
					);
				$config[] = array(
						'name' => 'Number of retries',
						'value' => $this->mOtp->GetMaxBlockFailures(),
						'type' => 'text',
						'description' => $this->l->t('After this much retries you will be locked until an admin unlocks you.'),
					);
				$config[] = array(
						'name' => 'Prefix pin',
						'value' => empty($this->mOtp->GetUserPin()) ? $this->mOtp->GetUserPrefixPin() : $this->mOtp->GetUserPin(),
						'type' => 'text',
						'description' => $this->l->t('This pin has to be prefixed to the generated token. e.g. 1234TOKEN'),
					);
				if (strtoupper($this->mOtp->GetUserAlgorithm()) === 'HOTP') {
					$config[] = array(
							'name' => 'Last event',
							'value' => $this->mOtp->GetUserTokenLastEvent(),
							'type' => 'text',
							'description' => $this->l->t('Number of tokens used to login.'),
						);
				} elseif (strtoupper($this->mOtp->GetUserAlgorithm()) === 'TOTP') {
					$config[] = array(
							'name' => 'Time interval',
							'value' => $this->mOtp->GetUserTokenTimeInterval(),
							'type' => 'text',
							'description' => $this->l->t('Amount of time in seconds a generated token is valid.'),
						);
				}
				$config[] = array(
						'name' => 'Token link',
						'value' => $this->mOtp->GetUserTokenUrlLink(),
						'type' => 'link',
						'description' => $this->l->t('Clicking this link should start your OTP app and at these settings correctly.'),
					);
				$config[] = array(
						'name' => 'QR code',
						'value' => base64_encode($this->mOtp->GetUserTokenQrCode($this->mOtp->GetUser(),'','binary')),
						'type' => 'image',
						'description' => $this->l->t('If you scan this code with your OTP app it will import these settings correctly.'),
					);
				$config[] = array(
						'name' => 'QR code link',
						'value' => \OCP\Util::linkToAbsolute('user_otp', 'qrcode.php'),
						'type' => 'link',
						'description' => $this->l->t('If you scan the code on the linked page with your OTP app it will import these settings correctly.'),
					);

				$fromaddress = \OCP\Util::getDefaultEmailAddress('no-reply');
				$fromname = $defaults->getName();
				$subject = '[' . $defaults->getTitle() . '] ' . $this->l->t('OTP Notification');

				$theme = OC_Util::getTheme();
				$ocroot = OC::$SERVERROOT;
				$apppath = OC_App::getAppPath('user_otp');


				// FIXME: check whether template file exists as \OCP\Template() doesn't check and raises a fatal exception
				if (
					\OC\Files::file_exists(
						\OCP\Template::getAppTemplateDirs(
							$theme,
							'user_otp',
							$ocroot,
							$apppath
						) . 'email-' . $template . '-html.php'
					)
				) {
					$htmlbody = new \OCP\Template('user_otp', 'email-' . $template . '-html', '');
					$htmlbody->assign('uid', $this->uid);
					$htmlbody->assign('fullname', OC_User::getDisplayName($this->uid));
					$htmlbody->assign('requestor', OC_User::getUser());
					$htmlbody->assign('url', \OC_Helper::makeURLAbsolute('/'));
					$htmlbody->assign('title', $defaults->getTitle());
					$htmlbody->assign('footer', OC_Mail::getfooter());
					$htmlbody->assign('overwriteL10N', $this->l);
					$htmlbody->assign('config', $config);
					$htmlmail = $htmlbody->fetchPage();
				} else {
					$htmlmail = '';
				}

				// FIXME: check whether template file exists as \OCP\Template() doesn't check and raises a fatal exception
				if (
					\OC\Files::file_exists(
						\OCP\Template::getAppTemplateDirs(
							$theme,
							'user_otp',
							$ocroot,
							$apppath
						) . 'email-' . $template . '-plain.php'
					)
				) {
					$plainbody = new \OCP\Template('user_otp', 'email-' . $template . '-plain', '');
					$plainbody->assign('uid', $this->uid);
					$plainbody->assign('fullname', OC_User::getDisplayName($this->uid));
					$plainbody->assign('requestor', OC_User::getUser());
					$plainbody->assign('url', \OC_Helper::makeURLAbsolute('/'));
					$plainbody->assign('title', $defaults->getTitle());
					$plainbody->assign('footer', OC_Mail::getfooter());
					$plainbody->assign('overwriteL10N', $this->l);
					$plainbody->assign('config', $config);
					$plainmail = $plainbody->fetchPage();
				} else {
					$plainmail = '';
				}

				try{
					$result = \OCP\Util::sendMail($toaddress, $this->uid, $subject, $htmlmail, $fromaddress, $fromname, 1, $plainmail );
					$this->setError(_OTP_SUCCESS_, 'email sent to ' . $toaddress);
					return;
				}catch(Exception $e){
					$this->setError(_OTP_ERROR_, $e->getMessage());
					return;
				}
			}else{
				$this->setError(_OTP_ERROR_, 'No e-mail address found for user: ' . $this->uid);
				return;
			}
		}
	}

	public function replaceDefaultPin() {
		if (
			isset($_POST['newpin']) &&
			isset($_POST['oldpin'])
		) {
			$newpin = $_POST['newpin'];
			$oldpin = $_POST['oldpin'];
			if ($oldpin === 'all') {
			} else {
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
		$ajax->sendOtpEmail('resent');
		$ajax->sendResponse();
		break;
	case 'replace_default_pin':
		$ajax->replaceDefaultPin();
		$ajax->sendResponse();
		break;
	default:
		$ajax->setError(_OTP_ERROR_, 'Invalid request');
		$ajax->sendResponse();
		break;
}
