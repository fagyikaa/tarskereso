'use strict';

var IndexApp = angular.module('IndexApp', [
    'ngMessages',
    'counter'
]);

IndexApp.controller('IndexAppController', ['$scope', function ($scope) {
        $scope.malesCount;
        $scope.femalesCount;
        $scope.showLoginForm;
        $scope.resettingSuccess;
        $scope.registrationSuccess;

        $scope.init = function (activeUsers) {
            $scope.resettingSuccess = false;
            $scope.registrationSuccess = false;
            $scope.showLoginForm = true;
            $scope.malesCount = activeUsers[0]['activeCount'];
            $scope.femalesCount = activeUsers[1]['activeCount'];
        };

        $scope.setShowLoginForm = function (bool) {
            $scope.showLoginForm = bool;
        };

        $scope.$on('handleResettingSuccess', function () {
            $scope.showLoginForm = true;
            $scope.resettingSuccess = true;
        });
        
        $scope.$on('handleRegistrationSuccess', function () {
            $scope.registrationSuccess = true;
        });
        
    }]);

