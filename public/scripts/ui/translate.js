/**
 * @namespace
 */
var Translate = Translate || {};

/**
 * Liste des traductions
 * @type {Array}
 */
Translate.translations = [];

/**
 * Raccourci pour Translate.translate()
 * @param module {string}
 * @param file {string}
 * @param ref {string}
 * @param [replacements] {Array} Optionnel
 * @return string
 */
function __(module, file, ref, replacements) {
    return Translate.translate(module, file, ref, replacements);
}

/**
 * Traduit un message
 * @param module {string}
 * @param file {string}
 * @param ref {string}
 * @param [replacements] {Array} Optionnel
 * @return string
 */
Translate.translate = function (module, file, ref, replacements) {
    if ((typeof(Translate.translations[module]) === 'undefined')
        || (typeof(Translate.translations[module][file]) === 'undefined')
        || (typeof(Translate.translations[module][file][ref]) === 'undefined')) {
        return ref;
    }
    var message = Translate.translations[module][file][ref];

    for (var search in replacements) {
        var replacement = replacements[search];
        message.replace('[' + search + ']', replacement);
    }
    return message
};

/**
 * Enregistre une traduction
 * @param module {string}
 * @param file {string}
 * @param ref {string}
 * @param message {string}
 */
Translate.addTranslation = function (module, file, ref, message) {
    if (typeof(Translate.translations[module]) === 'undefined') {
        Translate.translations[module] = [];
    }
    if (typeof(Translate.translations[module][file]) === 'undefined') {
        Translate.translations[module][file] = [];
    }
    Translate.translations[module][file][ref] = message;
};
