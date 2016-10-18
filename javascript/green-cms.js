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
				e.preventDefault();
				$.ajax({
					url: this.attr('href'),
					dataType: 'JSON',
					success: function(json) {
						if(json.result) {
							var target = $('[name=TemplateData]');
							target.val(json.result);
							if(target.hasClass('codeeditor')) {								
								var divID = target.attr('id') + '_Ace';
								var editor = ace.edit(divID);
								editor.getSession().setValue(json.result);
							}
						}

					}					
				})
			}
		})
	});
})(jQuery);