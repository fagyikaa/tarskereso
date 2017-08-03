'use strict';

App.controller('AdminActiveUsersController', ['$scope', '$timeout', 'DTOptionsBuilder', '$wamp', '$state', '$interval', function ($scope, $timeout, DTOptionsBuilder, $wamp, $state, $interval) {

        $scope.users = [];
        $scope.dtInstance = {};
        $scope.datatablesLock;

        $scope.dtOptions = DTOptionsBuilder
                .newOptions()
                .withBootstrap();

        /**
         * Calls users/get_online_users websocket function with a lock strategy 
         * to get the actual online users array which then is put into $scope.users.
         * The lock strategy is required because these asynchronious calls (especially on join and on state change)
         * may conflict with dataTables and cause a 'Cannot reinitialise Datatable' error
         */
        $scope.wampCallGetOnlineUsersWithLockStrategy = function () {
            var interval = $interval(function () {
                if (false === $scope.datatablesLock) {
                    $scope.datatablesLock = true;
                    $wamp.call('user/get_online_users').then(
                            function (result) {
                                $timeout(function () {
                                    $scope.users = result;
                                    $scope.datatablesLock = false;
                                    $interval.cancel(interval);
                                    interval = undefined;
                                });
                            },
                            function (error) {
                                $scope.datatablesLock = false;
                                $interval.cancel(interval);
                                interval = undefined;
                            });
                }
            }, 100);
        };

        /**
         * Calls users/get_online_users websocket function to get the actual online users array
         * then subscribes to the topics: wamp.session.on_join, wamp.session.on_leave, user/state_change
         * to call $scope.wampCallGetOnlineUsersWithLockStrategy function on each event.
         */
        $scope.init = function () {
            $scope.datatablesLock = false;
            $wamp.call('user/get_online_users').then(
                    function (result) {
                        $scope.users = result;
                    });

            $wamp.subscribeOnScope($scope, 'wamp.session.on_join', function (args) {
                $scope.wampCallGetOnlineUsersWithLockStrategy();
            });

            $wamp.subscribeOnScope($scope, 'wamp.session.on_leave', function (args) {
                $scope.wampCallGetOnlineUsersWithLockStrategy();
            });

            $wamp.subscribeOnScope($scope, 'user/state_change', function (args) {
                $scope.wampCallGetOnlineUsersWithLockStrategy();
            });
        };

        /**
         * Returns the translation of the translator key stateTranslatorKey in the 
         * translation domain translatorKeyDomain.
         * 
         * @param {String} stateTranslatorKey
         * @param {String} translatorKeyDomain
         * @returns {String}
         */
        $scope.translateState = function (stateTranslatorKey, translatorKeyDomain) {
            return Translator.trans(stateTranslatorKey, {}, translatorKeyDomain);
        };

        /**
         * Returns the src of the given user's profile picture
         * 
         * @param {Integer} userId
         * @returns {String}
         */
        $scope.getUserProfileImageSrc = function (userId) {
            //Using ts here for disabling cache causes endless loop due to angular
            return Routing.generate('api_core_user_serve_profile_image_thumbnail', {userId: userId, size: 40, _locale: Translator.locale});
        };

    }]);
