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

	static public function pre_login($login) {

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

		if(isset($_POST['otpPassword'])){
			$otpPassword = $_POST['otpPassword'];
		}

		if ( OCP\Config::getAppValue('user_otp', 'UseUserPrefixPin', 0) &&
			(	OCP\Config::getAppValue('user_otp', 'UserPrefixPin', '') !== '' ||
				( $mOtp->GetUserPrefixPin() !== 0 &&
					$mOtp->GetUserPin !== '' )
			)
		) {
			$otpPwLen = strlen($otpPassword);
			$userPinLen = strlen($mOtp->GetUserPin);
			$defaultPinLen = strlen(OCP\Config::getAppValue('user_otp', 'UserPrefixPin', ''));
			$tokenLen = OCP\Config::getAppValue('user_otp', 'UserTokenNumberOfDigits', 6);
var_dump(__LINE__);
var_dump(__FILE__);
var_dump($otpPwLen);
var_dump($userPinLen);
var_dump($defaultPinLen);
var_dump($tokenLen);
var_dump(' ' . $otpPwLen . ' = ' . $tokenLen . ' + ' . $userPinLen);
var_dump(' ' . $otpPwLen . ' = ' . $tokenLen . ' + ' .  $defaultPinLen);
exit;
			if ($otpPwLen !== $tokenLen + $userPinLen || $otpPwLen !== $tokenLen + $defaultPinLen) {
				self::displayLoginPage('', array(
					'code' => 'otppinmissing',
					'text' => 'Required prefix pin is missing'
				));
				exit();
			}
		}

		if(!isset($otpPassword) || $otpPassword === ''){
			self::displayLoginPage('', array(
					'code' => 'otpmissing',
					'text' => 'The OTP token is missing'
				));
			exit();
		}

		$result = $mOtp->CheckToken($otpPassword);
		if ($result===0){
			return true;
		}else{
			if(isset($mOtp->_errors_text[$result])){
				self::displayLoginPage('', array(
						'code' => 'otpmissing',
						'text' => $mOtp->_errors_text[$result]
					));
			} else {
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
		OCP\Util::addScript('user_otp', 'error');
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
