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
<form id='user_otp' class='section'>
	<h2><?php p($l->t('One Time Password')); ?></h2>

	<?php foreach ($_['configOtp'] as $option): ?>
		<div class="<?php isset($option['classes']) ? p($option['classes']) : '' ?>">
			<p>
				<?php if ($option['type'] === 'checkbox'): ?>

					<input class="otpApplicable" type="<?php p($option['type']) ?>"
						name="<?php p($option['name']) ?>"
						<?php
							$option['value'] ? p('checked=checked ') : ''; 
							p('data-otp-provides=' . $option['name']) . ' ';
						?>
					/>
					<label for="<?php p($option['name']) ?>">
						<?php p($option['label']) ?>
					</label>

				<?php elseif ($option['type'] === 'text' or $option['type'] === 'number'): ?>

					<label for="<?php p($option['name']) ?>">
						<?php p($option['label']) ?>:
					</label><br />

					<input class="otpApplicable" type="<?php p($option['type']) ?>"
						name="<?php p($option['name']) ?>"
						value="<?php p($option['value']) ?>"
						<?php isset($option['pattern']) ? p('pattern=^[' . $option['pattern'] . ']*$') : '' ?>
					/>
					<input class="hidden"
						name="<?php p($option['name']) ?>_default"
						value="<?php p($option['default_value']) ?>"
					/>

				<?php elseif ($option['type'] === 'select'): ?>

					<label for="<?php p($option['name']) ?>">
						<?php p($option['label']) ?>:
					</label><br />

					<select class="otpApplicable" name="<?php p($option['name']) ?>">
					<?php foreach ($option['values'] as $value): ?>
						<option value="<?php p($value['value']) ?>"
							<?php
								$value['value'] == $option['value'] ? p('selected=selected ') : '';
								p('data-otp-provides=' . $option['name'] . '_' . $value['value']) . ' ';
							?>
						>
							<?php p($value['label']) ?>
						</option>
					<?php endforeach ?>
					</select>

				<?php endif; ?>

				<?php if (isset($option['description'])):	?>
					<br />
					<em><?php print_unescaped($option['description']) ?></em>
				<?php endif ?>
			</p>

			<?php if ($option['type'] !== 'checkbox' || isset($option['description'])): ?>
				<br />
			<?php endif ?>
		</div>
	<?php endforeach; ?>
</form>
