<?php
/** @var OC_L10N $l */
/** @var array $_ */
$l = $_['overwriteL10N'];

if ($_['fullname'] !== '') {
	// print greeting with full name if set
	print_unescaped($l->t('Hello %s,', array($_['fullname'])));
} else {
	// print greeting with uid otherwise
	print_unescaped($l->t('Hello %s,', array($_['uid'])));
}
?>

<p>
	<?php p($_['requestor']) ?> requested the creation of an OTP token for your account for <a href="<?php print_unescaped($_['url']) ?>"><?php p($_['title']) ?></a>.
</p>

<p>
	The next time you login to <a href="<?php print_unescaped($_['url']) ?>"><?php p($_['title']) ?></a> you have to provide a valid OTP token.
	<br />
	To generate this token you have to install an OTP token generator e.g.
  <table width="300px" style="border-spacing: 0px 10px;">
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
	More OTP generators can be found on <a href="http://www.rcdevs.com/tokens/?type=software">http://www.rcdevs.com/tokens/?type=software</a>.
</p>
<br />
<br />
<br />
<p>
These are the settings you have to configure your generator with:

	<table width="100%" style="border-spacing: 0px 10px;">
		<thead>
			<tr>
				<th>Option</th>
				<th>Value</th>
				<th>Description</th>
			</tr>
		</thead>
		<tbody>
		<?php
			// process each available config option
			foreach ($_['config'] as $config):
				// skip config options without a value as they can't be displayed
				if (! empty($config['value'])):
		?>
			<tr style="vertical-align:top">
				<td><?php p($config['name']) ?></td>
				<td>
				<?php
					// print value of option according to type
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

<?php print_unescaped($_['footer']) ?>
