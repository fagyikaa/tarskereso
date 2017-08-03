'use strict';

App.controller('UserProfileController', ['$scope', '$rootScope', '$state', 'MediaImageHelperService', '$http', 'UserFriendshipHelperService', function ($scope, $rootScope, $state, MediaImageHelperService, $http, UserFriendshipHelperService) {
        $scope.userId;
        $scope.allowedMimeTypes;
        $scope.maxFileSize;
        $scope.profileImageSrc;
        $scope.profileImageId;
        $scope.friendship;
        $scope.friendshipConstants;
        $scope.age;

        /**
         * Sets userId, friendship and friendshipConstants. If friendship isn't object then sets friendship to
         * false. Gets the allowedMimeTypes and maxFileSize from MediaImageHelperService.
         * 
         * @param {Integer} userId
         * @param {Object} friendship
         * @param {Array} friendshipConstants
         * @param {DateTime} birthDate
         */
        $scope.init = function (userId, friendship, friendshipConstants, birthDate) {
            $scope.userId = userId;
            $scope.age =  $scope.getAge(birthDate.date);
            if (false === angular.isObject(friendship)) {
                $scope.friendship = false;
            } else {
                $scope.friendship = friendship;
            }

            $scope.friendshipConstants = friendshipConstants;
            MediaImageHelperService.getFileFilters().then(function (obj) {
                $scope.allowedMimeTypes = obj.allowedMimeTypes;
                $scope.maxFileSize = obj.maxFileSize;
            });
        };

        /**
         * Returns the calculated age from birthDateString
         * 
         * @param {String} birthDateString
         * @returns {Number}
         */
        $scope.getAge = function (birthDateString) {
            return moment().diff(birthDateString, 'years');
        };
        
        /**
         * On handleBirthDateChanged event refresh $scope.age with birthDate in birthDateObj.
         */
        $scope.$on('handleBirthDateChanged', function(eventArgs, birthDateObj) {
            $scope.age = $scope.getAge(birthDateObj.birthDate);
        });

        /**
         * On handleUploadImageSuccess event if image is profile picture
         * then pulls down the new src of the image.
         */
        $scope.$on('handleUploadImageSuccess', function (eventArgs, image) {
            if (image.isProfile === true) {
                $scope.initProfileImageSrc();
            }
        });

        /**
         * On handleImageEditSuccess event pulls down the profile image src.
         */
        $scope.$on('handleImageEditSuccess', function () {
            $scope.initProfileImageSrc();
        });

        /**
         * On handleImageRemoveSuccess event pulls down the profile image src.
         */
        $scope.$on('handleImageRemoveSuccess', function () {
            $scope.initProfileImageSrc();
        });

        /**
         * Pulls down the UserFriendship between the current user and the user with the id of $scope.userId.
         * If the response's status is greater then or equals 400 then set friendship to false.
         */
        $scope.updateFriendship = function () {
            UserFriendshipHelperService.getFriendshipWith($scope.userId).then(function (result) {
                if (result.status >= 400) {
                    $scope.friendship = false;
                } else {
                    $scope.friendship = result.data;
                }
            });
        };

        /**
         * Returns true if the friendship's requester is the current user, false otherwise or if no friendship exists.
         * 
         * @returns {Boolean}
         */
        $scope.requestedBySelf = function () {
            if ($scope.friendship === false) {
                return false;
            }
            return $scope.friendship.requesterId === $rootScope.userId;
        };

        /**
         * If the user with userId has requested a friendship with the current user then accepts it.
         * 
         * @returns {undefined}
         */
        $scope.acceptFriend = function () {
            if ($scope.friendship !== false && $scope.requestedBySelf() === false && $scope.friendship.status === $scope.friendshipConstants.STATUS_PENDING) {
                UserFriendshipHelperService.acceptFriend($scope.userId).then(function (result) {
                    $scope.handleFriendshipResult(result);
                });
            }
        };

        /**
         * If the user with userId and the current user has a pending friendship then declines it.
         * 
         * @returns {undefined}
         */
        $scope.declineFriend = function () {
            if ($scope.friendship !== false && $scope.friendship.status !== $scope.friendshipConstants.STATUS_DECLINED) {
                UserFriendshipHelperService.declineFriend($scope.userId).then(function (result) {
                    $scope.handleFriendshipResult(result);
                });
            }
        };

        /**
         * Sends a friend request from the current user to the user with userId.
         */
        $scope.addFriend = function () {
            if ($scope.friendship === false || $scope.friendship.status !== $scope.friendshipConstants.STATUS_PENDING) {
                UserFriendshipHelperService.addFriend($scope.userId).then(function (result) {
                    $scope.handleFriendshipResult(result);
                });
            }
        };

        /**
         * If the current suer has blocked the user with userId then sends an unblock request.
         */
        $scope.removeBlock = function () {
            if ($scope.requestedBySelf() && $scope.friendship.status === $scope.friendshipConstants.STATUS_BLOCKED) {
                UserFriendshipHelperService.unblock($scope.userId).then(function (result) {
                    $scope.handleFriendshipResult(result);
                });
            }
        };

        /**
         * If the current user and the user with userId isn't blocked then sends a block request.
         */
        $scope.block = function () {
            if ($scope.friendship === false || $scope.friendship.status !== $scope.friendshipConstants.STATUS_BLOCKED) {
                UserFriendshipHelperService.block($scope.userId).then(function (result) {
                    $scope.handleFriendshipResult(result);
                });
            }
        };

        /**
         * If the status of the result is greater then or equals 400 then calls updateFriendship(),
         * sets friendship to result.data otherwise.
         * 
         * @param {Response} result
         */
        $scope.handleFriendshipResult = function (result) {
            if (result.status >= 400) {
                $scope.updateFriendship();
            } else {
                $scope.friendship = result.data;
            }
        };

        /**
         * Go to the show messages state and opens conversation with the user with the id of userId.
         * 
         * @param {Integer} userId
         */
        $scope.writeMessage = function (userId) {
            if (userId === $rootScope.userId) {
                return;
            }

            $state.go('messages', {userId: $rootScope.userId, target: userId});
        };

        /**
         * Go to the show messages state and opens last conversation of the user with the id of userId.
         * This method is only available for admins, restricted serverside.
         * 
         * @param {Integer} userId
         */
        $scope.openUserMessages = function (userId) {
            if (userId === $rootScope.userId) {
                return;
            }

            $state.go('messages', {userId: userId, target: 'last'});
        };

        /**
         * Pulls down the user's profile image's src and id.
         */
        $scope.initProfileImageSrc = function () {
            $scope.profileImageSrc = Routing.generate('api_core_user_serve_profile_image_thumbnail', {userId: $scope.userId, size: 500, _locale: Translator.locale, _ts: new Date().getTime()});
            $http.get(Routing.generate('api_core_user_get_profile_picture_id', {userId: $scope.userId, _locale: Translator.locale})).then(function (response) {
                $scope.profileImageId = response.data;
            });
        };

        /**
         * Opens the image uploading modal.
         */
        $scope.openUploadModal = function () {
            MediaImageHelperService.openUploadModal($scope.userId, 'true', $scope.maxFileSize, $scope.allowedMimeTypes);
        };

        /**
         * Opens the image viewing modal to display profil image.
         */
        $scope.openViewImageModal = function () {
            if ($scope.profileImageId > 0) {
                MediaImageHelperService.openViewModal($scope.profileImageId);
            }
        };

    }]);


