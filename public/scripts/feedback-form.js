/**
 * Generates the feedback form
 * @param {string} url Submission URL for the form
 * @param {object} data Additional data
 */
feedbackForm = function(url, data) {
    var that = this;
    this.additionalData = data;
    this.ajaxSuccesses = [];
    this.ajaxErrors = [];

    var contentHTML =
        '<div class="checkbox"> \
            <label> \
                <input type="checkbox" name="not-clear"> ' + __('UI', 'feedback', 'unclearContent') + ' \
            </label> \
        </div> \
        <div class="checkbox"> \
            <label> \
                <input type="checkbox" name="bug"> ' + __('UI', 'feedback', 'bugReport') + ' \
            </label> \
        </div> \
        <div class="checkbox"> \
            <label> \
                <input type="checkbox" name="improvement"> ' + __('UI', 'feedback', 'improvement') + ' \
            </label> \
        </div> \
        <div class="form-group"> \
            <label>' + __('UI', 'feedback', 'moreDetails') + '</label> \
            <textarea class="form-control" name="details" rows="5"></textarea> \
        </div>';

    var modalHTML =
        '<div class="modal fade">' +
            '<div class="modal-dialog">' +
                '<div class="modal-content">' +
                    '<form class="feedback-form">' +
                        '<div class=modal-header>' +
                            '<button type="button" class="close" data-dismiss="modal">&times;</button>' +
                            '<h4 class="modal-title">' + __('UI', 'feedback', 'title') + '</h4>' +
                        '</div>' +
                        '<div class="modal-body">' + contentHTML + '</div>' +
                        '<div class="modal-footer">' +
                            '<button class="btn btn-primary" type="submit">' +
                                __('UI', 'verb', 'send') +
                            '</button>' +
                            '<button class="btn btn-default" type="button" data-dismiss="modal">' +
                                __('UI', 'verb', 'cancel') +
                            '</div>' +
                        '</div>' +
                    '</form>' +
                '</div>' +
            '</div>' +
        '</div>';

    var modal = $(modalHTML);
    modal.on('hidden', function () {
        // Destroy modal
        modal.remove();
    });
    // Show the modal
    $("body").append(modal);
    modal.modal('show');

    // On submit
    var form = modal.find('form');
    form.submit(function(e) {
        e.preventDefault();
        var now = new Date();
        // AJAX submission
        var data = form.serialize();
        data += '&url=' + encodeURIComponent(document.URL);
        data += '&date=' + encodeURIComponent(now.toLocaleString());
        data += '&ajaxSuccesses=' + encodeURIComponent(JSON.stringify(that.ajaxSuccesses));
        data += '&ajaxErrors=' + encodeURIComponent(JSON.stringify(that.ajaxErrors));
        for (var key in that.additionalData) {
            if (that.additionalData.hasOwnProperty(key)) {
                data += "&" + key + "=" + that.additionalData[key];
            }
        }
        $.ajax(url, {
            type: 'POST',
            data: data
        });
        addMessage(__('UI', 'feedback', 'thanks'), 'success');
        modal.modal('hide');
    });

    // Intercept ajax requests
    $(document).ajaxSuccess(function(e, xhr, settings) {
        that.ajaxSuccesses.push(settings.url);
    });
    $(document).ajaxError(function(e, xhr, settings) {
        that.ajaxErrors.push(settings.url);
    });
};
