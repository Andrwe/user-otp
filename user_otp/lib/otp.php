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
class OC_User_OTP extends OC_User_Backend{

	public function pre_login($login) {

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
		$mOtp =	new MultiOtpDb(OCP\Config::getAppValue(
			'user_otp','EncryptionKey','DefaultCliEncryptionKey')
		);

		if(defined('DEBUG') && DEBUG===true){
			$mOtp->EnableVerboseLog();
		}

		$mOtp->SetMaxBlockFailures(
			OCP\Config::getAppValue('user_otp','MaxBlockFailures',6)
		);

		$mOtp->SetMaxEventWindow(
			OCP\Config::getAppValue('user_otp','UserTokenMaxEventWindow',100)
		);

		if(!$mOtp->CheckUserExists($user)){
			OC_Log::write('OC_USER_OTP','No OTP for user '.$user.' use user backend', OC_Log::DEBUG);
			return true;
		}

		$mOtp->SetUser($user);

		if(OCP\Config::getAppValue('user_otp','inputOtpAfterPwd','0')==='1') {
			$otpSize = $mOtp->GetTokenNumberOfDigits() + (
				strlen($mOtp->GetUserPin())* $mOtp->GetUserPrefixPin()
			);
			$_POST['otpPassword']=substr($password,-$otpSize);
			$password = substr($password,0,strlen($password) - $otpSize);
		}

		if(!isset($_POST['otpPassword']) || $_POST['otpPassword']===""){
			OCP\Util::addScript('user_otp', 'error-missing');
			OC_Util::displayLoginPage();
			exit();
		}

		OC_Log::write('OC_USER_OTP','used OTP : '.$_POST['otpPassword'], OC_Log::DEBUG);
		$result = $mOtp->CheckToken($_POST['otpPassword']);
		if ($result===0){
			return true;
		}else{
			if(isset($mOtp->_errors_text[$result])){
				echo $mOtp->_errors_text[$result];
			}
			OCP\Util::addScript('user_otp', 'error-wrong');
			OC_Util::displayLoginPage();
			exit();
		}
		OCP\Util::addScript('user_otp', 'error-unknown');
		OC_Util::displayLoginPage();
		exit();
	}
}
?>
