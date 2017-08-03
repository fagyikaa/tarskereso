'use strict';

IndexApp.directive('applySelect2', function ($timeout) {
    return {
        restrict: 'A',
        require: 'ngModel',
        link: function (scope, element, attr, ngModel) {
            scope.select2Init = function (element) {
                element.addClass('select2');
                var select2 = element.select2({
                    width: '100%',
                    placeholder: Translator.trans('common.choose_an_option'),
                    allowClear: element.attr('allowclear') || false,
                    minimumResultsForSearch: -1
                });
            };

            $timeout(function () {
                scope.select2Init(element);
            });

            scope.$on('select2ReInit', function (event, data) {
                scope.select2Init(data.element);
            });
        }
    };
});

