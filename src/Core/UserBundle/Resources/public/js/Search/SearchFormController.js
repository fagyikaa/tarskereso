'use strict';

App.controller('SearchFormController', ['$rootScope', '$scope', '$http', 'UserSearchHelperService', function ($rootScope, $scope, $http, UserSearchHelperService) {
        $scope.data = {};
        $scope.genderCheckbox = {};
        $scope.searchingForCheckbox = {};
        $scope.wantToCheckbox = {};
        $scope.bodyShapeCheckbox = {};
        $scope.hairColorCheckbox = {};
        $scope.hairLengthCheckbox = {};
        $scope.eyeColorCheckbox = {};
        $scope.errors = {};
        $scope.userId = 0;

        /**
         * Sets the actual userId from rootScope.
         */
        $scope.init = function () {
            $scope.userId = $rootScope.userId;
            UserSearchHelperService.setHasAnyFilter($scope.hasAnyFilter);
        };

        /**
         * Set the initial value of the number inputs if have one.
         * 
         * @param {String} field
         * @param {String} value
         */
        $scope.setIntData = function (field, value) {
            if (value !== "") {
                $scope.data[field] = parseInt(value);
            }
        };
        
        /**
         * Set the initial value of the text inputs if have one.
         * 
         * @param {String} field
         * @param {String} value
         */
        $scope.setStringData = function (field, value) {
            if (value !== "") {
                $scope.data[field] = value;
            }
        };

        /**
         * Returns true if the user gived any filter, false otherwise.
         * 
         * @returns {Boolean}
         */
        $scope.hasAnyFilter = function () {
            var counter = 0;
            angular.forEach($scope.data, function (value, index) {
                if (angular.isDefined(value) && value !== null && value !== '') {
                    counter++;
                }
            });

            return counter > 1;
        };

        /**
         * Builds every checkbox field in the format symfony requires and puts into data.
         */
        $scope.buildCheckboxFields = function () {
            $scope.buildCheckboxFieldData($scope.genderCheckbox, 'gender');
            $scope.buildCheckboxFieldData($scope.searchingForCheckbox, 'searchingFor');
            $scope.buildCheckboxFieldData($scope.wantToCheckbox, 'wantTo');
            $scope.buildCheckboxFieldData($scope.bodyShapeCheckbox, 'bodyShape');
            $scope.buildCheckboxFieldData($scope.hairColorCheckbox, 'hairColor');
            $scope.buildCheckboxFieldData($scope.hairLengthCheckbox, 'hairLength');
            $scope.buildCheckboxFieldData($scope.eyeColorCheckbox, 'eyeColor');
        };
        
        /**
         * Builds every checkbox field in the format symfony requires and puts into data and sends the form if
         * it has any filter.
         */
        $scope.buildCheckboxesAndSendFormIfHasAnyFilter = function () {
             $scope.buildCheckboxFields();
             if ($scope.hasAnyFilter()) {
                 $scope.submit();
             }
        };

        /**
         * Builds a checkbox field in the format symfony requires
         */
        $scope.buildCheckboxFieldData = function (data, fieldName) {
            var values = new Array();
            angular.forEach(data, function (value, key) {
                if (value === true) {
                    values.push(key);
                }
            });

            if (values.length > 0) {
                $scope.data['core_user_search[' + fieldName + ']'] = values;
            } else {
                delete $scope.data['core_user_search[' + fieldName + ']'];
            }
        };

        /**
         * If the value of the corresponding property form field is empty, undefined or null
         * then removes the corresponding property from the data object.
         * 
         * @param {String} property
         */
        $scope.removePropertyFromDataIfEmpty = function (property) {
            var value = $scope.data['core_user_search[' + property + ']'];
            if (angular.isUndefined(value) || value === null || value === '') {
                delete $scope.data['core_user_search[' + property + ']'];
            }
        };

        /**
         * Post the searching form
         */
        $scope.submit = function () {
            $scope.errors = {};
            $http.post(Routing.generate('api_core_user_search_users', {userId: $scope.userId, _locale: Translator.locale}), $.param($scope.data), {
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            })
                    .then(function (response) {
                        //broadcasts 'handleSearchResult'
                        UserSearchHelperService.searchResultSuccess(response.data);
                    }, function (response) {
                        if (response.status == 400) {
                            // Set the form errors which come from the server.
                            $scope.errors.global = response.data.data.errors;
                            $scope.errors.fields = response.data.data.form.children;
                        }
                    });
        };
    }]);

