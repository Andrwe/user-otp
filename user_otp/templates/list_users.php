<?php
/**
 * Copyright (c) 2011, Robin Appelman <icewind1991@gmail.com>
 * This file is licensed under the Affero General Public License version 3 or later.
 * See the COPYING-README file.
 */
?>

<table class="hascontrols grid">
	<thead>
		<tr>
			<?php if ($_['enableAvatars']): ?>
			<th id='headerAvatar'></th>
			<?php endif; ?>
			<th id='headerName'><?php p($l->t('Username'))?></th>
			<th id="headerLocked"><?php p($l->t( 'Locked' )); ?></th>
			<th id="headerAlgorithm"><?php p($l->t( 'Algorithm' )); ?></th>
			<?php if($_['PrefixPin']):?>
			<th id="headerUserPin"><?php p($l->t( 'UserPin' )); ?></th>
			<?php endif;?>
			<th id="headerRemove" class='otpButton'>Delete OTP</th>
			<th id="headerCreate" class='otpButton'>Create OTP</th>
			<th id="headerSendEmail" class='otpButton'>Send Email</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($_["users"] as $user): ?>
		<tr data-uid="<?php p($user["name"]) ?>"
			data-displayName="<?php p($user["displayName"]) ?>">
			<?php if ($_['enableAvatars']): ?>
			<td class="avatar"><div class="avatardiv"></div></td>
			<?php endif; ?>
			<td class="name"><?php p($user["name"]); ?></td>
			<td class="otpTextCenter"><span><?php p($user["UserLocked"]); ?></span></td>
			<td class="otpTextCenter"><span><?php p($user["UserAlgorithm"]); ?></span></td>
			<?php if($_['PrefixPin']):?>
			<td class="UserPin">
				  <span><?php p($user["UserPin"]); ?></span>
			</td>
			<?php endif;?>
			
			<td class="otpTextCenter">
				<?php if($user["OtpExist"]):?>
					<a href="#" class="delete" original-title="<?php p($l->t('Delete OTP'))?>">
						<img class="action" src="<?php print_unescaped(image_path('core', 'actions/delete.svg')) ?>" class="svg" />
					</a>
				<?php endif;?>
			</td>
			<td class="otpTextCenter">
				<?php if(!$user["OtpExist"]):?>
					<a href="#" class="add" original-title="<?php p($l->t('Create OTP'))?>">
						<img class="action" src="<?php print_unescaped(image_path('core', 'actions/add.svg')) ?>" class="svg" />
					</a>
				<?php endif;?>
			</td>
			<td class="otpTextCenter">
				<?php if($user["OtpExist"]):?>
					<a href="#" class="send-email" original-title="<?php p($l->t('Send email'))?>">
						<img class="action" src="<?php print_unescaped(image_path('core', 'actions/mail.svg')) ?>" class="svg" />
					</a>
				<?php endif;?>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
