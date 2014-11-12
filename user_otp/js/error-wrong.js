$(document).ready(function() {
	$('.login').after('<div class="error otpError">The given OTP Token was wrong.</div>');
  $('.otpError').css('margin-top', '60px').delay(2000).fadeOut(400);
});
