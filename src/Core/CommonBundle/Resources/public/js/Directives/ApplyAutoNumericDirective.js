App.directive('applyAutoNumeric', function ($filter) {
    return {
        restrict: 'A',
        require: 'ngModel',
        compile: function (tElm, tAttrs) {

            var isTextInput = tElm.is('input:text');

            return function (scope, elm, attrs, controller) {

                var options = {
                    mDec: 0
                };
                var opts = angular.extend({}, options, scope.$eval(attrs.applyAutoNumeric));

                // Helper method to update autoNumeric with new value.
                var updateElement = function (element, newVal) {
                    // Only set value if value is numeric
                    if ($.isNumeric(newVal)) {
                        element.autoNumeric('set', newVal);
                    } else {
                        element.autoNumeric('set', '');
                    }
                };

                // if element has controller, wire it (only for <input type="text" />)
                if (controller && isTextInput) {
                    // render element as autoNumeric
                    controller.$render = function () {
                        // Initialize element as autoNumeric with options.
                        elm.autoNumeric('init', opts);
                        updateElement(elm, controller.$viewValue);
                    };
                    // Detect changes on element and update model.
                    elm.on('change', function (e) {
                        scope.$apply(function () {
                            controller.$setViewValue($filter('removeNumberFormat')(elm.val()));
                        });
                    });
                    // Detect changes on element when key up and update model.
                    elm.on('keyup', function (e) {
                        scope.$apply(function () {
                            controller.$setViewValue($filter('removeNumberFormat')(elm.val()));
                        });
                    });
                } else {
                    // Listen for changes to value changes and re-render element.
                    // Useful when binding to a readonly input field.
                    if (isTextInput) {
                        attrs.$observe('value', function (val) {
                            updateElement(elm, val);
                        });
                    }
                }
            }
        }
    };
});


