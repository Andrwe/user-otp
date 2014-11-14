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

define('_OTP_VALID_CHARS_', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghiklmnopqrstuvwxyz');

OC::$CLASSPATH['OC_User_OTP'] = 'user_otp/lib/otp.php';
OCP\Util::connectHook('OC_User', 'pre_login', 'OC_User_OTP', 'pre_login');
//OCP\Util::connectHook('OC_User', 'logout', 'OC_User_OTP', 'logout');

OCP\App::registerAdmin('user_otp','adminSettings');
OCP\App::registerPersonal('user_otp','personalSettings');

if (!OCP\User::isLoggedIn()){
	if (OCP\Config::getAppValue('user_otp','inputOtpAfterPwd','0')!=='1') {
		// Load js code in order to add passco fix node field into the normal login form
		OCP\Util::addScript('user_otp', 'utils');
		OCP\Util::addStyle('user_otp', 'styles');
	}
} else {
	$isadmin = OC_User::isAdminUser(OC_User::getUser());
	if($isadmin){
		\OCP\App::addNavigationEntry(array(
	
	    // the string under which your app will be referenced in owncloud
	    'id' => 'user_otp',
	
	    // sorting weight for the navigation. The higher the number, the higher
	    // will it be listed in the navigation
	    'order' => 74,
	
	    // the route that will be shown on startup
	    'href' => \OCP\Util::linkToRoute('user_otp_list_users'),
	
	    // the icon that will be shown in the navigation
	    // this file needs to exist in img/example.png
	    'icon' => \OCP\Util::imagePath('settings', 'admin.svg'),
	
	    // the title of your application. This will be used in the
	    // navigation or on the settings page of your app
	    'name' => 'OTP Users'
	));
	}
}
?>
