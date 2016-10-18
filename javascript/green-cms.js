(function($) {
	$.entwine('ss', function($) {
		$('select[name=DesignModule]').entwine({
			onchange: function(e) {
				this.closest('body .cms-container').submitForm(
					this.closest('form'),
					this.closest('form').find('[name=action_save]')[0]
				);
			}
		});

		$('a.template-parse-button').entwine({
			onclick: function(e) {
				alert('yaah');
			}
		})
	});
})(jQuery);