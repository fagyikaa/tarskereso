'use strict';

App.controller('UserProfileIntroductionController', ['$scope', '$rootScope', 'UserProfileHelperService', function ($scope, $rootScope, UserProfileHelperService) {
        $scope.fieldsData;
        $scope.readOnly = true;
        $scope.userId;
        $scope.category;
        $scope.possibleSelectValues = {};
        $scope.UserProfileHelperService = UserProfileHelperService;

        /**
         * Gets the user's introduction field datas from the server and that these datas are read only by the current user or not.
         * If not read only also get the possible values for select fields. Also set the userId and category scope variables.
         * 
         * @param {Int} userId
         * @param {String} category
         */
        $scope.init = function (userId, category) {
            $scope.userId = userId;
            $scope.category = category;

            UserProfileHelperService.getProfileData($scope.userId, $scope.category).then(function (profileDatas) {
                $scope.fieldsData = profileDatas.fieldsData;
                $scope.readOnly = profileDatas.readOnly;
                if (angular.isDefined(profileDatas.possibleSelectValues)) {
                    $scope.possibleSelectValues = profileDatas.possibleSelectValues;
                }
            });
        };

        /**
         * Updates UserIntroduction's property with data for the user with userId. Result true in case of success,
         * string in case of failure. isBirthDate is given and true if the field is the birth date selector.
         * 
         * @param {String} data
         * @param {String} property
         * @param {True|undefined}
         * @return {Promise}
         */
        $scope.updateUser = function (data, property, isBirthDate) {
            if (property === 'address') {
                angular.forEach($scope.possibleSelectValues['address'], function (value, key) {
                    if (value['value'] === data) {
                        data = value['id'];
                    }
                });
            }

            return $scope.UserProfileHelperService.updateUser(data, property, $scope.userId, $scope.category).then(function (result) {
                if (true === isBirthDate && angular.isUndefined(result)) {
                    $rootScope.$broadcast('handleBirthDateChanged', {birthDate: data});
                } 
                
                return result;
            });
        };

        /**
         * Translates the data of user's property or if data is undefined then default text.
         * 
         * @param {String} data
         * @param {String} property
         * @returns {String}
         */
        $scope.getTranslatedNameForSelect = function (data, property) {
            return UserProfileHelperService.getTranslatedNameForSelect(data, property);
        };

    }]);


