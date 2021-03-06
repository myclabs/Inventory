var afModule = angular.module('AF', ['ui.bootstrap']);

// Configure Angular pour poster en "form data" plutôt qu'en JSON
afModule.config(function ($httpProvider) {
    $httpProvider.defaults.transformRequest = function(data){
        if (data === undefined) {
            return data;
        }
        return $.param(data);
    };
    $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';
});

afModule.filter('rawHtml', function ($sce) {
    return function (text) {
        return $sce.trustAsHtml(text);
    };
});

/**
 * Evalue une expression de type condition.
 */
afModule.factory('evaluateExpression', function ($rootScope) {
    return function (expression, variables) {
        // Transforme l'expression en une expression javascript valide
        expression = expression.replace('&', '&&');
        expression = expression.replace('|', '||');

        // Utilise $eval d'AngularJS pour évaluer l'expression
        var expressionScope = $rootScope.$new(true);
        angular.forEach(variables, function (value, key) {
            expressionScope[key] = value;
        });
        return expressionScope.$eval(expression);
    };
});

/**
 * Evalue une condition.
 */
afModule.factory('testCondition', function (evaluateExpression) {
    return function testCondition(condition, inputs) {
        if (condition === null) {
            return false;
        }

        // Expression
        if (condition.type === 'expression') {
            var variables = {};
            // Évalue les sous-expression
            angular.forEach(condition.subConditions, function (subCondition) {
                variables[subCondition.ref] = testCondition(subCondition, inputs);
            });
            return evaluateExpression(condition.expression, variables);
        }

        // Find target component input
        var input = inputs.filter(function (input) {
            return input.componentRef === condition.targetComponent;
        })[0];

        if (angular.isUndefined(input)) {
            return false;
        }

        // Cas particulier des saisies numériques
        var value = input.value;
        if (value !== null && value.hasOwnProperty('digitalValue')) {
            value = value.digitalValue;
        }

        if (value === null) {
            return false;
        }

        switch(condition.type) {
            case 'equal':
                return value === condition.value;
            case 'nequal':
                return value !== condition.value;
            case '>=':
                return value >= condition.value;
            case '>':
                return value > condition.value;
            case '<=':
                return value <= condition.value;
            case '<':
                return value < condition.value;
            case 'contains':
                return value.indexOf(condition.value) !== -1;
            case 'ncontains':
                return value.indexOf(condition.value) === -1;
            default:
                return false;
        }
    };
});

afModule.factory('isInputVisible', ['testCondition', function (testCondition) {
    return function (input, component, inputs) {
        if (angular.isUndefined(component.actions)) {
            return component.visible;
        }

        var actions = component.actions.filter(function (action) {
            return action.type === 'show' || action.type === 'hide';
        });

        // No actions on this component
        if (actions.length === 0) {
            return component.visible;
        }

        return actions.reduce(function (result, action) {
            if (action.type === 'show') {
                return result && testCondition(action.condition, inputs);
            } else if (action.type === 'hide') {
                return result && !testCondition(action.condition, inputs);
            } else {
                return result;
            }
        }, true);
    };
}]);

afModule.factory('isInputEnabled', ['$window', 'testCondition', function ($window, testCondition) {
    return function (input, component, inputs) {
        if ($window.readOnly) {
            return false;
        }

        if (angular.isUndefined(component.actions)) {
            return component.enabled;
        }

        var actions = component.actions.filter(function (action) {
            return action.type === 'enable' || action.type === 'disable';
        });

        // No actions on this component
        if (actions.length === 0) {
            return component.enabled;
        }

        return actions.reduce(function (result, action) {
            if (action.type === 'enable') {
                return result && testCondition(action.condition, inputs);
            } else if (action.type === 'disable') {
                return result && !testCondition(action.condition, inputs);
            } else {
                return result;
            }
        }, true);
    };
}]);

/**
 * Retrouve une saisie à partir d'un composant.
 */
