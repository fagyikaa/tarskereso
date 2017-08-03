'use strict';

IndexApp.controller('ResetPasswordFormController', ['$scope', '$http', function ($scope, $http) {
        $scope.errors = {};
        $scope.data = {};
        $scope.fatalError = false;

        /**
         * Post the reset password form
         */
        $scope.submit = function (url) {
            $scope.fatalError = false;
            $scope.errors = {};
            $http.post(url, $.param($scope.data), {
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            })
                    .then(function (response) {
                        $scope.$emit('handleResettingSuccess');
                    }, function (response) {
                        if (response.status === 500) {
                            $scope.fatalError = true;
                        } else {
                            $scope.errors.error = response.data;
                        }
                    });
        };
    }]);


