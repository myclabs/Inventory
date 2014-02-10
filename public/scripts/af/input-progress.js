/**
 * @namespace
 */
var AF = AF || {};

/**
 * Progression de la saisie d'un AF
 *
 * @author matthieu.napoli
 *
 * @constructor
 */
AF.InputProgress = function () {
};

AF.InputProgress.prototype = {

    /**
     * Saisie incomplète
     * @param {int} completion Pourcentage d'avancement
     */
    inputIncomplete: function (completion) {
        $('.inputProgress .progress .bar')
            .width(completion + '%')
            .text(completion + '%');
        $('.inputProgress .completionIcon').prop('src', 'images/af/bullet_red.png');
        $('.inputProgress .completionMessage').text(__('AF', 'inputInput', 'statusInputIncomplete'));

        $(".inputProgress .progress").removeClass("progress-striped").removeClass("progress-success");

        $(".inputSave").prop("disabled", true).removeClass('btn-primary').show();
        $(".inputValidate").prop("disabled", true).removeClass('btn-primary').show();
        $(".inputReopen").hide();
    },

    /**
     * En cours de saisie / Saisie modifiée
     */
    inputInProgress: function () {
        $('.inputProgress .completionIcon').prop('src', 'images/af/bullet_black.png');
        $('.inputProgress .completionMessage').text(__('AF', 'inputInput', 'statusInProgress'));

        // Progress bar rayée
        $(".inputProgress .progress")
            .removeClass("progress-striped").addClass("progress-striped")
            .removeClass("progress-success");

        $(".inputSave").prop("disabled", false).addClass('btn-primary').show();
        $(".inputValidate").prop("disabled", true).removeClass('btn-primary').show();
        $(".inputReopen").hide();
    },

    /**
     * Saisie complète mais erreur dans les calculs
     */
    inputCompleteCalculationIncomplete: function () {
        $('.inputProgress .progress .bar')
            .width('100%')
            .text('100%');
        $('.inputProgress .completionIcon').prop('src', 'images/af/bullet_orange.png');
        $('.inputProgress .completionMessage').text(__('AF', 'inputInput', 'statusCalculationIncomplete'));

        $(".inputProgress .progress").removeClass("progress-striped").removeClass("progress-success");

        $(".inputSave").prop("disabled", true).removeClass('btn-primary').show();
        $(".inputValidate").prop("disabled", false).addClass('btn-primary').show();
        $(".inputReopen").hide();
    },

    /**
     * Saisie complète
     */
    inputComplete: function () {
        $('.inputProgress .progress .bar')
            .width('100%')
            .text('100%');
        $('.inputProgress .completionIcon').prop('src', 'images/af/bullet_yellow.png');
        $('.inputProgress .completionMessage').text(__('AF', 'inputInput', 'statusComplete'));

        $(".inputProgress .progress").removeClass("progress-striped").removeClass("progress-success");

        $(".inputSave").prop("disabled", true).removeClass('btn-primary').show();
        $(".inputValidate").prop("disabled", false).addClass('btn-primary').show();
        $(".inputReopen").hide();
    },

    /**
     * Saisie terminée
     */
    inputFinished: function () {
        $('.inputProgress .progress .bar')
            .width('100%')
            .text('100%');
        $('.inputProgress .completionIcon').prop('src', 'images/af/bullet_green.png');
        $('.inputProgress .completionMessage').text(__('AF', 'inputInput', 'statusFinished'));

        $(".inputProgress .progress")
            .removeClass("progress-striped")
            .removeClass("progress-success").addClass("progress-success");

        $(".inputSave").prop("disabled", true).removeClass('btn-primary').hide();
        $(".inputValidate").prop("disabled", true).removeClass('btn-primary').hide();
        $(".inputReopen").show();
    },

    /**
     * Change le statut
     * @param {string}   ref
     * @param {int|null} completion
     */
    setStatus: function (ref, completion) {
        switch (ref) {
            case 'input_incomplete':
                this.inputIncomplete(completion);
                break;
            case 'in_progress':
                this.inputInProgress();
                break;
            case 'calculation_incomplete':
                this.inputCompleteCalculationIncomplete();
                break;
            case 'complete':
                this.inputComplete();
                break;
            case 'finished':
                this.inputFinished();
                break;
        }
    }

};
