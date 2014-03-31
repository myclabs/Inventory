// Ouverture et fermeture des collapses
$(document.body).on('show.bs.collapse', '.collapse-wrapper', function(e) {
    if (!$(e.target).parent().is($(this))) {
        return;
    }

    var title = $('legend', $(this)).first();
    var titleLink = $('legend > a', $(this)).first();

    titleLink.html(
        titleLink.html().replace(
            title.attr('data-closed-indicator'),
            title.attr('data-opened-indicator')
        )
    );
});
$(document.body).on('hidden.bs.collapse', '.collapse-wrapper', function(e) {
    if (!$(e.target).parent().is($(this))) {
        return;
    }

    var title = $('legend', $(this)).first();
    var titleLink = $('legend > a', $(this)).first();

    titleLink.html(
        titleLink.html().replace(
            title.attr('data-opened-indicator'),
            title.attr('data-closed-indicator')
        )
    );
});

// Tab AJAX
$(document.body).on('show.bs.tab', '[data-toggle="tab"]', function(e) {
    var targetTabLink = $(e.target).first();
    var targetTab = $(targetTabLink.attr('href')).first();

    if ((typeof targetTabLink.attr('data-src') !== 'undefined')
        && (targetTabLink.attr('data-src') !== false)
        && (
            (targetTab.attr('data-cache') !== 'true')
            || (typeof targetTab.attr('data-loading') === 'undefined')
            || (targetTab.attr('data-loading') === false)
        )
    ) {
        targetTab.trigger('loadTab.muih', [targetTabLink.attr('data-src')]);
    }

    var previousTab = $($(e.relatedTarget).attr('href')).first();

    if ((previousTab.attr('data-cache') === 'false')
        && (typeof previousTab.attr('data-loading') !== 'undefined')
        && (previousTab.attr('data-loading') !== false)
    ) {
        previousTab.html(previousTab.attr('data-loading'));
    }
});
$(document.body).on('loadTab.muih', '.tab-pane', function(e, src) {
    var targetTab = $(this);
    targetTab.attr('data-loading', targetTab.html());
    targetTab.load(src, function() {
        $('.withTooltip', targetTab).tooltip();
        $('.withPopover', targetTab).popover();
    });
});

$(document.body).on('click', '[data-toggle="modal"]', function(e) {
    var targetModalLink = $(this);
    if ((typeof targetModalLink.attr('data-target') === 'undefined')
        || (targetModalLink.attr('data-target') === false)
        || (!$(targetModalLink.attr('data-target')).hasClass('modal-ajax'))
    ) {
        return true;
    }
    e.preventDefault();

    var targetModal = $(targetModalLink.attr('data-target'));

    var targetModalContent = $('.modal-content', targetModal).first();
    if ((typeof targetModalContent.attr('data-loading') === 'undefined')
        || (targetModalContent.attr('data-loading') === false)
        ) {
        targetModalContent.attr('data-loading', targetModalContent.html());
    }

    targetModal.modal('show');
    targetModal.trigger('loadModal.muih', [targetModalLink.attr('href')]);
    e.stopPropagation();
});

$(document.body).on('hidden.bs.modal', '.modal', function(e) {
    if ((!$(e.target).is($(this))) || (!$(this).hasClass('modal-ajax'))) {
        return true;
    }

    var targetModalContent = $('.modal-content', $(this)).first();
    if ((typeof targetModalContent.attr('data-loading') !== 'undefined')
        && (targetModalContent.attr('data-loading') !== false)
        ) {
        targetModalContent.html(targetModalContent.attr('data-loading'));
    }
});

$(document.body).on('loadModal.muih', '.modal', function(e, src) {
    if (($(this).attr('data-ajax') !== 'undefined')
        && ($(this).attr('data-ajax') !== false)
        && ($(this).attr('data-ajax') === 'body')
    ) {
        var targetModalContent = $('.modal-body', $(this)).first();
    } else {
        var targetModalContent = $('.modal-content', $(this)).first();
    }

    targetModalContent.load(src, function () {
        $('.withTooltip', targetModalContent).tooltip();
        $('.withPopover', targetModalContent).popover();
    });
});
