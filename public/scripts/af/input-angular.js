var afModule = angular.module('AF', []);

afModule.filter('debug', function() {
    return function(input) {
        return JSON.stringify(input, undefined, 2);
    };
});

afModule.controller('InputController', function ($scope) {
    $scope.af = {
        label: 'Données générales',
        components: [
            {
                type: 'numeric',
                ref: 'chiffre_affaire',
                label: 'Chiffre d\'affaire',
                required: true
            },
            {
                type: 'select',
                ref: 'gaz',
                label: 'Gaz',
                required: true,
                options: [
                    {
                        ref: 'co2',
                        label: 'CO2'
                    },
                    {
                        ref: 'hxo',
                        label: 'HxO'
                    }
                ]
            },
            {
                type: 'checkbox',
                ref: 'check',
                label: 'Checkbox',
                required: true
            },
            {
                type: 'group',
                ref: 'groupe',
                label: 'Groupe',
                subComponents: [
                    {
                        type: 'text',
                        ref: 'text',
                        label: 'Champ de text court',
                        required: true
                    },
                    {
                        type: 'textarea',
                        ref: 'textarea',
                        label: 'Champ de text long',
                        required: true
                    }
                ]
            },
            {
                type: 'radio',
                ref: 'choixSimple',
                label: 'Choix simple',
                required: true,
                options: [
                    {
                        ref: 'bli',
                        label: 'Bli'
                    },
                    {
                        ref: 'blah',
                        label: 'Blah'
                    }
                ]
            },
            {
                type: 'select-multiple',
                ref: 'gazMultiple',
                label: 'Gaz',
                required: true,
                options: [
                    {
                        ref: 'co2',
                        label: 'CO2'
                    },
                    {
                        ref: 'hxo',
                        label: 'HxO'
                    }
                ]
            },
            {
                type: 'subaf-single',
                ref: 'subAFSingle',
                label: 'Sous-formulaire non répété',
                calledAF: {
                    label: 'Test',
                    components: [
                        {
                            type: 'numeric',
                            ref: 'chiffre_affaire',
                            label: 'Chiffre d\'affaire',
                            required: true
                        },
                        {
                            type: 'checkbox',
                            ref: 'check',
                            label: 'Checkbox',
                            required: true
                        }
                    ]
                }
            }
        ]
    };

    $scope.inputSet = {
        completion: 65,
        status: __('AF', 'inputInput', 'statusInputIncomplete'),
        inputs: [
            {
                componentRef: 'chiffre_affaire',
                value: {
                    digitalValue: 10.45,
                    uncertainty: 20,
                    unit: 'euro'
                }
            },
            {
                componentRef: 'gaz',
                value: 'hxo'
            },
            {
                componentRef: 'check',
                value: true
            },
            {
                componentRef: 'text',
                value: 'Bonsoir Paris !'
            },
            {
                componentRef: 'choixSimple',
                value: 'bli'
            },
            {
                componentRef: 'gazMultiple',
                value: [ 'co2', 'hxo' ]
            },
            {
                componentRef: 'textarea',
                value: 'Bonsoir Paris !'
            },
            {
                componentRef: 'subAFSingle',
                value: {
                    inputs: [
                        {
                            componentRef: 'chiffre_affaire',
                            value: {
                                digitalValue: 10.45,
                                uncertainty: 20,
                                unit: 'euro'
                            }
                        },
                        {
                            componentRef: 'check',
                            value: true
                        }
                    ]
                }
            }
        ]
    };
});

afModule.directive('afFieldset', function() {
    return {
        restrict: 'E',
        templateUrl: 'scripts/af/templates/fieldset.html',
        scope: {
            components: '=',
            label: '=',
            inputSet: '='
        },
        link: function ($scope) {
            $scope.getInput = function (component) {
                if (angular.isUndefined($scope.inputSet)) {
                    return null;
                }
                var inputs = $scope.inputSet.inputs;
                for (i = 0; i < inputs.length; ++i) {
                    var input = inputs[i];
                    if (input.componentRef === component.ref) {
                        return input;
                    }
                }
                return null;
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
