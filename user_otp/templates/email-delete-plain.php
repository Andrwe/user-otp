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

<?php p($_['requestor']) ?> requested the deletion of the OTP token for your account for <?php p($_['title']) ?>[<?php 
  // store the url in an array for later listing
	$urls[] = $_['url'];
	// print the index of stored url
	p(count($urls) - 1);
 ?>].

The next time you login to <?php p($_['title']) ?>[<?php
	// check whether url is already in array
	if (! in_array($_['url'], $urls)):
		// if not store the url in the array
		$urls[] = $_['url'];
		// print the index of stored url
		p(count($urls) - 1);
	else:
		// print the index of stored url
		p(array_search($_['url'], $urls));
	endif;
?>] you don't have to provide an OTP token.

<?php 
	// print footer
	print_unescaped($_['footer']);
?>
