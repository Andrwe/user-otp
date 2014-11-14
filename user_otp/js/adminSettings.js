$(document).ready(function() {
	// safe checkbox settings
  $('input[type="checkbox"].otpApplicable').on('change', function() {
		if (this.checked) {
			OC.AppConfig.setValue('user_otp', this.name, '1');
		} else {
			OC.AppConfig.setValue('user_otp', this.name, '0');
		}
	});
	// safe text input settings
  $('input[type="text"].otpApplicable').on('focusout', function() {
		if (this.checkValidity()) {
			if (this.value !== '') {
				OC.AppConfig.setValue('user_otp', this.name, this.value);
			} else {
				$('[name=' + this.name + ']').val($('[name=' + this.name + '_default]').val());
				OC.AppConfig.setValue('user_otp', this.name, $('[name=' + this.name + '_default]').val());
			}
		}
	});
	// safe number input settings
  $('input[type="number"].otpApplicable').on('focusout', function() {
		if (this.checkValidity()) {
			if (this.value !== '') {
				OC.AppConfig.setValue('user_otp', this.name, this.value);
			} else {
				$('[name=' + this.name + ']').val($('[name=' + this.name + '_default]').val());
				OC.AppConfig.setValue('user_otp', this.name, $('[name=' + this.name + '_default]').val());
			}
		}
	});
	// safe select settings
  $('select.otpApplicable').on('change', function() {
		OC.AppConfig.setValue('user_otp', this.name, this.value);
	});
});
