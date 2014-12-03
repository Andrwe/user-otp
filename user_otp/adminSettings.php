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

OC_Util::checkAdminUser();

OCP\Util::addscript('user_otp', 'adminSettings');
//OCP\Util::addstyle('user_otp', 'settings');

$tmpl = new OCP\Template('user_otp', 'adminSettings');

// input type process tab OTP config
$i=0;
$configOtp[] = array(
			'name'          => 'EncryptionKey',
			'label'         => 'Encryption Key',
			'description'   => 'NOTICE: if left blank, it will be generated automatically<br />NOTICE: Only the following characters are allowed:<br />&nbsp;&nbsp;' . _OTP_VALID_CHARS_,
			'type'          => 'text',
			'pattern'       => _OTP_VALID_CHARS_,
			'default_value' => generateRandomString(16,32,2,_OTP_VALID_CHARS_)
		);
$configOtp[] = array(
			'name'          => 'UserAlgorithm',
			'label'         => 'User Algorithm (TOTP/HOTP)',
			'type'          => 'select',
			'default_value' => 'TOTP',
			'values'        => array(
				'TOTP'    => array(
					'value' => 'TOTP',
					'label' => 'TOTP',
				),
				'HOTP'    => array(
					'value' => 'HOTP',
					'label' => 'HOTP',
				)
			),
		);
$configOtp[] = array(
			'name'          => 'UserTokenTimeIntervalOrLastEvent',
			'label'         => 'User Token Time Interval (TOTP)',
			'description'   => 'time in seconde between two TOTP (must be 30 in order to works with Google Authenticator)',
			'type'          => 'number',
			'default_value' => '30',
			'classes'       => 'hidden hasDependency UserAlgorithm_TOTP UserAlgorithm_not_HOTP',
		);
$configOtp[] = array(
			'name'          => 'UserTokenTimeIntervalOrLastEvent',
			'label'         => 'Last Event (HOTP)',
			'description'   => 'number of past HOTP (If you’ve just re-initialised your Yubikey, then set this to 0) ',
			'type'          => 'number',
			'default_value' => '0',
			'classes'       => 'hidden hasDependency UserAlgorithm_HOTP UserAlgorithm_not_TOTP',
		);
$configOtp[] = array(
			'name'          => 'MaxBlockFailures',
			'label'         => 'Max try before a temporary block',
			'type'          => 'number',
			'default_value' => '6',
		);
$configOtp[] = array(
			'name'          => 'UseUserPrefixPin',
			'label'         => 'activate user prefix pin',
			'description'   => 'If active users have to prefix their token with a 4 digit pin.<br />You may define a default pin which might be overwritten by the user.',
			'type'          => 'checkbox',
			'default_value' => false,
		);
$configOtp[] = array(
			'name'          => 'AllowUserPrefixPinOverride',
			'label'         => 'allow user defined prefix pin',
			'description'   => 'If active users may overwrite the default pin',
			'type'          => 'checkbox',
			'default_value' => false,
			'classes'       => 'hidden hasDependency UseUserPrefixPin',
		);
$configOtp[] = array(
			'name'          => 'UserPrefixPin',
			'label'         => 'User Prefix Pin',
			'description'   => 'set a default 4 digit pin as token prefix',
			'type'          => 'number',
			'default_value' => '',
			'classes'       => 'hidden hasDependency UseUserPrefixPin',
		);
$configOtp[] = array(
			'name'          => 'UserTokenNumberOfDigits',
			'label'         => 'User Token Number Of Digits',
			'description'   => 'NOTICE: must be 6 in order to works with Google Authenticator',
			'type'          => 'number',
			'default_value' => '6',
		);
$configOtp[] = array(
			'name'          => 'UserTokenMaxEventWindow',
			'label'         => 'User Token Max Event Window (default : 100)',
			'type'          => 'number',
			'default_value' => '100',
		);
$configOtp[] = array(
			'name'          => 'disableOtpOnRemoteScript',
			'label'         => 'Disable OTP with remote.php (webdav and sync)',
			'type'          => 'checkbox',
			'default_value' => true,
		);
$configOtp[] = array(
			'name'          => 'disableDeleteOtpForUsers',
			'label'         => 'Disable delete OTP for users (only regenerated)',
			'type'          => 'checkbox',
			'default_value' => false,
		);
$configOtp[] = array(
			'name'          => 'inputOtpAfterPwd',
			'label'         => 'Used password field only and add OTP after the password',
			'type'          => 'checkbox',
			'default_value' => false,
		);

$i=0;
foreach ($configOtp as $option) {
	$configOtp[$i]['value'] = OCP\Config::getAppValue('user_otp', $option['name'], $option['default_value']);
  $i++;
}

$tmpl->assign('configOtp', $configOtp);

return $tmpl->fetchPage();
