$(document).ready(function() {
	// save checkbox settings
  $('input[type="checkbox"].otpApplicable').on('change', function() {
		if (this.checked) {
			OC.AppConfig.setValue('user_otp', this.name, '1');

			// save options which depend on changed option
			$('.' + this.name).each(function (index) {
				option = $(this).find('.otpApplicable');
				if (option.attr('type') === 'checkbox') {
					if (option.checked) {
						OC.AppConfig.setValue('user_otp', option.attr('name'), '1');
					} else {
						OC.AppConfig.setValue('user_otp', option.attr('name'), '0');
					}
				} else {
					OC.AppConfig.setValue('user_otp', option.attr('name'), option.val());
				}
				// show saved message
				$('[for=' + option.attr('name') + ']').append('<span class="' + option.attr('name') + ' msg success">Saved</span>');
				$('.' + option.attr('name') + '.msg').delay(2000).fadeOut(400);
			});
		} else {
			OC.AppConfig.setValue('user_otp', this.name, '0');
		}

		// show saved message
		$('[for=' + this.name + ']').append('<span class="' + this.name + ' msg success">Saved</span>');
		$('.' + this.name + '.msg').delay(2000).fadeOut(400);

		// toggle option which depend on changed option
		$('.' + this.name).toggleClass('hidden');
	});
	// save text input settings
  $('input[type="text"].otpApplicable').on('focusout', function() {
		if (this.checkValidity()) {
			if (this.value !== '') {
				OC.AppConfig.setValue('user_otp', this.name, this.value);
			} else {
				$('[name=' + this.name + ']').val($('[name=' + this.name + '_default]').val());
				OC.AppConfig.setValue('user_otp', this.name, $('[name=' + this.name + '_default]').val());
			}
			// show saved message
			$('[for=' + this.name + ']').append('<span class="' + this.name + ' msg success">Saved</span>');
			$('.' + this.name + '.msg').delay(2000).fadeOut(400);
		}
	});
	// save number input settings
  $('input[type="number"].otpApplicable').on('focusout', function() {
		if (this.checkValidity()) {
			if (this.value !== '') {
				OC.AppConfig.setValue('user_otp', this.name, this.value);
			} else {
				$('[name=' + this.name + ']').val($('[name=' + this.name + '_default]').val());
				OC.AppConfig.setValue('user_otp', this.name, $('[name=' + this.name + '_default]').val());
			}
			// show saved message
			$('[for=' + this.name + ']').append('<span class="' + this.name + ' msg success">Saved</span>');
			$('.' + this.name + '.msg').delay(2000).fadeOut(400);
		}
	});
	// save select settings
  $('select.otpApplicable').on('change', function() {
		OC.AppConfig.setValue('user_otp', this.name, this.value);

		$('.' + this.name + '_not_' + this.value).addClass('hidden');
		$('.' + this.name + '_' + this.value).removeClass('hidden');
		$('.' + this.name + '_' + this.value).each(function (index) {
			option = $(this).find('.otpApplicable');
			OC.AppConfig.setValue('user_otp', option.attr('name'), option.val())

			// show saved message
			$('[for=' + option.attr('name') + ']').append('<span class="' + option.attr('name') + ' msg success">Saved</span>');
			$('.' + option.attr('name') + '.msg').delay(2000).fadeOut(400);
		});

		// show saved message
		$('[for=' + this.name + ']').append('<span class="' + this.name + ' msg success">Saved</span>');
		$('.' + this.name + '.msg').delay(2000).fadeOut(400);
	});
	// show every element which has the selected element as dependency (provided class is set)
	$('.provider:selected').each(function (index) {
		$('.' + $(this).attr('data-otp-provides')).removeClass('hidden');
	});
	// show every element which has the checked element as dependency (provided class is set)
	$('[data-otp-provides]:checked').each(function (index) {
		$('.' + $(this).attr('data-otp-provides')).removeClass('hidden');
	});
});
