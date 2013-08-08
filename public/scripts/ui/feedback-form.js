/**
 * feedback-form.js
 */
(function($) {

	/**
	 * Generates the feedback form
	 * @param {string} url Submission URL for the form
	 * @param {object} data Additional data
	 */
	$.fn.feedbackForm = function(url, data) {
		var that = this;
		this.additionalData = data;
		this.ajaxSuccesses = [];
		this.ajaxErrors = [];

		var form = $(
		'<form class="feedback-form"> \
			<a class="feedback-form-text" href="#"> \
				' + __('UI', 'feedback', 'title') + ' \
			</a> \
			<div class="alert alert-success hide"> \
				<button type="button" class="close" data-dismiss="alert">&times;</button> \
				' + __('UI', 'feedback', 'thanks') + ' \
			</div> \
			<div class="feedback-form-content hide"> \
				<label class="checkbox"> \
					<input type="checkbox" name="not-clear"> ' + __('UI', 'feedback', 'unclearContent') + ' \
				</label> \
				<label class="checkbox"> \
					<input type="checkbox" name="bug"> ' + __('UI', 'feedback', 'bugReport') + ' \
				</label> \
				<label class="checkbox"> \
					<input type="checkbox" name="improvement"> ' + __('UI', 'feedback', 'improvement') + ' \
				</label> \
				<p> \
					' + __('UI', 'feedback', 'moreDetails') + ' \
				</p> \
				<p> \
					<textarea name="details" rows="2" style="width: 100%"></textarea> \
				</p> \
				<input type="submit" class="btn btn-primary" value="' + __('UI', 'verb', 'send') + '"> \
				<input type="button" class="cancel btn" value="' + __('UI', 'verb', 'cancel') + '"> \
			</div> \
		</form>');

		form.find(".feedback-form-text").click(function(e) {
			e.preventDefault();
			form.find(".feedback-form-text").hide();
			form.find(".feedback-form-content").fadeIn();
		});

		// Cancel
		form.find(".cancel").click(function(e) {
			form.find(".feedback-form-text").fadeIn();
			form.find(".feedback-form-content").hide();
		});

		// On submit
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
			// Reset
			form[0].reset();
			form.find(".feedback-form-text").fadeIn();
			form.find(".alert-success").fadeIn();
			form.find(".feedback-form-content").hide();
			setTimeout(function() {
				form.find(".alert-success").fadeOut();
			}, 2000);
		});

		// Intercept ajax requests
		$(document).ajaxSuccess(function(e, xhr, settings) {
			that.ajaxSuccesses.push(settings.url);
		});
		$(document).ajaxError(function(e, xhr, settings) {
			that.ajaxErrors.push(settings.url);
		});

		this.append(form);

		return this;
	};

})(jQuery);
