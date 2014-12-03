$(document).ready(function() {
	// show every element which has the selected element as dependency (provided class is set)
	$('[data-otp-provides]:selected').each(function (index) {
		$('.' + $(this).attr('data-otp-provides')).removeClass('hidden');
	});
	// show every element which has the checked element as dependency (provided class is set)
	$('[data-otp-provides]:checked').each(function (index) {
		$('.' + $(this).attr('data-otp-provides')).removeClass('hidden');
	});

	// save checkbox settings
	$('input[type="checkbox"].otpApplicable').on('change', function() {
		if (this.checked) {
			OC.msg.startSaving('#' + this.name + 'Msg');
			OC.AppConfig.setValue('user_otp', this.name, '1');

			// save options which depend on changed option
			$('.' + this.name).each(function (index) {
				option = $(this).find('.otpApplicable');
				OC.msg.startSaving('#' + option.attr('name') + 'Msg');
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
				OC.msg.finishedSaving('#' + option.attr('name') + 'Msg', {status: 'success', data: {message: t('settings', 'Saved')}});
			});
		} else {
			OC.AppConfig.setValue('user_otp', this.name, '0');
		}

		OC.msg.finishedSaving('#' + this.name + 'Msg', {status: 'success', data: {message: t('settings', 'Saved')}});

		// toggle option which depend on changed option
		$('.' + this.name).toggleClass('hidden');
	});
	// save text input settings
	$('input[type="text"].otpApplicable').on('focusout', function() {
		if (this.checkValidity()) {
			OC.msg.startSaving('#' + this.name + 'Msg');
			if (this.value !== '') {
				OC.AppConfig.setValue('user_otp', this.name, this.value);
			} else {
				$('[name=' + this.name + ']').val($('[name=' + this.name + '_default]').val());
				OC.AppConfig.setValue('user_otp', this.name, $('[name=' + this.name + '_default]').val());
			}
			// show saved message
			OC.msg.finishedSaving('#' + this.name + 'Msg', {status: 'success', data: {message: t('settings', 'Saved')}});
		}
	});
	// save number input settings
  $('input[type="number"].otpApplicable').on('focusout', function() {
		if (this.checkValidity()) {
			OC.msg.startSaving('#' + this.name + 'Msg');
			if (this.value !== '') {
				OC.AppConfig.setValue('user_otp', this.name, this.value);
			} else {
				$('[name=' + this.name + ']').val($('[name=' + this.name + '_default]').val());
				OC.AppConfig.setValue('user_otp', this.name, $('[name=' + this.name + '_default]').val());
			}
			// show saved message
			OC.msg.finishedSaving('#' + this.name + 'Msg', {status: 'success', data: {message: t('settings', 'Saved')}});
		}
	});
	// save select settings
	$('select.otpApplicable').on('change', function() {
		OC.msg.startSaving('#' + this.name + 'Msg');
		OC.AppConfig.setValue('user_otp', this.name, this.value);

		$('.' + this.name + '_not_' + this.value).addClass('hidden');
		$('.' + this.name + '_' + this.value).removeClass('hidden');
		$('.' + this.name + '_' + this.value).each(function (index) {
			option = $(this).find('.otpApplicable');
			OC.msg.startSaving('#' + option.attr('name') + 'Msg');
			OC.AppConfig.setValue('user_otp', option.attr('name'), option.val())

			// show saved message
			OC.msg.finishedSaving('#' + option.attr('name') + 'Msg', {status: 'success', data: {message: t('settings', 'Saved')}});
		});

		// show saved message
		OC.msg.finishedSaving('#' + this.name + 'Msg', {status: 'success', data: {message: t('settings', 'Saved')}});
	});
});
