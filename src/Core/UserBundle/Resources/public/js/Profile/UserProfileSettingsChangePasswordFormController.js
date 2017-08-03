'use strict';

App.controller('UserProfileSettingsChangePasswordFormController', ['$scope', '$http', function ($scope, $http) {
        $scope.data = {};
        $scope.errors = {};

        /**
         * Submits the change password form. In case of error the error variables set,
         * in case of success the form is reseted.
         */
        $scope.submit = function () {
            $http.post(Routing.generate('api_core_user_profile_change_password', {_locale: Translator.locale}), $.param($scope.data), {
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            }).then(function (response) {
                $scope.errors = {};
                $scope.resetForm();
            }, function (response) {
                if (response.status == 400) {
                    $scope.errors.global = response.data.form.errors;
                    $scope.errors.fields = response.data.form.children;
                }
            });
        };

        /**
         * Searches for the CSRF token of the form and deletes form data except the token
         * and set the form to pristine.
         */
        $scope.resetForm = function () {
            for (var prop in $scope.data) {
                if (prop.indexOf('_token') > -1) {
                    var token = $scope.data[prop];
                    $scope.data = {};
                    $scope.data[prop] = token;
                }
            }
            $scope.core_user_change_password.$setPristine();
        };
    }]);


