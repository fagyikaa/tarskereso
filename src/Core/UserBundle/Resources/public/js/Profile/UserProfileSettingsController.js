'use strict';

App.controller('UserProfileSettingsController', ['$scope', '$rootScope', '$http', '$window', '$state', 'CommonHelperService', function ($scope, $rootScope, $http, $window, $state, CommonHelperService) {
        $scope.userId;
        $scope.error;
        $scope.user;

        /**
         * Sets userId and user.enabled
         * 
         * @param {Integer} userId
         * @param {Boolean} enabled
         * @param {String} email
         */
        $scope.init = function (userId, enabled, email) {
            $scope.userId = userId;
            $scope.user = {
                enabled: enabled,
                email: email
            };
        };

        /**
         * Posts request to server to edit the user's email property. In case of
         * error the error message returned.
         * 
         * @param {String} email
         * @returns {String}
         */
        $scope.setUserEmail = function (email) {
            var params = {
                email: email
            };

            return $http.post(Routing.generate('api_core_user_edit_user_email', {_locale: Translator.locale}), params)
                    .then(function (response) {
                    }, function (response) {
                        var result = response.data;

                        if (angular.isDefined(result) && result.hasOwnProperty('data') && result.data.constructor === Array && angular.isDefined(result.data[0])) {
                            return result.data[0];
                        } else if (angular.isDefined(result) && result.hasOwnProperty('message')) {
                            return result.message;
                        }

                        return result;
                    });
        };

        /**
         * Posts request to server to edit the user's enabled property. In case of
         * error the error message returned.
         * 
         * @param {Boolean} isEnabled
         * @returns {String}
         */
        $scope.setUserEnabled = function (isEnabled) {
            var params = {
                userId: $scope.userId,
                isEnabled: isEnabled
            };

            return $http.post(Routing.generate('admin_api_core_user_edit_user_enabled', {_locale: Translator.locale}), params)
                    .then(function (response) {
                    }, function (response) {
                        return response.data;
                    });
        };

        /**
         * Returns the translation for the user's enabled property.
         * 
         * @param {Boolean} isEnabled
         * @returns {String}
         */
        $scope.getUserEnabledTranslation = function (isEnabled) {
            if (true === isEnabled) {
                return Translator.trans('settings.enabled.enabled', {}, 'profile');
            } else {
                return Translator.trans('settings.enabled.disabled', {}, 'profile');
            }
        };

        /**
         * Opens confirmation modal and in case of yes, posts a request to the server
         * to delete the user. If the requester is the current user then redirects to
         * login page, if an admin then to search page. In case of error, error message is shown.
         * 
         * @param {Integer} userId
         */
        $scope.deleteUser = function (userId) {
            var header = Translator.trans('settings.delete.confirm_modal.header', {}, 'profile');
            var msg = Translator.trans('settings.delete.confirm_modal.msg', {}, 'profile');
            var dlg = CommonHelperService.confirmModal(header, msg);
            dlg.result.then(function () {
                $http.delete(Routing.generate('api_core_user_delete_user', {_locale: Translator.locale, userId: userId})).then(function (response) {
                    if (response.data.logout === true) {
                        $window.location.href = $rootScope.logoutPath;
                    } else {
                        $state.go('search');
                    }
                }, function (response) {
                    $scope.error = response.data.message;
                });
            }, function (btn) {
            });
        };
    }]);


