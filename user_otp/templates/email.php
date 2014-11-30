<?php
/** @var OC_L10N $l */
/** @var array $_ */
$l = $_['overwriteL10N'];

if ($_['fullname'] !== '') {
	print_unescaped($l->t('Hello %s,', array($_['fullname'])));
} else {
	print_unescaped($l->t('Hello %s,', array($_['uid'])));
}
?>

<p>
	you get this mail because <?php print_unescaped($_['url']) ?> generated an OTP token for you.
</p>

<p>
	The next time you login to <?php print_unescaped($_['url']) ?> you have to provide a valid OTP token.
	To generate this token you have to install an OTP token generator e.g.
  <table>
		<thead>
			<tr>
				<th>System</th>
				<th>Software</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>Android</td>
				<td>Google Authenticator</td>
			</tr>
			<tr>
				<td>SailfishOS (Jolla)</td>
				<td>SailOTP</td>
			</tr>
			<tr>
				<td>IOS (iPhone)</td>
				<td>Google Authenticator</td>
			</tr>
			<tr>
				<td>Windows Phone</td>
				<td>Authenticator</td>
			</tr>
		</tbody>
	</table>
<br />
More can be found on http/www.rcdevs.com/tokens/?type=software.
<p>
<p>
The following are the settings you have to use to configure your generator:

	<p>
		<table>
			<thead>
				<tr>
					<td>Setting</td>
					<td>Value</td>
					<td>Description</td>
				</tr>
			</thead>
			<tbody>
			<?php
				foreach ($_['config'] as $config):
					if (! empty($config['value'])):
			?>
				<tr>
					<td><?php p($config['name']) ?></td>
					<td>
					<?php
						switch ($config['type']) {
							case 'text':
								p($config['value']);
								break;
							case 'image':
								print_unescaped('<img src="data:image/png;base64,' . $config['value'] . '"/>');
								break;
							case 'link':
								print_unescaped('<a href="' . $config['value'] . '">' . $config['value'] . '</a>');
								break;
						} 
					?>
					</td>
					<td>
						<?php isset($config['description']) ? p($config['description']) : '' ?>
					</td>
				</tr>
			<?php
					endif;
				endforeach;
			 ?>
			</tbody>
		</table>
	</p>
</p>

