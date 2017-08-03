'use strict';

App.controller('SearchController', ['$scope', 'UserSearchHelperService', '$state', 'UserFriendshipHelperService', function ($scope, UserSearchHelperService, $state, UserFriendshipHelperService) {
        $scope.hasAnyFilter;
        $scope.friendshipConstants;
        $scope.distributedResult;
        $scope.resultPerPage;
        $scope.pagination = {
            countOfResult: 0,
            nextText: '',
            previousText: '',
            currentPage: 0,
            resultPerPage: 0,
            maxSize: 0,
            showFirstLast: true
        };
        $scope.hasBeenSearched;
        $scope.genderConstants;

        /**
         * Sets friendshipConstants and genderConstants and hasBeenSearched to false. Also
         * sets the pagination's previous- and nextText translations, currentPage to 1 and resultPerPage to 12.
         * 
         * @param {Array} friendshipConstants
         * @param {Array} genderConstants
         */
        $scope.init = function (friendshipConstants, genderConstants) {
            $scope.friendshipConstants = friendshipConstants;
            $scope.genderConstants = genderConstants;
            $scope.hasBeenSearched = false;
            $scope.distributedResult = [];
            $scope.pagination.nextText = Translator.trans('common.pagination.next', {}, 'messages');
            $scope.pagination.previousText = Translator.trans('common.pagination.prev', {}, 'messages');
            $scope.pagination.currentPage = 1;
            $scope.pagination.resultPerPage = 12;
            $scope.pagination.maxSize = 5;
        };

        /**
         * On handleSearchResult event sets hasBeenSearched to true and fills up the distributedResult array and
         * sets the pagination's countOfResult number.
         */
        $scope.$on('handleSearchResult', function (eventArgs, object) {
            $scope.hasBeenSearched = true;
            $scope.distributedResult = $scope.distributeResult(object.result, $scope.pagination.resultPerPage);
            $scope.pagination.countOfResult = object.result.length;
        });

        /**
         * On handleSetHasAnyFilter event sets hasAnyFilter to the one saved in UserSearchHelper
         * to check if the form has any value.
         */
        $scope.$on('handleSetHasAnyFilter', function () {
            $scope.hasAnyFilter = UserSearchHelperService.hasAnyFilterFunction;
        });

        /**
         * Calculates how years old the user from given birthDate.
         * 
         * @param {Date} birthDate
         * @returns {String}
         */
        $scope.getAge = function (birthDate) {
            return moment().diff(birthDate, 'years');
        };

        /**
         * If county and settlement is equal then returns only settlement, returns county + ', ' + settlement otherwise.
         * 
         * @param {String} county
         * @param {String} settlement
         * @returns {String}
         */
        $scope.getAddressString = function (user) {
            if (user.settlement !== user.county) {
                return user.county + ', ' + user.settlement;
            } else {
                return user.settlement;
            }
        };
        
        /**
         * Go to the profile of the user with the id of userId.
         * 
         * @param {Integer} userId
         */
        $scope.goToProfile = function (userId) {
            $state.go('profile.introduction', {userId: userId});
        };

        /**
         * Sends a request to add as friend the given user then alter the user object's invitedStatus
         * according to the result.
         * 
         * @param {Object} user
         */
        $scope.addFriend = function (user) {
            if ((user.requestedStatus && user.requestedStatus === $scope.friendshipConstants.STATUS_PENDING) || 
                    (user.invitedStatus && user.invitedStatus === $scope.friendshipConstants.STATUS_PENDING)) {
                    return true;
            }
        
            UserFriendshipHelperService.addFriend(user.id).then(function (result) {
               if (result.status === 204) {
                   user.invitedStatus = $scope.friendshipConstants.STATUS_PENDING;
               } else {
                   user.invitedStatus = result.data.status;
               }
            }, function (errorResult) {
                
            });
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
         * Distributes the given result (containing the search result) in an array under integer keys 
         * starts from 1, grouped by splitCount. 
         * 
         * @param {Object} result
         * @param {Integer} splitCount
         * @returns {Array}
         */
        $scope.distributeResult = function (result, splitCount) {
            var distributedResult = [];
            var loopIndex = 0, arrayIndex = 0;
            angular.forEach(result, function (value, key) {
                if (loopIndex % splitCount === 0) {
                    arrayIndex++;
                    distributedResult[arrayIndex] = [];
                }
                distributedResult[arrayIndex].push(value);
                loopIndex++;
            });

            return distributedResult;
        };

        /**
         * Gets the profile picture src of the user with userId.
         * 
         * @param {Integer} userId
         * @returns {String}
         */
        $scope.getProfilePictureSrc = function (userId) {
            return Routing.generate('api_core_user_serve_profile_image_thumbnail', {userId: userId, size: 100, _locale: Translator.locale});
        };
        
        /**
         * Translates gender.
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
        
    }]);

