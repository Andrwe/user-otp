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
	<?php p($_['requestor']) ?> requested the deletion of the OTP token for your account for <a href="<?php print_unescaped($_['url']) ?>"><?php p($_['title']) ?></a>.
	<br />
	The next time you login to <a href="<?php print_unescaped($_['url']) ?>"><?php p($_['title']) ?></a> you don't have to provide an OTP token.
</p>

<?php print_unescaped($_['footer']) ?>
