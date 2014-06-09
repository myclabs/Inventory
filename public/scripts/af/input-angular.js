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

afModule.filter('debug', function () {
    return function (input) {
        return JSON.stringify(input, undefined, 2);
    };
});

afModule.filter('rawHtml', function ($sce) {
    return function (text) {
        return $sce.trustAsHtml(text);
    };
});

afModule.factory('testCondition', function () {
    return function (condition, inputs) {
        // Find target component input
        var input = inputs.filter(function (input) {
            return input.componentRef === condition.targetComponent;
        })[0];

        if (angular.isUndefined(input)) {
            return false;
        }

        switch(condition.type) {
            case 'equal':
                return input.value === condition.value;
            case 'nequal':
                return input.value !== condition.value;
            default:
                console.log('Unrecognized condition type');
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

afModule.factory('isInputEnabled', ['testCondition', function (testCondition) {
    return function (input, component, inputs) {
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
afModule.factory('validateInputSet', ['getInput', function (getInput) {
    return function validateInputSet(inputSet, components) {
        angular.forEach(components, function (component) {
            var input = getInput(inputSet, component);
            var isEmpty = function (variable) {
                return angular.isUndefined(variable) || variable === null || variable === ''
                    || (angular.isArray(variable) && variable.length == 0);
            };

            switch (component.type) {
                case 'numeric':
                    var value = '';
                    var uncertainty = '';
                    if (angular.isDefined(input.value) && !isEmpty(input.value.digitalValue)) {
                        value = input.value.digitalValue;
                    }
                    if (angular.isDefined(input.value) && !isEmpty(input.value.uncertainty)) {
                        uncertainty = input.value.uncertainty;
                    }
                    if (component.required && (value === '')) {
                        input.hasErrors = true;
                        input.error = __('AF', 'inputInput', 'emptyRequiredField');
                    } else if (! /^-?[0-9]*[.,]?[0-9]*$/.test(value)) {
                        input.hasErrors = true;
                        input.error = __('UI', 'formValidation', 'invalidNumber');
                    } else if (! /^[0-9]*[.,]?[0-9]*$/.test(uncertainty)) {
                        input.hasErrors = true;
                        input.error = __('UI', 'formValidation', 'invalidUncertainty');
                    } else {
                        input.hasErrors = false;
                        input.error = null;
                    }
                    break;
                case 'select':
                case 'radio':
                case 'select-multiple':
                case 'text':
                case 'textarea':
                    if (component.required && isEmpty(input.value)) {
                        input.hasErrors = true;
                        input.error = __('AF', 'inputInput', 'emptyRequiredField');
                    } else if (input.value === 'null') {
                        input.hasErrors = true;
                        input.error = __('AF', 'inputInput', 'emptyRequiredField');
                    } else {
                        input.hasErrors = false;
                        input.error = null;
                    }
                    break;
                case 'subaf-single':
                    validateInputSet(input.value, component.calledAF.components);
                    break;
                case 'subaf-multi':
                    angular.forEach(input.value, function (subInputSet) {
                        validateInputSet(subInputSet, component.calledAF.components);
                    });
                    break;
            }
        });
    };
}]);

afModule.controller('InputController', ['$scope', '$window', '$http', 'validateInputSet',
function ($scope, $window, $http, validateInputSet) {
    $scope.af = $window.af;
    $scope.inputSet = $window.inputSet;
    var urlParams = $window.afUrlParams;

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

    // Preview results
    $scope.preview = function () {
        // Validate input
        validateInputSet($scope.inputSet, $scope.af.components);

        $scope.previewIsLoading = true;
        $scope.resultsPreview = null;
        var data = {
            input: $scope.inputSet,
            urlParams: urlParams
        };
        $http.post('af/input/results-preview?id=' + af.id, data).success(function (response) {
            $scope.resultsPreview = response.data;
            $scope.previewIsLoading = false;
        }).error(function () {
            $scope.previewIsLoading = false;
            addMessage(__('Core', 'exception', 'applicationError'), 'error');
        });
    };

    // Save input
    $scope.save = function () {
        // Validate input
        validateInputSet($scope.inputSet, $scope.af.components);

        $scope.resultsPreview = null;
        $scope.saving = true;
        var data = {
            input: $scope.inputSet,
            urlParams: urlParams
        };
        $http.post('af/input/submit?id=' + af.id, data).success(function (response) {
            $scope.saving = false;
            $scope.inputSet.completion = response.data.completion;
            $scope.inputSet.status = response.data.status;
            addMessage(response.message, response.type);
        }).error(function () {
            $scope.saving = false;
            addMessage(__('Core', 'exception', 'applicationError'), 'error');
        });
    };

    // Mark input as finished
    $scope.finish = function () {
        $scope.resultsPreview = null;
        $scope.markingInputAsFinished = true;
        var data = {
            input: $scope.inputSet,
            urlParams: urlParams
        };
        $http.post('af/input/finish?id=' + af.id, data).success(function (response) {
            $scope.markingInputAsFinished = false;
            $scope.inputSet.status = response.status;
            addMessage(response.message, 'success');
        }).error(function () {
            $scope.markingInputAsFinished = false;
            addMessage(__('Core', 'exception', 'applicationError'), 'error');
        });
    };
}]);

/**
 * Directive pour utiliser le "btn-loading" de Bootstrap
 */
afModule.directive("btnLoading", function() {
    return function(scope, element, attrs){
        scope.$watch(
            function () {
                return scope.$eval(attrs.btnLoading);
            },
            function (loading){
                if (loading) {
                    element.button("loading");
                    return;
                }
                element.button("reset");
            }
        );
    }
});
