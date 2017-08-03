'use strict';

App.controller('ShowConversationsController', ['$scope', '$stateParams', '$interval', function ($scope, $stateParams, $interval) {        
        $scope.refresh;
        $scope.userId;
        $scope.target;
        
        /**
         * Sets userId and target and sets moment.js's locale to the current locale.
         * Creates and interval which sets to false then true $scope.refresh every 60seconds
         * which cause to update the frontend element with ng-if="refresh".
         */
        $scope.init = function () {
            $scope.refresh = true;
            $scope.userId = $stateParams.userId;
            $scope.target = $stateParams.target;
            moment.locale(Translator.locale);
            //Refresh created at times
            $interval(function () {
                $scope.refresh = false;
                $scope.refresh = true;
            }, 60000);
        };

    }]);

