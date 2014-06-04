var afModule = angular.module('AF', []);

afModule.filter('debug', function() {
    return function(input) {
        return JSON.stringify(input, undefined, 2);
    };
});

// Todo à supprimer
afModule.factory('af', function () {
    return af;
    return {
        components: [
            {
                type: 'subaf-single',
                ref: 'subAFSingle',
                label: 'Sous-formulaire non répété',
                visible: true,
                calledAF: {
                    label: 'Test',
                    components: [
                        {
                            type: 'numeric',
                            ref: 'chiffre_affaire',
                            label: 'Chiffre d\'affaire',
                            visible: true,
                            required: true
                        },
                        {
                            type: 'checkbox',
                            ref: 'check',
                            label: 'Checkbox',
                            visible: true,
                            required: true
                        }
                    ]
                }
            },
            {
                type: 'subaf-multi',
                ref: 'subAFMulti',
                label: 'Sous-formulaire répété',
                visible: true,
                calledAF: {
                    label: 'Test',
                    components: [
                        {
                            type: 'numeric',
                            ref: 'chiffre_affaire',
                            label: 'Chiffre d\'affaire',
                            visible: true,
                            required: true
                        },
                        {
                            type: 'text',
                            ref: 'text',
                            label: 'Champ de text court',
                            visible: true,
                            required: true
                        }
                    ]
                }
            }
        ]
    };
});

// Todo à supprimer
afModule.factory('inputSet', function () {
    return {};
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

        // Compare to condition
        return input.value === condition.value;
    };
});

afModule.factory('isInputVisible', ['testCondition', function (testCondition) {
    return function (input, component, inputs) {
        if (angular.isUndefined(component.actions)) {
            return component.visible;
        }

        var actions = component.actions.filter(function (action) {
            return action.type === 'show';
        });

        // No actions on this component
        if (actions.length === 0) {
            return component.visible;
        }

        return actions.reduce(function (result, action) {
            return result && testCondition(action.condition, inputs);
        }, true);
    };
}]);

afModule.controller('InputController', ['$scope', 'af', 'inputSet', function ($scope, af, inputSet) {
    $scope.af = af;
    $scope.inputSet = inputSet;
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
                console.log(component);
                if (angular.isUndefined($scope.inputSet)) {
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
        templateUrl: 'scripts/af/templates/component.html'
    };
});
afModule.directive('afHorizontalComponent', function() {
    return {
        restrict: 'E',
        scope: {
            component: '=',
            input: '='
        },
        templateUrl: 'scripts/af/templates/horizontal-component.html'
    };
});

afModule.directive('afNumericField', function() {
    return {
        restrict: 'E',
        scope: {
            component: '=',
            input: '='
        },
        templateUrl: 'scripts/af/templates/component-numeric-field.html'
    };
});

afModule.directive('afSelect', function() {
    return {
        restrict: 'E',
        scope: {
            component: '=',
            input: '='
        },
        templateUrl: 'scripts/af/templates/component-select.html'
    };
});

afModule.directive('afCheckbox', function() {
    return {
        restrict: 'E',
        scope: {
            component: '=',
            input: '='
        },
        templateUrl: 'scripts/af/templates/component-checkbox.html'
    };
});

afModule.directive('afTextField', function() {
    return {
        restrict: 'E',
        scope: {
            component: '=',
            input: '='
        },
        templateUrl: 'scripts/af/templates/component-text-field.html'
    };
});

afModule.directive('afTextarea', function() {
    return {
        restrict: 'E',
        scope: {
            component: '=',
            input: '='
        },
        templateUrl: 'scripts/af/templates/component-textarea.html'
    };
});

afModule.directive('afRadio', function() {
    return {
        restrict: 'E',
        scope: {
            component: '=',
            input: '='
        },
        templateUrl: 'scripts/af/templates/component-radio.html'
    };
});

afModule.directive('afSelectMultiple', function() {
    return {
        restrict: 'E',
        scope: {
            component: '=',
            input: '='
        },
        templateUrl: 'scripts/af/templates/component-select-multiple.html',
        link: function ($scope) {
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
            element.append('<af-fieldset components="component.subComponents" label="component.label" input-set="inputSet"></af-fieldset>');
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
