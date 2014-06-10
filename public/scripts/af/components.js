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
            $scope.isCollapsed = false;
            $scope.toggle = function () {
                $scope.isCollapsed = !$scope.isCollapsed;
            };
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
            $scope.inputSet = $scope.$parent.inputSet;
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
            $scope.inputSet = $scope.$parent.inputSet;
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
            $scope.inputSet = $scope.$parent.inputSet;
            $scope.inputs = $scope.$parent.inputs;

            $scope.isInputEnabled = isInputEnabled;

            $scope.defaultValueReminder = __('AF', 'inputInput', 'defaultValueReminder') + ' '
                + $scope.component.defaultValue.digitalValue + ' ' + $scope.component.unit.symbol
                + ' ± ' + ($scope.component.defaultValue.uncertainty || '0') + ' %';

            $scope.toggleHistory = function ($event) {
                $($event.target).popover('toggle');
            };
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
            $scope.inputSet = $scope.$parent.inputSet;
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
            $scope.inputSet = $scope.$parent.inputSet;
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
            $scope.inputSet = $scope.$parent.inputSet;
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
            $scope.inputSet = $scope.$parent.inputSet;
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
            $scope.inputSet = $scope.$parent.inputSet;
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
            $scope.inputSet = $scope.$parent.inputSet;
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
            // Init the input
            if ($scope.input === null) {
                $scope.input = {};
            }
            if (angular.isUndefined($scope.input.value) || $scope.input.value === null) {
                $scope.input.value = [];
            }

            $scope.add = function () {
                $scope.input.value.push({});
            };

            switch ($scope.component.init) {
                case 'one_deletable':
                case 'one_not_deletable':
                    $scope.add();
                    break;
            }

            var template =
                '<fieldset>' +
                    '<legend ng-click="toggle()">' +
                        '<i class="fa fa-chevron-right" ng-show="isCollapsed"></i>' +
                        '<i class="fa fa-chevron-down" ng-show="!isCollapsed"></i>' +
                        '{{ component.label }}' +
                    '</legend>' +
                    '<div collapse="isCollapsed">' +
                        '<af-horizontal-fieldset components="component.calledAF.components" input-sets="input.value"></af-horizontal-fieldset>' +
                        '<button type="button" class="btn btn-default" ng-click="add()">' + __('UI', 'verb', 'add') + '</button>' +
                    '</div>' +
                '</fieldset>';

            $scope.isCollapsed = false;
            $scope.toggle = function () {
                $scope.isCollapsed = !$scope.isCollapsed;
            };

            // On compile manuellement sinon récursion infinie : angular pré-importe les templates
            // même s'ils ne sont pas vraiment utilisés
            element.append(template);
            $compile(element.contents())($scope.$new());
        }
    };
});

/**
 * Historique d'une saisie
 */
afModule.directive('afHistory', function($http) {
    return {
        restrict: 'E',
        scope: {
            input: '=',
            inputSet: '='
        },
        templateUrl: 'scripts/af/templates/history.html',
        link: function ($scope, $element) {
            var button = $element.find('button');
            var historyFetched = false;
            var url = 'af/input/input-history?inputSet=' + $scope.inputSet.id + '&input=' + $scope.input.id;

            button.popover({
                placement: 'bottom',
                title: __('UI', 'history', 'valueHistory'),
                html: true,
                content: '<p class="text-center"><img src="images/ui/ajax-loader.gif"></p>'
            }).on('show.bs.popover', function () {
                if (historyFetched) {
                    return;
                }

                $http.get(url).success(function (html) {
                    button.data('bs.popover').options.content = html;
                    historyFetched = true;
                    button.popover('show');
                }).error(function () {
                    addMessage(__('Core', 'exception', 'applicationError'), 'error');
                });
            });
        }
    };
});
