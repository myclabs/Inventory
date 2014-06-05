var afModule = angular.module('AF', []);

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

afModule.filter('debug', function() {
    return function(input) {
        return JSON.stringify(input, undefined, 2);
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

afModule.controller('InputController', ['$scope', '$window', '$http', function ($scope, $window, $http) {
    $scope.af = $window.af;
    $scope.inputSet = $window.inputSet;
    var urlParams = $window.afUrlParams;

    $scope.save = function () {
        var data = {
            input: $scope.inputSet,
            urlParams: urlParams
        };
        $http.post('af/input/submit?id=' + af.id, data).success(function (response) {
            if (angular.isUndefined(response)) {
                return;
            }
            $scope.inputSet.completion = response.data.completion;
            $scope.inputSet.status = response.data.status;

            addMessage(response.message, response.type);
        });
    };
}]);

afModule.directive('afFieldset', [ 'isInputVisible', function(isInputVisible) {
    return {
        restrict: 'E',
        templateUrl: 'scripts/af/templates/fieldset.html',
        scope: {
            components: '=',
            label: '=',
            inputSet: '='
        },
        link: function ($scope) {
            $scope.isInputVisible = isInputVisible;

            $scope.getInput = function (component) {
                if (angular.isUndefined($scope.inputSet) || $scope.inputSet === null) {
                    $scope.inputSet = {};
                }
                if (angular.isUndefined($scope.inputSet.inputs)) {
                    $scope.inputSet.inputs = [];
                }
                var inputs = $scope.inputSet.inputs;
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
                $scope.inputSet.inputs.push(newInput);
                return newInput;
            };
        }
    };
}]);

afModule.directive('afHorizontalFieldset', function() {
    return {
        restrict: 'E',
        templateUrl: 'scripts/af/templates/horizontal-fieldset.html',
        scope: {
            components: '=',
            inputSets: '='
        },
        link: function ($scope) {
            $scope.getInput = function (inputSet, component) {
                if (angular.isUndefined(inputSet)) {
                    inputSet = {};
                }
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
        }
    };
});

afModule.directive('afComponent', function() {
    return {
        restrict: 'E',
        scope: {
            component: '=',
            input: '='
        },
        templateUrl: 'scripts/af/templates/component.html',
        link: function ($scope) {
            // Forward les saisies du scope courant
            $scope.inputs = $scope.$parent.inputSet.inputs;
        }
    };
});
afModule.directive('afHorizontalComponent', function() {
    return {
        restrict: 'E',
        scope: {
            component: '=',
            input: '='
        },
        templateUrl: 'scripts/af/templates/horizontal-component.html',
        link: function ($scope) {
            // Forward les saisies du scope courant
            $scope.inputs = $scope.$parent.inputs;
        }
    };
});

afModule.directive('afNumericField', function(isInputEnabled) {
    return {
        restrict: 'E',
        scope: {
            component: '=',
            input: '='
        },
        templateUrl: 'scripts/af/templates/component-numeric-field.html',
        link: function ($scope) {
            // Forward les saisies du scope courant
            $scope.inputs = $scope.$parent.inputs;

            $scope.isInputEnabled = isInputEnabled;
        }
    };
});

afModule.directive('afSelect', function(isInputEnabled) {
    return {
        restrict: 'E',
        scope: {
            component: '=',
            input: '='
        },
        templateUrl: 'scripts/af/templates/component-select.html',
        link: function ($scope) {
            // Forward les saisies du scope courant
            $scope.inputs = $scope.$parent.inputs;

            $scope.isInputEnabled = isInputEnabled;
        }
    };
});

afModule.directive('afCheckbox', function(isInputEnabled) {
    return {
        restrict: 'E',
        scope: {
            component: '=',
            input: '='
        },
        templateUrl: 'scripts/af/templates/component-checkbox.html',
        link: function ($scope) {
            // Forward les saisies du scope courant
            $scope.inputs = $scope.$parent.inputs;

            $scope.isInputEnabled = isInputEnabled;
        }
    };
});

afModule.directive('afTextField', function(isInputEnabled) {
    return {
        restrict: 'E',
        scope: {
            component: '=',
            input: '='
        },
        templateUrl: 'scripts/af/templates/component-text-field.html',
        link: function ($scope) {
            // Forward les saisies du scope courant
            $scope.inputs = $scope.$parent.inputs;

            $scope.isInputEnabled = isInputEnabled;
        }
    };
});

afModule.directive('afTextarea', function(isInputEnabled) {
    return {
        restrict: 'E',
        scope: {
            component: '=',
            input: '='
        },
        templateUrl: 'scripts/af/templates/component-textarea.html',
        link: function ($scope) {
            // Forward les saisies du scope courant
            $scope.inputs = $scope.$parent.inputs;

            $scope.isInputEnabled = isInputEnabled;
        }
    };
});

afModule.directive('afRadio', function(isInputEnabled) {
    return {
        restrict: 'E',
        scope: {
            component: '=',
            input: '='
        },
        templateUrl: 'scripts/af/templates/component-radio.html',
        link: function ($scope) {
            // Forward les saisies du scope courant
            $scope.inputs = $scope.$parent.inputs;

            $scope.isInputEnabled = isInputEnabled;
        }
    };
});

afModule.directive('afSelectMultiple', function(isInputEnabled) {
    return {
        restrict: 'E',
        scope: {
            component: '=',
            input: '='
        },
        templateUrl: 'scripts/af/templates/component-select-multiple.html',
        link: function ($scope) {
            // Forward les saisies du scope courant
            $scope.inputs = $scope.$parent.inputs;

            $scope.toggleSelection = function toggleSelection(optionRef) {
                if ($scope.input.value === null) {
                    $scope.input.value = [];
                }
                var idx = $scope.input.value.indexOf(optionRef);

                // is currently selected
                if (idx > -1) {
                    $scope.input.value.splice(idx, 1);
                }
                // is newly selected
                else {
                    $scope.input.value.push(optionRef);
                }
            };

            $scope.isInputEnabled = isInputEnabled;
        }
    };
});

afModule.directive('afGroup', function($compile) {
    return {
        restrict: 'E',
        scope: {
            component: '=',
            inputSet: '='
        },
        template: '',
        link: function ($scope, element) {
            // On compile manuellement sinon récursion infinie : angular pré-importe les templates
            // même s'ils ne sont pas vraiment utilisés
            element.append('<af-fieldset components="component.components" label="component.label" input-set="inputSet"></af-fieldset>');
            $compile(element.contents())($scope.$new());
        }
    };
});

afModule.directive('afSubSingle', function($compile) {
    return {
        restrict: 'E',
        scope: {
            component: '=',
            input: '='
        },
        template: '',
        link: function ($scope, element) {
            // On compile manuellement sinon récursion infinie : angular pré-importe les templates
            // même s'ils ne sont pas vraiment utilisés
            element.append('<af-fieldset components="component.calledAF.components" label="component.label" input-set="input.value"></af-fieldset>');
            $compile(element.contents())($scope.$new());
        }
    };
});

afModule.directive('afSubMulti', function($compile) {
    return {
        restrict: 'E',
        scope: {
            component: '=',
            input: '='
        },
        template: '',
        link: function ($scope, element) {
            $scope.add = function () {
                if ($scope.input === null) {
                    $scope.input = {};
                }
                if (angular.isUndefined($scope.input.value) || $scope.input.value === null) {
                    $scope.input.value = [];
                }
                $scope.input.value.push({});
            };

            var template =
                '<fieldset>' +
                    '<legend>{{ component.label }}</legend>' +
                    '<af-horizontal-fieldset components="component.calledAF.components" input-sets="input.value"></af-horizontal-fieldset>' +
                    '<button type="button" class="btn btn-default" ng-click="add()">' + __('UI', 'verb', 'add') + '</button>' +
                '</fieldset>';

            // On compile manuellement sinon récursion infinie : angular pré-importe les templates
            // même s'ils ne sont pas vraiment utilisés
            element.append(template);
            $compile(element.contents())($scope.$new());
        }
    };
});
