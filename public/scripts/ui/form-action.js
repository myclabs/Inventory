$.fn.formActionSetValue = function (value)
{
	if ($(this).children('label').hasClass('checkbox')) {
        for (var i = 0; i < $(this).children('label').children('input').length; i++) {
            var checkbox = $($(this).children('label').children('input')[i]);
            if ((value == 'true') || (checkbox.val() == value) || (jQuery.inArray(checkbox.val(), value) > 0)) {
                if (checkbox.is(':not(:checked)')) {
                    checkbox.prop('checked', 'checked');
                }
            } else {
                if (checkbox.is(':checked')) {
                    checkbox.removeProp('checked');
                }
            }
        }
	} else if ($(this).children('option').length > 0) {
		$(this).val(value);
	} else if (typeof($(this).attr('value')) != 'undefined') {
		$(this).attr('value', value);
	} else {
		$(this).html(value);
	}
};

$.fn.formActionSetOptions = function (value)
{
	if ($(this).is('select')) {
		var select = $(this);
		$('option', select).remove();
		jQuery.each(value, function(index, option) {
			select.append($('<option></option>').attr('value', index).text(option));
		});
	} else if ($(this).children('label.checkbox').length > 0) {
		$(this).html('');
		var div = $(this);
		jQuery.each(value, function(index, option) {
			var html = '<label class="checkbox" for="' + index + '">';
			html += '<input id="' + index + '" type="checkbox" value="' + index + '" name="' + div.attr('id') + '"/>';
			html += option + '</label>';
			div.append(html);
		});
	}
};

$.fn.formActionShow = function ()
{
	var element = $(this).formActionGetElement();
	if (element.parent().hasClass('input')) {
		element = $(element.parent())
	}
	if (element.hasClass('hide')) {
		element.removeClass('hide');
	}
};

$.fn.formActionHide = function ()
{
	var element = $(this).formActionGetElement();
	if (element.parent().hasClass('input')) {
		element = $(element.parent())
	}
	if (!(element.hasClass('hide'))) {
		element.addClass('hide');
	}
};

$.fn.formActionEnable = function ()
{
	$('textare, input', $(this).parent()).removeAttr('disabled');
};

$.fn.formActionDisable = function ()
{
	$('textare, input', $(this).parent()).attr('disabled', 'disabled');
};

$.fn.formActionGetElement = function ()
{
	if ($('#' + $(this).attr('id') + '-line').length == 1) {
		return $('#' + $(this).attr('id') + '-line');
	} else if ($(this).parent().hasClass('radio') || $(this).parent().hasClass('checkbox')) {
		return $($(this).parent());
	} else {
		return $(this);
	}
};