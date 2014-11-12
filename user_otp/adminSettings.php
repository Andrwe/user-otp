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
$configOtp[$i]['name']          = 'EncryptionKey'; 
$configOtp[$i]['label']         = 'Encryption Key';
$configOtp[$i]['description']   = 'NOTICE: if left blank, it will be generated automatically';
$configOtp[$i]['type']          = 'text';
$VALID_CHAR = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghiklmnopqrstuvwxyz";
$configOtp[$i]['pattern']       = $VALID_CHAR;
$configOtp[$i]['default_value'] = generateRandomString(16,32,2,$VALID_CHAR); $i++;

$configOtp[$i]['name']          = 'MaxBlockFailures'; 
$configOtp[$i]['label']         = 'Max try before a temporary block';
$configOtp[$i]['type']          = 'number';
$configOtp[$i]['default_value'] = '6'; $i++;

$configOtp[$i]['name']          = 'UserPrefixPin'; 
$configOtp[$i]['label']         = 'User Prefix Pin';
$configOtp[$i]['description']   = 'add a 4 digit fix prefix before token';
$configOtp[$i]['type']          = 'number';
$configOtp[$i]['default_value'] = false; $i++;

$configOtp[$i]['name']          = 'UserAlgorithm'; 
$configOtp[$i]['label']         = 'User Algorithm (TOTP/HOTP)';
$configOtp[$i]['type']          = 'select';
$configOtp[$i]['default_value'] = 'TOTP'; 
$configOtp[$i]['values']['TOTP']['value'] = 'TOTP';  
$configOtp[$i]['values']['TOTP']['label'] = 'TOTP';
$configOtp[$i]['values']['HOTP']['value'] = 'HOTP';  
$configOtp[$i]['values']['HOTP']['label'] = 'HOTP';$i++;

$configOtp[$i]['name']          = 'UserTokenNumberOfDigits'; 
$configOtp[$i]['label']         = 'User Token Number Of Digits';
$configOtp[$i]['description']   = 'NOTICE: must be 6 in order to works with Google Authenticator';
$configOtp[$i]['type']          = 'number';
$configOtp[$i]['default_value'] = '6'; $i++;

$configOtp[$i]['name']          = 'UserTokenTimeIntervalOrLastEvent'; 
$configOtp[$i]['label']         = 'User Token Time Interval (TOTP) / Last Event (HOTP)';
$configOtp[$i]['description']   = 'TOTP: time in seconde between two TOTP (must be 30 in order to works with Google Authenticator)<br />HOTP: number of past HOTP (If you’ve just re-initialised your Yubikey, then set this to 0) ';
$configOtp[$i]['type']          = 'number';
$configOtp[$i]['default_value'] = '30'; $i++;

$configOtp[$i]['name']          = 'UserTokenMaxEventWindow'; 
$configOtp[$i]['label']         = 'User Token Max Event Window (default : 100)';
$configOtp[$i]['type']          = 'number';
$configOtp[$i]['default_value'] = '100'; $i++;

$configOtp[$i]['name']          = 'disableOtpOnRemoteScript'; 
$configOtp[$i]['label']         = 'Disable OTP with remote.php (webdav and sync)';
$configOtp[$i]['type']          = 'checkbox';
$configOtp[$i]['default_value'] = true; $i++;

$configOtp[$i]['name']          = 'disableDeleteOtpForUsers'; 
$configOtp[$i]['label']         = 'Disable delete OTP for users (only regenerated)';
$configOtp[$i]['type']          = 'checkbox';
$configOtp[$i]['default_value'] = false; $i++;

$configOtp[$i]['name']          = 'inputOtpAfterPwd'; 
$configOtp[$i]['label']         = 'Used password field only and add OTP after the password';
$configOtp[$i]['type']          = 'checkbox';
$configOtp[$i]['default_value'] = false;

$i=0;
foreach ($configOtp as $option) {
	$configOtp[$i]['value'] = OCP\Config::getAppValue('user_otp', $option['name'], $option['default_value']);
  $i++;
}

$tmpl->assign('configOtp', $configOtp);

return $tmpl->fetchPage();
