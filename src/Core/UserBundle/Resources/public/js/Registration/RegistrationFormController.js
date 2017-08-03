'use strict';

IndexApp.controller('RegistrationFormController', ['$scope', '$http', function ($scope, $http) {
        $scope.data = {};
        $scope.errors = {};
        $scope.loading = false;
        $scope.fatalError = false;

        /**
         * Post the register form
         */
        $scope.submit = function (url) {
            $scope.loading = true;
            $scope.fatalError = false;
            $scope.errors = {};
            $http.post(url, $.param($scope.data), {
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            })
                    .then(function (response) {
                        if (response.status === 204) {
                            $scope.$emit('handleRegistrationSuccess');
                        }
                        $scope.loading = false;
                    }, function (response) {
                        if (response.status === 500) {
                            $scope.fatalError = true;
                        } else if (response.status === 400) {
                            $scope.errors.global = response.data.form.errors;
                            $scope.errors.fields = response.data.form.children;
                        }
                        $scope.loading = false;
                    });
        };

    }]);


