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

you get this mail because <?php print_unescaped($_['url']) ?> generated an OTP token for you.

The next time you login to <?php print_unescaped($_['url']) ?> you have to provide a valid OTP token.

To generate this token you have to install an OTP token generator e.g.

	System	Software
	Android	Google Authenticator
	SailfishOS (Jolla)	SailOTP
	IOS (iPhone)	Google Authenticator
	Windows Phone	Authenticator

More can be found on http/www.rcdevs.com/tokens/?type=software.


The following are the settings you have to use to configure your generator:

			<td>Setting</td>
			<td>Value</td>
	<?php
		foreach ($_['config'] as $config):
			if (! empty($config['value'])):
	?>
			<td><?php p($config['name']) ?></td>
			<?php
				switch ($config['type']) {
					case 'text':
						p($config['value']);
						break;
					case 'image':
						print_unescaped('<img src="data:image/png;base64,' . $config['value'] . '"/>');
						break;
					case 'link':
						print_unescaped('<a href="' . $config['value'] . '" />');
						break;
				} 
			?>
	<?php
			endif;
		endforeach;
	 ?>

