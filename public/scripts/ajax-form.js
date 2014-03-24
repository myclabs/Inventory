/**
 * AJAX form submission and validation handling.
 *
 * @param {string} selector CSS selector for the form.
 * @constructor
 */
AjaxForm = function (selector) {
    var self = this;

    /**
     * @type {string}
     */
    this.form = $(selector);

    // Uses the ajaxForm jquery plugin
    this.form.ajaxForm({
        dataType: 'json',
        beforeSubmit: function(formData) {
            self.beforeSubmit(formData);
        },
        success: function(data) {
            self.onSuccess(data);
        },
        error: function(jqXHR) {
            self.onError(jqXHR);
        }
    });

    /**
     * Can be overridden
     */
    this.beforeSubmit = function(formData)
    {
        self.clearErrors();
    };

    /**
     * Can be overridden
     */
    this.onSuccess = function(data)
    {
        addMessage(data.message, data.type);
    };

    /**
     * Can be overridden
     */
    this.onError = function(jqXHR)
    {
        var data = $.parseJSON(jqXHR.responseText);

        if (typeof(data.errorMessages) === 'undefined') {
            errorHandler(jqXHR);
        } else {
            self.addErrors(data);
        }
    };

    this.addErrors = function (data)
    {
        for (var name in data.errorMessages) {
            if (! data.errorMessages.hasOwnProperty(name)) {
                continue;
            }

            var input = self.form.find('[name="' + name + '"]');
            input.parent().append('<span class="help-block errorMessage">'
                + data.errorMessages[name]
                + '</span>');

            var inputLine = input.parents('.form-group');
            if (! inputLine.hasClass('has-error')) {
                inputLine.addClass('has-error');
            }
        }
    };

    this.clearErrors = function()
    {
        self.form.find('.help-block.errorMessage').remove();
        self.form.find('.has-error').removeClass('has-error');
    };
};
