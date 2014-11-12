$(document).ready(function() {
	$('.login').after('<div class="error otpError">An unknown error occured while checking the OTP token.</div>');
  $('.otpError').css('margin-top', '60px').delay(2000).fadeOut(400);
});
