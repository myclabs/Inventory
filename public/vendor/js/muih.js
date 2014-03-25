$(document.body).on('show.bs.collapse', '.collapse-wrapper', function(e) {
    var title = $('legend', $(this)).first();
    var titleLink = $('legend > a', $(this)).first();
    titleLink.html(titleLink.html().replace(title.attr('data-closed-indicator').replace(/&quot;/g, '"').replace(/&amp;/g, '&'), title.attr('data-opened-indicator').replace(/&quot;/g, '"').replace(/&amp;/g, '&')));
});
$(document.body).on('hidden.bs.collapse', '.collapse-wrapper', function(e) {
    var title = $('legend', $(this)).first();
    var titleLink = $('legend > a', $(this)).first();
    titleLink.html(titleLink.html().replace(title.attr('data-opened-indicator').replace(/&quot;/g, '"').replace(/&amp;/g, '&'), title.attr('data-closed-indicator').replace(/&quot;/g, '"').replace(/&amp;/g, '&')));
});

$(document.body).on('show.bs.tab', '[data-toggle="tab"]', function(e) {
    var targetTabLink = $(e.target).first();
    var targetTab = $(targetTabLink.attr('href')).first();
    if ((typeof targetTabLink.attr('data-src') !== 'undefined') && (targetTabLink.attr('data-src') !== false) && ((targetTab.attr('data-cache') !== 'true') || (typeof targetTab.attr('data-loading') === 'undefined') || (targetTab.attr('data-loading') === false))) {
        console.log(targetTab);
        console.log(targetTabLink.attr('data-src'));
        targetTab.trigger('loadTab.muih', [targetTabLink.attr('data-src')]);
    }
    var previousTab = $($(e.relatedTarget).attr('href')).first();
    if ((previousTab.attr('data-cache') === 'false') && (typeof previousTab.attr('data-loading') !== 'undefined') && (previousTab.attr('data-loading') !== false)) {
        previousTab.html(previousTab.attr('data-loading'));
    }
});

$(document.body).on('loadTab.muih', '.tab-pane', function(e, src) {
    var targetTab = $(this);
    targetTab.attr('data-loading', targetTab.html());
    targetTab.load(src, function(responseText, textStatus, XMLHttpRequest) {
        $('.withTooltip', targetTab).tooltip();
        $('.withPopover', targetTab).popover();
    });
});