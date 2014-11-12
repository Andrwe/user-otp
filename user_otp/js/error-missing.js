$(document).ready(function() {
	$('.login').after('<div class="error otpError">The OTP token was missing.</div>');
  $('.otpError').css('margin-top', '60px').delay(2000).fadeOut(400);
});
