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

include_once("user_otp/lib/multiotpdb.php");

/**
 * Class for user management with OTP if user exist in otp db
 * act as manager for other backend
 * @package user_otp
 */
class OC_User_OTP {

	public function pre_login($login) {
		\OC::$server->getLogger()->warning('Running for user ' . $login['uid'], array('app' => 'user_otp'));
		$session = OC::$server->getSession();
		if (!empty($session->get('otp_run')) &&
			$session->get('otp_result_code') === 'otpsuccess'
		) {
			return true;
		}
		if (!empty($session->get('otp_run')) &&
				!empty($session->get('otp_result_code')) &&
				$session->get('otp_result_code') !== 'otpsuccess'
		) {
			self::displayLoginPage('', array(
					'code' => $session->get('otp_result_code'),
					'text' => $session->get('otp_result_text')
				));
			$session->clear();
			return true;
		}

		$session->set('otp_run', 'true');

		if (\OCP\App::isEnabled('user_otp') === false) {
			return true;
		}
		// if access is made by remote.php and option is not set to force mtop, keep standard auth methode
		// this for keep working webdav access and sync apps
		// And news api for android new app
		if(
			( 
			basename($_SERVER['SCRIPT_NAME']) === 'remote.php' || 
			preg_match("#^/apps/news/api/v1-2(.*)$#i", $_SERVER['PATH_INFO']) 
			)
			&& OCP\Config::getAppValue('user_otp','disableOtpOnRemoteScript',true)
		){
			return true;
		}

		$user = $login['uid'];
		$password = $login['password'];

		$mOtp =	new MultiOtpDb(OCP\Config::getAppValue(
			'user_otp','EncryptionKey','DefaultCliEncryptionKey')
		);

		if(!$mOtp->CheckUserExists($user)){
			OC_Log::write('OC_USER_OTP','No OTP for user '.$user.' use user backend', OC_Log::DEBUG);
			\OC::$server->getLogger()->warning('No OTP for user ' . $user . ' found.', array('app' => 'user_otp'));
			return true;
		}

		if(defined('DEBUG') && DEBUG===true){
			$mOtp->EnableVerboseLog();
		}

		$mOtp->SetMaxBlockFailures(
			OCP\Config::getAppValue('user_otp','MaxBlockFailures',6)
		);

		$mOtp->SetMaxEventWindow(
			OCP\Config::getAppValue('user_otp','UserTokenMaxEventWindow',100)
		);

		$mOtp->SetUser($user);

		if(OCP\Config::getAppValue('user_otp','inputOtpAfterPwd','0')==='1') {
			$otpSize = $mOtp->GetTokenNumberOfDigits() + (
				strlen($mOtp->GetUserPin())* $mOtp->GetUserPrefixPin()
			);
			$_POST['otpPassword']=substr($password,-$otpSize);
			$password = substr($password,0,strlen($password) - $otpSize);
		}

		if(!isset($_POST['otpPassword']) || $_POST['otpPassword']===""){
			$session->set('otp_result_code', 'otpmissing');
			$session->set('otp_result_text', 'The OTP token is missing');
			OCP\Util::addScript('user_otp', 'error');
			self::displayLoginPage('', array(
					'code' => 'otpmissing',
					'text' => 'The OTP token is missing'
				));
			exit();
		}

		OC_Log::write('OC_USER_OTP','used OTP : '.$_POST['otpPassword'], OC_Log::DEBUG);
		$result = $mOtp->CheckToken($_POST['otpPassword']);
		if ($result===0){
			$session->set('otp_result_code', 'otpsuccess');
			return true;
		}else{
			$session->set('otp_result_code', 'otpwrong');
			OCP\Util::addScript('user_otp', 'error');
			if(isset($mOtp->_errors_text[$result])){
				$session->set('otp_result_text', $mOtp->_errors_text[$result]);
				self::displayLoginPage('', array(
						'code' => 'otpmissing',
						'text' => $mOtp->_errors_text[$result]
					));
			} else {
				$session->set('otp_result_text', 'The OTP token was wrong');
				self::displayLoginPage('', array(
						'code' => 'otpmissing',
						'text' => 'The OTP token was wrong'
					));
			}
			exit();
		}
		return false;
	}

	private function displayLoginPage($errors = array(), $otperror = '') {
		$parameters = array();
		foreach ($errors as $value) {
			$parameters[$value] = true;
		}
		$parameters['otperror'] = $otperror;
		if (!empty($_REQUEST['user'])) {
			$parameters["username"] = $_REQUEST['user'];
			$parameters['user_autofocus'] = false;
		} else {
			$parameters["username"] = '';
			$parameters['user_autofocus'] = true;
		}
		if (isset($_REQUEST['redirect_url'])) {
			$redirectUrl = $_REQUEST['redirect_url'];
			$parameters['redirect_url'] = urlencode($redirectUrl);
		}

		$parameters['alt_login'] = OC_App::getAlternativeLogIns();
		$parameters['rememberLoginAllowed'] = OC_Util::rememberLoginAllowed();
		OC_Template::printGuestPage('user_otp', 'login', $parameters);
	}

}
?>
