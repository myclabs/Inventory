/**
 * @namespace
 */
var AF = AF || {};

/**
 * Progression de la saisie d'un AF
 *
 * @author matthieu.napoli
 *
 * @param {int} id ID de l'AF
 * @param {int|null} idInputSet ID de l'inputSet, ou null si inputSet en session
 * @param {object} urlParams
 * @constructor
 */
AF.InputProgress = function (id, idInputSet, urlParams) {
    var that = this;

    /**
     * id de l'AF
     * @type {int}
     */
    this.id = id;

    /**
     * ID de l'inputSet, ou null si inputSet en session
     * @type {int|null}
     */
    this.idInputSet = idInputSet;

    /**
     * Paramètres d'URL additionnels à utiliser
     * @type {object}
     */
    this.urlParams = urlParams;

    $(function () {
        // Marquer la saisie comme terminée
        $("#markInputAsFinished").change(function () {
            that.markInputAsFinished();
        });
    });
};

AF.InputProgress.prototype = {

    /**
     * Change le pourcentage de complétion
     * @param {int} completion Pourcentage
     */
    setCompletion: function (completion) {
        $(".inputProgress .progress .bar").width(completion + "%")
            .text(completion + "%");
    },

    /**
     * Change le statut
     * @param {AF.InputProgress.Status} status
     */
    setStatus: function (status) {

        $(".inputProgress .completionIcon").prop("src", status.icon);
        $(".inputProgress .completionMessage").text(status.getMessage());

        // Cas particulier : saisie en cours = progressbar grisée
        if (status == AF.InputProgress.Status.IN_PROGRESS) {
            $(".inputProgress .progress").addClass("progress-striped");
        } else {
            $(".inputProgress .progress").removeClass("progress-striped");
        }

        // Marquer la saisie comme terminée
        var markInputAsFinished = $("#markInputAsFinished");
        markInputAsFinished.attr("checked", (status == AF.InputProgress.Status.FINISHED));
        markInputAsFinished.attr("disabled", (status != AF.InputProgress.Status.COMPLETE) && (status != AF.InputProgress.Status.FINISHED));
        markInputAsFinished.parent("label").tooltip("destroy");
        if (status == AF.InputProgress.Status.IN_PROGRESS) {
            markInputAsFinished.parent("label").tooltip({
                title: __('AF', 'inputInput', 'markInProgressInputAsFinishedTooltip')
            });
        } else if (status == AF.InputProgress.Status.INPUT_INCOMPLETE) {
            markInputAsFinished.parent("label").tooltip({
                title: __('AF', 'inputInput', 'markIncompleteInputAsFinishedTooltip')
            });
        }
    },

    /**
     * Marque la saisie comme terminée
     */
    markInputAsFinished: function () {
        var that = this;
        var data = {
            id: that.id,
            value: $("#markInputAsFinished").is(':checked') ? 1 : 0
        };
        if (that.idInputSet) {
            data.idInputSet = that.idInputSet;
        }

        for (var key in this.urlParams) {
            data[key] = this.urlParams[key];
        }

        $.ajax("af/input/mark-input-as-finished", {
            data: data,
            success: function (data) {
                addMessage(data.message, 'success');
                // Met à jour la complétion de la saisie
                var status = AF.InputProgress.getStatusByRef(data.status);
                that.setStatus(status);
                that.setCompletion(data.completion);
            }
        });
    }

};

/**
 * Statut de la saisie
 * @enum
 */
AF.InputProgress.Status = {
    IN_PROGRESS: {
        ref: "in_progress",
        icon: "images/af/bullet_black.png",
        getMessage: function () {
            return __('AF', 'inputInput', 'statusInProgress');
        }
    },
    INPUT_INCOMPLETE: {
        ref: "input_incomplete",
        icon: "images/af/bullet_red.png",
        getMessage: function () {
            return __('AF', 'inputInput', 'statusInputIncomplete');
        }
    },
    CALCULATION_INCOMPLETE: {
        ref: "calculation_incomplete",
        icon: "images/af/bullet_orange.png",
        getMessage: function () {
            return __('AF', 'inputInput', 'statusCalculationIncomplete');
        }
    },
    COMPLETE: {
        ref: "complete",
        icon: "images/af/bullet_yellow.png",
        getMessage: function () {
            return __('AF', 'inputInput', 'statusComplete');
        }
    },
    FINISHED: {
        ref: "finished",
        icon: "images/af/bullet_green.png",
        getMessage: function () {
            return __('AF', 'inputInput', 'statusFinished');
        }
    }
};

/**
 * Retourne le statut qui correspond au ref donné
 * @param {string} ref
 * @return {AF.InputProgress.Status}
 */
AF.InputProgress.getStatusByRef = function (ref) {
    for (var key in AF.InputProgress.Status) {
        var status = AF.InputProgress.Status[key];
        if (status.ref == ref) {
            return status;
        }
    }
    console.log("Statut " + ref + " non trouvé");
    return null;
};
