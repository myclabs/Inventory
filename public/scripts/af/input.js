/**
 * @namespace AF
 */
var AF = AF || {};

/**
 * Saisie d'un AF
 *
 * @author matthieu.napoli
 *
 * @param {int} id ID de l'AF
 * @param {string} ref Ref de l'AF
 * @param {string} mode read/write/test
 * @param {int|null} idInputSet ID de l'inputSet, ou null si inputSet en session
 * @param {string} exitURL URL où envoyer l'utilisateur lors du clic sur le bouton "Exit"
 * @param {object} urlParams Paramètres d'URL additionnels à utiliser
 * @param {string} resultsPreviewUrl
 * @constructor
 */
AF.Input = function (id, ref, mode, idInputSet, exitURL, urlParams, resultsPreviewUrl) {
    var that = this;

    /**
     * id de l'AF
     * @type {int}
     */
    this.id = id;

    /**
     * ref de l'AF
     * @type {string}
     */
    this.ref = ref;

    /**
     * Formulaire (objet jquery)
     */
    this.form = $("#" + ref);

    /**
     * Mode de la saisie
     * @type {string} read/write/test
     */
    this.mode = mode;

    /**
     * ID de l'inputSet, ou null si inputSet en session
     * @type {int|null}
     */
    this.idInputSet = idInputSet;

    /**
     * Exit URL
     * @type {string}
     */
    this.exitURL = exitURL;

    /**
     * Results preview submit URL
     * @type {string}
     */
    this.resultsPreviewUrl = resultsPreviewUrl;

    /**
     * Est-ce que la saisie a été modifiée
     * @type {boolean}
     */
    this.hasChanges = false;

    /**
     * Sauvegarde l'URL par défaut de soumission du formulaire
     * @type {string}
     * @private
     */
    this.defaultFormAction = this.form.prop("action");

    /**
     * @type {function(data)}
     * @private
     */
    this.inputSavedHandler = this.onSaveHandler;

    /**
     * Complétion de la saisie
     * @type {AF.InputProgress}
     */
    this.inputProgress = new AF.InputProgress();

    /**
     * Paramètres d'URL additionnels à utiliser
     * @type {object}
     */
    this.urlParams = urlParams;

    // Initialisation lorsque toute la page est chargée
    $(function () {
        // Se branche sur les boutons du formulaire
        $(".inputSave").click(function () {
            that.save();
        });
        $(".inputFinish").click(function () {
            that.finishInput();
        });
        $(".inputPreview").click(function () {
            that.previewResults();
        });
        $(".inputExit").click(function () {
            that.exit();
        });
        // Quand le formulaire est modifié
        that.onChange(function () {
            that.hasChanges = true;
            that.inputProgress.setStatus('in_progress');
        });
    });

    // Handler appelé quand la saisie a été sauvegardée avec succès
    $.fn.inputSavedHandler = function (data, textStatus, jqXHR) {
        that.inputSavedHandler(data, textStatus, jqXHR);
    };

    // Handler pour l'historique d'une valeur
    var popoverDefaultContent = '<p class="text-center"><img src="images/ui/ajax-loader.gif"></p>';
    $(".input-history").popover({
        placement: 'bottom',
        title: __('UI', 'history', 'valueHistory'),
        html: true,
        content: popoverDefaultContent
    })
    .on('show.bs.popover', function () {
        // Failsafe parce qu'on ré-appelle "show" quand AJAX a finit de charger
        if ($(this).hasClass('active')) {
            return;
        }
        that.loadInputHistory($(this).data('input-id'), $(this));
    })
    .on('hidden.bs.popover', function () {
        // Rétablit le contenu par défaut
        $(this).data('bs.popover').options.content = popoverDefaultContent;
    })
};

