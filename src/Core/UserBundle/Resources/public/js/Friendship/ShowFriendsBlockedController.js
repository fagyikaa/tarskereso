'use strict';

App.controller('ShowFriendsBlockedController', ['$scope', 'UserFriendshipHelperService', function ($scope, UserFriendshipHelperService) {
        $scope.distributedResult;
        $scope.pagination = {
            countOfResult: 0,
            nextText: '',
            previousText: '',
            currentPage: 0,
            resultPerPage: 0,
            maxSize: 0,
            showFirstLast: true
        };
        $scope.userId;

        /**
         * Set userId, nextpage and previouspage translations for pagination, current page to 1 and resultsPerPage to 12
         * then gets the blocked friendships from the server where the requester is the user with the id of userId.
         * 
         * @param {Integer} userId
         */
        $scope.init = function (userId) {
            $scope.userId = userId;
            $scope.distributedResult = [];
            $scope.pagination.nextText = Translator.trans('common.pagination.next', {}, 'messages');
            $scope.pagination.previousText = Translator.trans('common.pagination.prev', {}, 'messages');
            $scope.pagination.currentPage = 1;
            $scope.pagination.resultPerPage = 12;
            $scope.pagination.maxSize = 5;
            $scope.getBlockedFriendships();
        };

        /**
         * Gets the blocked friendships from the server where the requester is the user with the id of userId 
         * then distributes the result into distributedResult and if the count of blocked friendships are less then
         * resultPerPage sets currentPage to 1.
         */
        $scope.getBlockedFriendships = function () {
            UserFriendshipHelperService.getBlockedFriendships($scope.userId).then(function (response) {
                $scope.distributedResult = UserFriendshipHelperService.distributeUsers($scope.userId, response.data, $scope.pagination.resultPerPage);
                $scope.pagination.countOfResult = response.data.length;
                if ($scope.pagination.countOfResult <= $scope.pagination.resultPerPage) {
                    $scope.pagination.currentPage = 1;
                }
            });
        };

        /**
         * Sets the friendship with the user with the id of userId back to declined and refresh blocked friendships.
         * 
         * @param {Integer} userId
         */
        $scope.removeBlock = function (userId) {
            UserFriendshipHelperService.unblock(userId).then(function (result) {
                $scope.getBlockedFriendships();
            });
        };

    }]);