afModule.factory('getInput', function () {
    return function (inputSet, component) {
        if (angular.isUndefined(inputSet.inputs)) {
            inputSet.inputs = [];
        }
        var inputs = inputSet.inputs;
        for (i = 0; i < inputs.length; ++i) {
            var input = inputs[i];
            if (input.componentRef === component.ref) {
                return input;
            }
        }
        // Pas de valeur, on en crée une vide
        var newInput = {
            componentRef: component.ref,
            value: null
        };
        inputSet.inputs.push(newInput);
        return newInput;
    };
});

/**
 * Valide une saisie.
 */
afModule.factory('updateInputs', ['getInput', function (getInput) {
    return function updateInputs(inputSet, updatedInputSet, components) {
        var context = {};
        context['inputSet'] = inputSet;
        context['updatedInputSet'] = updatedInputSet;
        angular.forEach(components, function (component) {
            var input = getInput(this.inputSet, component);
            var updatedInput = getInput(this.updatedInputSet, component);
            switch (component.type) {
                case 'numeric':
                case 'select':
                case 'radio':
                case 'select-multiple':
                case 'text':
                case 'textarea':
                    input.hasErrors = updatedInput.hasErrors;
                    input.error = updatedInput.error;
                    input.inconsistent = updatedInput.inconsistent;
                    break;
                case 'group':
                    updateInputs(this.inputSet, this.updatedInputSet, component.components);
                    break;
                case 'subaf-single':
                    updateInputs(input.value, updatedInput.value, component.calledAF.components);
                    break;
                case 'subaf-multi':
                    angular.forEach(input.value, function (subInputSet, index) {
                        updateInputs(subInputSet, this.value[index], component.calledAF.components);
                    }, updatedInput);
                    break;
            }
        }, context);
    };
}]);
afModule.factory('validateInputSet', ['$window', '$http', 'updateInputs',
function ($window, $http, updateInputs) {
    return function validateInputSet(inputSet, components, callback) {
        var urlParams = $window.afUrlParams;
        var inputValidationUrl = $window.inputValidationUrl;
        var data = {
            input: inputSet,
            urlParams: urlParams,
            idInputSet: inputSet.id
        };
        $http.post(inputValidationUrl, data).success(function (response) {
            updateInputs(inputSet, response.input, components);
        }).error(function () {
            addMessage(__('Core', 'exception', 'applicationError'), 'error');
        }).then(callback);
    };
}]);

