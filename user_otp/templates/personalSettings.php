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
?>
<form id="user_otp" class="section">
	<h2>OTP Configuration</h2>
	<?php if($_['UserExists']) { ?>
		<p>
			<label for="UserTokenSeed">User Token Seed:</label><br />
			<input class="otpApplicable" type="text"
				name="UserTokenSeed"
				value="<?php p($_['UserTokenSeed']) ?>"
				pattern="^[ABCDEFGHIJKLMNOPQRSTUVWXYZ234567]*$" /><br />
			<em>
				if left blank, it will be generated automatically<br />
				NOTICE: Only the following characters are allowed:<br />
				&nbsp;&nbsp; <?php p(_OTP_VALID_CHARS_) ?>
			</em><br />
		<?php if($_['UserPrefixPin']){ ?>
			<label for="UserPin">User Pin:</label><br />
			<input class="otpApplicable" type="number"
				name="UserPin"
				value="<?php p($_['UserPin']); ?>"><br />
			<em>if left blank, it will be generated automatically</em><br />
			<br />
		<?php } ?>
		<?php if($_['UserLocked']){ ?>
			<br />
			<strong>User is locked</strong>
		<?php } ?>
		</p>
		<p>
			User Algorithm : <?php p($_['UserAlgorithm']); ?><br />
		<?php if ($_['UserAlgorithm'] === 'TOTP'): ?>
			User Token Time Interval
		<?php elseif ($_['UserAlgorithm'] === 'HOTP'): ?>
			Last Event
		<?php endif ?>:
		<?php p($_['UserTokenTimeIntervalOrLastEvent']); ?>
		</p>
		<p>
			Token Url Link : <a href="<?php p($_['UserTokenUrlLink']); ?>"><?php p($_['UserTokenUrlLink']); ?></a>
		</p>
		<p>
			With android token apps select base32 before input seed<br/>
			UserTokenQrCode :<br /><img src="<?php p($_['UserTokenQrCode']); ?>">
		</p>
		<p>
			<br />
			<input class="otp_submit_action" type='submit' value='Update' />
		<?php if(!$_['!disableDeleteOtpForUsers']): ?>				
			<input class="otp_submit_action" type='button' value='Delete' />
		<?php endif ?>
		</p>

	<?php }else{ ?>
		<p>
			<label for="UserTokenSeed">User Token Seed:</label><br />
			<input class="otpApplicable" type="text" name="UserTokenSeed" value="" pattern="^[ABCDEFGHIJKLMNOPQRSTUVWXYZ234567]*$"><br />
			<em>
				if left blank, it will be generated automatically<br />
				NOTICE: Only the following characters are allowed:<br />
				&nbsp;&nbsp; <?php p(_OTP_VALID_CHARS_) ?>
			</em><br />
			<?php if($_['UserPrefixPin']){ ?>
				<label for="UserPin">User Pin:</label><br />
				<input class="otpApplicable" type="number" name="UserPin" value=""><br />
				<em>if left blank, it will be generated automatically</em><br />
			<?php } ?>
			<br />
			<input type="hidden" name="otp_action" value="create_otp">
			<input class="otp_submit_action" type='submit' value='Generate'>
		</p>
	<?php } ?>
</form>