AF.Input.prototype = {

    /**
     * Quitte le formulaire
     */
    exit: function () {
        var that = this;
        if (this.hasChanges) {
            $.confirm({
                text: __("AF", "inputInput", "confirmExitInput"),
                confirmButton: __("UI", "verb", "confirm"),
                cancelButton: __("UI", "verb", "cancel"),
                confirm: function() {
                    window.location.href = that.exitURL;
                },
                cancel: function() {
                    // nothing to do
                }
            });
        } else {
            window.location.href = that.exitURL;
        }
    },

    /**
     * Sauvegarde le formulaire
     */
    save: function () {
        this.form.find(".inputSave").button("loading");
        this.form.submit();
    },

    /**
     * Handler appelée pour la réponse de sauvegarde du formulaire
     * @param {object} response JSON
     * @param {string} textStatus
     * @param {XMLHttpRequest} jqXHR
     * @private
     */
    onSaveHandler: function (response, textStatus, jqXHR) {
        var that = this;
        this.hasChanges = false;

        // Ajoute l'idInputSet aux l'URL
        if ("idInputSet" in response.data) {
            this.idInputSet = response.data.idInputSet;
            this.inputProgress.idInputSet = this.idInputSet;
            // URL de soumission du formulaire
            var currentUrl = this.form.attr("action");
            if ((currentUrl.indexOf("/idInputSet/") === -1) && (currentUrl.indexOf("?idInputSet=") === -1)) {
                this.form.attr("action", currentUrl + "?idInputSet=" + this.idInputSet);
            }
            // URL d'aperçu des résultats
            currentUrl = $("[href='#tabs_tabResult']").attr("data-src");
            if ((typeof currentUrl !== "undefined")
                && (currentUrl.indexOf("/idInputSet/") === -1)
                && (currentUrl.indexOf("?idInputSet=") === -1)
            ) {
                $("[href='#tabs_tabResult']").attr("data-src", currentUrl + "?idInputSet=" + this.idInputSet);
            }
            // URL du détails des calculs
            currentUrl = $("[href='#tabs_tabCalculationDetails']").attr("data-src");
            if ((typeof currentUrl !== "undefined")
                && (currentUrl.indexOf("/idInputSet/") === -1)
                && (currentUrl.indexOf("?idInputSet=") === -1)
            ) {
                $("[href='#tabs_tabCalculationDetails']").attr("data-src", currentUrl + "?idInputSet=" + this.idInputSet);
            }
        }

        // Réinitialise l'aspect des boutons
        this.form.find(".inputExit").removeClass("btn-danger");
        this.form.find(".inputSave").button("reset");
        // @see https://github.com/twitter/bootstrap/issues/6242
        setTimeout(function () {
            that.form.find(".inputSave").prop("disabled", true);
        }, 0);

        // Cache l'aperçu des résultats
        $(".resultsPreview").addClass('hide');

        // Met à jour la complétion de la saisie
        this.inputProgress.setStatus(response.data.status, response.data.completion);

        // Affiche les messages d'erreur
        if ("errorMessages" in response) {
            this.form.parseFormErrors(jqXHR);
        }
        addMessage(response.message, response.type);
    },

    /**
     * Affiche l'aperçu des résultats
     */
    previewResults: function () {
        this.form.find(".inputPreview").button("loading");
        // Définit une nouvelle URL pour le submit
        var url;
        if (this.resultsPreviewUrl) {
            url = this.resultsPreviewUrl + "/id/" + this.id;
        } else {
            url = "af/input/results-preview/id/" + this.id;
        }
        for (var key in this.urlParams) {
            if (this.urlParams.hasOwnProperty(key)) {
                url += '/' + key + '/' + this.urlParams[key];
            }
        }
        this.form.prop("action", url);
        // Définit le handler de retour à utiliser
        this.inputSavedHandler = this.onResultsPreviewHandler;
        // Soumet le formulaire
        this.form.submit();
    },

    /**
     * Handler appelée pour la réponse d'affichage d'aperçu des résultats
     * @param {object} response JSON
     * @param {string} textStatus
     * @param {XMLHttpRequest} jqXHR
     * @private
     */
    onResultsPreviewHandler: function (response, textStatus, jqXHR) {
        this.form.find(".inputPreview").button("reset");
        $(".resultsPreviewContent").html(response.data);
        $(".resultsPreview").removeClass('hide');
        // Restaure l'URL de submit par défaut
        this.form.prop("action", this.defaultFormAction);
        // Restaure le handler par défaut
        this.inputSavedHandler = this.onSaveHandler;
        // Affiche les messages d'erreur
        if ("errorMessages" in response) {
            this.form.parseFormErrors(jqXHR);
        }
    },

    /**
     * Ajoute un handler à l'évènement "change" de la saisie
     * Ne supprime pas les handlers précédents
     * @param {Function} handler Callback
     */
    onChange: function (handler) {
        // Pour tous les input du formulaire (utilise "on()" pour des raisons de performances)
        this.form.on("change keyup", ":input", handler);
        this.form.on("click", ".addRow", handler);
        this.form.find('.repeatedGroup').on("click", ".deleteRow", handler);
    },

    /**
     * Marque la saisie comme terminée
     */
    finishInput: function () {
        var that = this;
        var data = {
            id: this.id
        };
        if (this.idInputSet) {
            data.idInputSet = this.idInputSet;
        }

        for (var key in this.urlParams) {
            data[key] = this.urlParams[key];
        }

        $.ajax("af/input/mark-input-as-finished", {
            data: data,
            success: function (data) {
                addMessage(data.message, 'success');
                that.inputProgress.setStatus(data.status);
            }
        });
    },

    /**
     * Charge l'historique des valeurs d'une saisie
     * @param inputId {int}
     * @param button
     */
    loadInputHistory: function (inputId, button) {
        var url = "af/input/input-history/id/" + this.id + "/idInputSet/" + this.idInputSet + "/idInput/" + inputId;
        for (var key in this.urlParams) {
            if (this.urlParams.hasOwnProperty(key)) {
                url += '/' + key + '/' + this.urlParams[key];
            }
        }

        $.get(url, function (html) {
            button.data('bs.popover').options.content = html;
            button.popover('show');
        });
    }

};
