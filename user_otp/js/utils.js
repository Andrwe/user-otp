$(document).ready(function(){
	$('#password').parent().removeClass("infield groupbottom");
	$('#password').parent().addClass("infield groupmiddle");
	$('#password').attr( "autocomplete", "on" );
	$('#password').parent().after(
		'<p class="infield groupbottom">'+
		'<input id="otpPassword" type="number" placeholder="One Time Password" value=""' +
			' name="otpPassword" original-title="" autocomplete="off" >' +
		'<label class="infield" for="otpPassword" style="opacity: 1;">One Time Password</label>' +
		'<img id="password-icon" class="svg" alt="" class="otppassword-icon" src="/core/img/actions/password.svg">' +
		'</p>'
	);
	$("#submit").removeAttr("disabled");
});

