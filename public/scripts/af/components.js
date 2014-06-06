afModule.directive('afFieldset', ['isInputVisible', 'getInput', function(isInputVisible, getInput) {
    return {
        restrict: 'E',
        templateUrl: 'scripts/af/templates/fieldset.html',
        scope: {
            components: '=',
            label: '=',
            inputSet: '='
        },
        link: function ($scope) {
            if (angular.isUndefined($scope.inputSet) || $scope.inputSet === null) {
                $scope.inputSet = {};
            }

            $scope.isInputVisible = isInputVisible;
            $scope.getInput = function (component) {
                return getInput($scope.inputSet, component);
            };
        }
    };
}]);

afModule.directive('afHorizontalFieldset', ['getInput', function(getInput) {
    return {
        restrict: 'E',
        templateUrl: 'scripts/af/templates/horizontal-fieldset.html',
        scope: {
            components: '=',
            inputSets: '='
        },
        link: function ($scope) {
            $scope.getInput = getInput;
        }
    };
}]);

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