afModule.controller('InputController', ['$scope', '$element', '$window', '$http', '$timeout', 'validateInputSet',
function ($scope, $element, $window, $http, $timeout, validateInputSet) {
    $scope.readOnly = $window.readOnly;
    $scope.af = $window.af;
    $scope.inputSet = $window.inputSet;
    $scope.refPrefix = '';
    var urlParams = $window.afUrlParams;
    var submitInputUrl = $window.submitInputUrl;
    var finishInputUrl = $window.finishInputUrl;
    var resultsPreviewUrl = $window.resultsPreviewUrl;

    $scope.inputStatuses = {
        in_progress: __('AF', 'inputInput', 'statusInProgress'),
        input_incomplete: __('AF', 'inputInput', 'statusInputIncomplete'),
        calculation_incomplete: __('AF', 'inputInput', 'statusCalculationIncomplete'),
        complete: __('AF', 'inputInput', 'statusComplete'),
        finished: __('AF', 'inputInput', 'statusFinished')
    };
    $scope.statusColors = {
        in_progress: 'black',
        input_incomplete: 'red',
        calculation_incomplete: 'orange',
        complete: 'yellow',
        finished: 'green'
    };

    $scope.previewIsLoading = false;
    $scope.saving = false;
    $scope.markingInputAsFinished = false;

    // Enable/disable form actions
    $scope.isInputInProgress = function () {
        return $scope.inputSet.status === 'in_progress';
    };
    $scope.isInputComplete = function () {
        switch ($scope.inputSet.status) {
            default:
            case 'in_progress':
            case 'input_incomplete':
                return false;
            case 'calculation_incomplete':
            case 'complete':
            case 'finished':
                return true;
        }
    };
    $scope.isInputFinished = function () {
        return $scope.inputSet.status === 'finished';
    };

    // When the input is edited
    $timeout(function () {
        $scope.$watch('inputSet.inputs', function (newValue, oldValue) {
            if ( newValue === oldValue ) {
                return;
            }
            $scope.inputSet.status = 'in_progress';
        }, true);
    }, 1000);

    // Preview results
    $scope.preview = function () {
        // Validate input
        validateInputSet($scope.inputSet, $scope.af.components, function () {
            $scope.previewIsLoading = true;
            $scope.resultsPreview = null;
            var data = {
                input: $scope.inputSet,
                urlParams: urlParams,
                idInputSet: $scope.inputSet.id
            };
            $http.post(resultsPreviewUrl, data).success(function (response) {
                $scope.resultsPreview = response.data;
                $scope.previewIsLoading = false;
            }).error(function () {
                $scope.previewIsLoading = false;
                addMessage(__('Core', 'exception', 'applicationError'), 'error');
            });
        });
    };

    // Save input
    $scope.save = function () {
        // Validate input
        validateInputSet($scope.inputSet, $scope.af.components, function () {
            $scope.resultsPreview = null;
            $scope.saving = true;
            var data = {
                input: $scope.inputSet,
                urlParams: urlParams,
                idInputSet: $scope.inputSet.id
            };
            $http.post(submitInputUrl, data).success(function (response) {
                $scope.saving = false;
                $scope.inputSet.completion = response.data.completion;
                $scope.inputSet.status = response.data.status;
                $scope.inputSet.id = response.data.idInputSet;
                addMessage(response.message, response.type);
            }).error(function () {
                $scope.saving = false;
                addMessage(__('Core', 'exception', 'applicationError'), 'error');
            });
        });
    };

    // Mark input as finished
    $scope.finish = function () {
        $scope.resultsPreview = null;
        $scope.markingInputAsFinished = true;
        var data = {
            input: $scope.inputSet,
            urlParams: urlParams,
            idInputSet: $scope.inputSet.id
        };
        $http.post(finishInputUrl, data).success(function (response) {
            $scope.markingInputAsFinished = false;
            $scope.inputSet.status = response.status;
            addMessage(response.message, 'success');
        }).error(function () {
            $scope.markingInputAsFinished = false;
            addMessage(__('Core', 'exception', 'applicationError'), 'error');
        });
    };

    // Exit
    $scope.exit = function () {
        if ($scope.inputSet.status === 'in_progress') {
            $.confirm({
                text: __('AF', 'inputInput', 'confirmExitInput'),
                confirmButton: __('UI', 'verb', 'confirm'),
                cancelButton: __('UI', 'verb', 'cancel'),
                confirm: function() {
                    window.location.href = $window.exitUrl;
                },
                cancel: function() {
                    // nothing to do
                }
            });
        } else {
            window.location.href = $window.exitUrl;
        }
    };
}]);

/**
 * Directive pour utiliser le "btn-loading" de Bootstrap
 */
afModule.directive('btnLoading', function() {
    return function(scope, element, attrs){
        scope.$watch(
            function () {
                return scope.$eval(attrs.btnLoading);
            },
            function (loading){
                if (loading) {
                    element.button('loading');
                    return;
                }
                element.button('reset');
            }
        );
    }
});

/**
 * Directive pour avoir un vrai champ de saisie pour les nombres à virgule.
 */
afModule.directive('inputFloat', function ($window) {
    var FLOAT_REGEXP = /^\-?\d+[\.|\,]?\d*$/;
    var decimalSeparator = $window.decimalSeparator;

    return {
        require: 'ngModel',
        link: function (scope, element, attrs, controller) {
            controller.$parsers.unshift(function (viewValue) {
                if (FLOAT_REGEXP.test(viewValue)) {
                    controller.$setValidity('float', true);
                    if (typeof viewValue === 'number') {
                        return viewValue;
                    } else {
                        return parseFloat(viewValue.replace(decimalSeparator, '.'));
                    }
                } else {
                    controller.$setValidity('float', false);
                    return viewValue;
                }
            });
            controller.$formatters.push(function (modelValue) {
                if (modelValue === null || modelValue === undefined) {
                    return modelValue;
                }
                return modelValue.toString().replace('.', decimalSeparator);
            });
        }
    };
});
