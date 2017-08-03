'use strict';

App.controller('ShowFriendsController', ['$rootScope', '$scope', '$state', '$stateParams', 'UserFriendshipHelperService', function ($rootScope, $scope, $state, $stateParams, UserFriendshipHelperService) {
        $scope.userId;
        $scope.genderConstants;

        /**
         * Sets userId and genderConstants and if the tab stateParam is pending then navigates
         * to the pending tab and sets every unseen pending friend request of the user to seen
         * by setting invitedSeenAt to now.
         * 
         * @param {Integer} userId
         * @param {Array} genderConstants
         */
        $scope.init = function (userId, genderConstants) {
            $scope.userId = userId;
            $scope.genderConstants = genderConstants;
            if ('pending' === $stateParams.tab) {
                angular.element('#tab-pending').trigger('click');
                $scope.setUnseenPendingRequestsInvitedSeenAt();
            }
        };

        /**
         * Sets the state's tab param to tabParam (actual tab).
         * It is needed due to requests through websockets, if the current tab
         * is pending then it is unnecessary to get the count.
         * 
         * @param {String} tabParam
         */
        $scope.changeStateTabParam = function (tabParam) {
            $stateParams.tab = tabParam;
        };

        /**
         * Sets the current user's (if not an admin viewing a user's friendships) every unseend 
         * pending requests to seen by setting invitedSeenAt to now.
         */
        $scope.setUnseenPendingRequestsInvitedSeenAt = function () {
            if ($scope.userId === $rootScope.userId) {
                UserFriendshipHelperService.setUnseenPendingRequestsInvitedSeenAt().then(function (response) {
                    if (response.status === 204) {
                        $rootScope.$broadcast('handleUnseenPendingRequestsChecked');
                    }
                });
            }
        };

        /**
         * On handleNewFriendshipRequestWebsocket event if the currently opened tab
         * is the pending then calls setUnseenPendingRequestsInvitedSeenAt().
         */
        $scope.$on('handleNewFriendshipRequestWebsocket', function () {
            if ('friends' === $state.current.name && 'pending' === $state.params.tab) {
                $scope.setUnseenPendingRequestsInvitedSeenAt();
            }
        });

        /**
         * Go to the profile of the user with the id of userId.
         * 
         * @param {Integer} userId
         */
        $scope.goToProfile = function (userId) {
            $state.go('profile.introduction', {userId: userId});
        };

        /**
         * Go to the show messages state and opens conversation with the user with the id of userId.
         * 
         * @param {Integer} userId
         */
        $scope.writeMessage = function (userId) {
            $state.go('messages', {userId: $scope.userId, target: userId});
        };

        /**
         * Returns the translated string of the gender string.
         * 
         * @param {String} gender
         * @returns {String}
         */
        $scope.getGenderString = function (gender) {
            if ($scope.genderConstants.GENDER_MALE === gender) {
                return Translator.trans('user.gender.male', {}, 'profile');
            } else {
                return Translator.trans('user.gender.female', {}, 'profile');
            }
        };

        /**
         * If county and settlement is equal then returns only settlement, returns county + ', ' + settlement otherwise.
         * 
         * @param {String} county
         * @param {String} settlement
         * @returns {String}
         */
        $scope.getAddressString = function (county, settlement) {
            if (settlement !== county) {
                return county + ', ' + settlement;
            } else {
                return settlement;
            }
        };

    }]);

