<?php
/** @var OC_L10N $l */
/** @var array $_ */
$l = $_['overwriteL10N'];

if ($_['fullname'] !== ''):
	// print greeting with full name if set
	print_unescaped($l->t('Hello %s,', array($_['fullname'])));
else:
	// print greeting with uid otherwise
	print_unescaped($l->t('Hello %s,', array($_['uid'])));
endif;
// add line break
p(PHP_EOL);
?>

<?php p($_['requestor']) ?> requested to sent you your OTP settings for <?php p($_['title']) ?>[<?php 
  // store the url in an array for later listing
	$urls[] = $_['url'];
	// print the index of stored url
	p(count($urls) - 1);
 ?>].


To generate an OTP token you have to install an OTP token generator e.g.

       System		   Software
    Android		Google Authenticator
    SailfishOS (Jolla)	SailOTP
    IOS (iPhone)	Google Authenticator
    Windows Phone	Authenticator

More OTP generators can be found here[<?php
  // store the url in the array
	$urls[] = 'http://www.rcdevs.com/tokens/?type=software';
	// print the index of stored url
	p(count($urls) - 1);
?>].


These are the settings you have to configure your generator with:

<?php
	// process each available config option
	foreach ($_['config'] as $config):
		// skip config options without a value and with type image as they con't be displayed in plain mails
		if (! empty($config['value']) && $config['type'] !== 'image'):
			// print name of option
			p($config['name'] . ': ');
			// print value of option according to type
			switch ($config['type']):
				case 'text':
					p($config['value']);
					break;
				case 'link':
					// check whether url is already in array
					if (! in_array($config['value'], $urls)):
						// if not store the url in the array
						$urls[] = $config['value'];
						$valuestring = count($urls) - 1;
					else:
						$valuestring = array_search($config['value'], $urls);
					endif;
					// print the index of stored url
					p('[' . $valuestring . ']');
					break;
			endswitch;
			// add line break
			p(PHP_EOL);
			// display description if set
    	isset($config['description']) ? p('( ' . $config['description'] . ' )' . PHP_EOL) : '';
			// add line break
			p(PHP_EOL);
		endif;
	endforeach;
 ?>



<?php
	// print urls in format for plain mails
	foreach ($urls as $key => $url):
		print_unescaped('[' . $key . '] ' . $url . PHP_EOL);
	endforeach;

	// add line break
	p(PHP_EOL);
	p(PHP_EOL);

	// print footer
	print_unescaped($_['footer']);
?>
