'use strict';

App.factory('UserFriendshipHelperService', ['$rootScope', '$http', function ($rootScope, $http) {
        var service = {};

        /**
         * Sends a friend request to the user with userId.
         * 
         * @param {Integer} userId
         */
        service.addFriend = function (userId) {
            return $http.post(Routing.generate('api_core_user_add_friend', {_locale: Translator.locale}), {userId: userId})
                    .then(function (response) {
                        return response;
                    }, function (response) {
                        return response;
                    });
        };

        /**
         * Sends request to accept the friend request from the user with userId.
         * 
         * @param {Integer} userId
         */
        service.acceptFriend = function (userId) {
            return $http.post(Routing.generate('api_core_user_accept_friend', {_locale: Translator.locale}), {userId: userId})
                    .then(function (response) {
                        return response;
                    }, function (response) {
                        return response;
                    });
        };

        /**
         * Sends request to set the friendship to decline with the user with userId.
         * 
         * @param {Integer} userId
         */
        service.declineFriend = function (userId) {
            return $http.post(Routing.generate('api_core_user_decline_friend', {_locale: Translator.locale}), {userId: userId})
                    .then(function (response) {
                        return response;
                    }, function (response) {
                        return response;
                    });
        };

        /**
         * Sends request to block the user with userId.
         * 
         * @param {Integer} userId
         */
        service.block = function (userId) {
            return $http.post(Routing.generate('api_core_user_block_friend', {_locale: Translator.locale}), {userId: userId})
                    .then(function (response) {
                        return response;
                    }, function (response) {
                        return response;
                    });
        };

        /**
         * Sends request to unblock the user with userId.
         * 
         * @param {Integer} userId
         */
        service.unblock = function (userId) {
            return $http.post(Routing.generate('api_core_user_unblock_friend', {_locale: Translator.locale}), {userId: userId})
                    .then(function (response) {
                        return response;
                    }, function (response) {
                        return response;
                    });
        };

        /**
         * Pulls the riendship between the current user and the user with userId.
         * 
         * @param {Integer} userId
         */
        service.getFriendshipWith = function (userId) {
            return $http.get(Routing.generate('api_core_user_get_friendship_with', {_locale: Translator.locale, userId: userId}))
                    .then(function (response) {
                        return response;
                    }, function (response) {
                        return response;
                    });
        };
        
        /**
         * Pulls the friendships of the user with userId if it's the current user or the current
         * user has the aproprirate role. 
         * 
         * @param {Integer} userId
         */
        service.getFriends = function (userId) {
            return $http.get(Routing.generate('api_core_user_get_friends', {_locale: Translator.locale, userId: userId}))
                    .then(function (response) {
                        return response;
                    }, function (response) {
                        return response;
                    });
        };
        
        /**
         * Pulls the pending friendships of the user with userId if it's the current user or the current
         * user has the aproprirate role. 
         * 
         * @param {Integer} userId
         */
        service.getPendingRequests = function (userId) {
            return $http.get(Routing.generate('api_core_user_get_pending_requests', {_locale: Translator.locale, userId: userId}))
                    .then(function (response) {
                        return response;
                    }, function (response) {
                        return response;
                    });
        };
        
        /**
         * Pulls the blocked friendships of the user with userId if it's the current user or the current
         * user has the aproprirate role. 
         * 
         * @param {Integer} userId
         */
        service.getBlockedFriendships = function (userId) {
            return $http.get(Routing.generate('api_core_user_get_blocked_friendships', {_locale: Translator.locale, userId: userId}))
                    .then(function (response) {
                        return response;
                    }, function (response) {
                        return response;
                    });
        };
        
        /**
         * Pulls the pending unseen friendships of the user with userId if it's the current user or the current
         * user has the aproprirate role (Unseen: invitedSeenAt is null).
         * 
         * @param {Integer} userId
         */
        service.getUnseenPendingRequests = function (userId) {
            return $http.get(Routing.generate('api_core_user_get_unseen_pending_requests', {_locale: Translator.locale, userId: userId}))
                    .then(function (response) {
                        return response;
                    }, function (response) {
                        return response;
                    });
        };
        
        /**
         * Send a request to set every unseen pending requests of the current user to seen.
         */
        service.setUnseenPendingRequestsInvitedSeenAt = function () {
            return $http.post(Routing.generate('api_core_user_set_unseen_pending_requests_invited_seen_at', {_locale: Translator.locale}))
                    .then(function (response) {
                        return response;
                    }, function (response) {
                        return response;
                    });
        };

        /**
         * Distributes the given photos in an array under integer keys starts from 1. 
         * The photos are grouped by splitCount. Each photo object expanded with thumbnailUrl property
         * which is the src of the related photo's thumbnail
         * 
         * @param {Array} photos
         * @param {Integer} splitCount
         * @returns {Array}
         */
        service.distributeUsers = function (userId, friendships, splitCount) {
            var distributedUsers = [];
            var loopIndex = 0, arrayIndex = 0;
            angular.forEach(friendships, function (value, key) {
                if (loopIndex % splitCount === 0) {
                    arrayIndex++;
                    distributedUsers[arrayIndex] = [];
                }
                if (value.invitedDatas.id === userId) {
                    var userDatas = value.requesterDatas;
                } else {
                    var userDatas = value.invitedDatas;
                }
                
                value['thumbnailUrl'] = Routing.generate('api_core_user_serve_profile_image_thumbnail', {userId: userDatas.id, size: 100, _locale: Translator.locale});
                value['userDatas'] = userDatas;
                distributedUsers[arrayIndex].push(value);
                loopIndex++;
            });

            return distributedUsers;
        };

        return service;
    }]);


