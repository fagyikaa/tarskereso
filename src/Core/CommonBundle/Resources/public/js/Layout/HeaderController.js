'use strict';

App.controller('HeaderController', ['$scope', '$window', '$location', function ($scope, $window, $location) {

        /**
         * Reloads the current page in the selected language.
         * 
         * @param {String} language
         * @param {String} url
         */
        $scope.selectLanguage = function (language, url) {
            $window.location.href = url + '#' + $location.path();
        };

    }]);


