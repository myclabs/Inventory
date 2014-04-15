$.fn.eraseFormErrors = function (o)
{
	$('#' + $(this).attr('id') + ' .help-block.errorMessage').remove();
	$('#' + $(this).attr('id') + ' .error').removeClass('error');
	$('#' + $(this).attr('id') + ' .warning').removeClass('warning');
};

$.fn.parseFormErrors = function (request)
{
    var response = $.parseJSON(request.responseText);
    if (typeof(response.errorMessages) !== 'undefined') {
        for (var x in response.errorMessages) {
            var xElementLine = $('#' + $(this).attr('id') + ' #' + x + '-line');
            xElementLine.append('<span class="help-block errorMessage">' + response.errorMessages[x] + '</span>');
            if (!(xElementLine.parent().hasClass('error'))) {
                xElementLine.parent().addClass('error');
            }
        }
    } else {
        errorHandler(request);
    }
};

$.fn.parseFormValidation = function (response)
{
    addMessage(response.message, response.type);
};

$.fn.parseFormData = function ()
{
	var data = this.attr('id') + '={';

	var children = $(this).children('fieldset').children();
	for(var key = 0; key < children.length; key++) {
		var child = $(children[key]);
		if (child.hasClass('form-group')) {
			data += '"' + child.attr('id').replace('-line', '') + '": ' + child.parseFormLine() + ', ';
		} else if (child.hasClass('subGroup')) {
			data += '"' + child.attr('id') + '": ' + child.parseFormGroup() + ', ';
		}
	}
    if (data.substring(data.length, data.length-1) !== '{') {
        data = data.slice(0, -2);
    }
	data += '}';

	return data;
};

$.fn.parseFormGroup = function ()
{
	var group = '{';
	var elements = '{';

	group += '"name": "' + $(this).attr('id') + '", ';
	group += '"hidden": ' + ($(this).hasClass('hide') ? 'true' : 'false') + ', ';
	group += '"folded": ' + (($(this).children('div').hasClass('collapse') && !$(this).children('div.collapse').hasClass('in')) ? 'true' : 'false') + ', ';
	if ($(this).hasClass('repeatedGroup')) {
        var rows = $(this).children('div').children('table').children('tbody').children('tr:not(:first)');
        for(var key = 0; key < rows.length; key++) {
            var row = $(rows[key]);
            elements += '"' + row.attr('id') + '": ' + row.parseFormGroupRepeatedRow() + ', ';
        }
    } else {
        var childElements = $(this).children('div').children();
        for(var key = 0; key < childElements.length; key++) {
            var child = $(childElements[key]);
            if (child.hasClass('form-group')) {
                elements += '"' + child.attr('id').replace('-line', '') + '": ' + child.parseFormLine() + ', ';
            } else if (child.hasClass('subGroup')) {
                elements += '"' + child.attr('id') + '": ' + child.parseFormGroup() + ', ';
            }
        }
    }

	if (elements !== '{') {
		elements = elements.slice(0, -2);
	}
	elements += '}';
	group += '"elements": ' + elements;
	group += '}';

	return group;
};

$.fn.parseFormGroupRepeatedRow = function ()
{
    var row = '{';

    var elements = $(this).children('td:not(:last)');
    for(var key = 0; key < elements.length; key++) {
        var element = $(elements[key]);
        row += '"' + element.attr('id').replace('-line', '') + '": ' + element.parseFormLine() + ', '
    }

    if (row !== '{') {
        row = row.slice(0, -2);
    }
    row += '}';

    return row;
};

$.fn.parseFormLine = function ()
{
	var element = '{';
	var children = '{';

	element += '"name": "' + $(this).attr('id').replace('-line', '') + '", ';
	element += '"hidden": ' + ($(this).hasClass('hide') ? 'true' : 'false') + ', ';
	element += $(this).children('div:first').getFormElementValue() + ', ';

	var childElements = $(this).children('div:not(:first)');
	for(var key = 0; key < childElements.length; key++) {
		children += '"' + $(childElements[key]).attr('id') + '": {' + $(childElements[key]).getFormElementValue() + '}, ';
	}
	if (children !== '{') {
		children = children.slice(0, -2);
	}

	children += '}';
	element += '"children": ' + children;
	element += '}';

	return element;
};

$.fn.getFormElementValue = function ()
{
	var element = '"value": ""';
	var hidden = new Array();

	var children = $('> input, > select, > textarea, > div > label > input[type="radio"], > div > label > input[type="checkbox"], > div > div.markItUp > div.markItUpContainer > textarea', $(this));
	for (var key = 0; key < children.length; key ++) {
		var child = $(children[key]);
		switch (child.attr('type'))
		{
            case 'radio':
                if (child.is(':checked')) {
                    element = element.slice(0, -2) + '"' + child.encodeVal() + '"';
                }
                break;
            case 'checkbox':
                if ($(this).children('input[type=checkbox]').length == 1) {
                    element = element.slice(0, -2);
                    if (child.attr('checked') == 'checked') {
                        element += '1';
                    } else {
                        element += '0';
                    }
                } else {
                    if (element == '"value": ""') {
                        element = element.slice(0, -2) + '[]';
                    }
                    if (child.is(':checked')) {
                        element = element.slice(0, -1);
                        if (element != '"value": [') {
                            element += ', ';
                        }
                        element += '"' + child.encodeVal() + '"]';
                    }
                }
                break;
            case 'hidden':
                hidden.push('"' + child.attr('id') + '": "' + child.encodeVal() + '"');
                break;
            default:
                element = element.slice(0, -2);
                if (typeof child.val() === 'object') {
                    element += '[';
                    if (child.val() != null) {
                        element += '"';
                        element += child.val().join('", "');
                        element += '"';
                    }
                    element += '], ';
                } else if (child.val() != null) {
                    element += '"' + child.encodeVal() + '", ';
                } else {
                    element += 'null, ';
                }
                element += '"disabled": ' + ((child.attr('disabled') == 'disabled') ? 'true' : 'false');
		}
	}

	element += ', "hiddenValues": {'
	for (var key = 0; key < hidden.length; key++) {
		element += hidden[key] + ', ';
	}
	if (hidden.length > 0) {
		element = element.slice(0, -2);
	}
	element += '}';

	return element;
};

$.fn.encodeVal = function()
{
    if ((typeof $(this).val() === 'string') && !($(this).hasClass('select2-offscreen'))) {
        return encodeURIComponent($(this).val().replace(/\\/g, '\\\\').replace(/\"/g, '\\"').replace(/\n/g, '\\n'));
    } else {
        return $(this).val();
    }
}
