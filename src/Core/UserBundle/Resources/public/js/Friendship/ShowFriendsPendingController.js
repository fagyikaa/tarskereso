'use strict';

App.controller('ShowFriendsPendingController', ['$rootScope', '$scope', 'UserFriendshipHelperService', function ($rootScope, $scope, UserFriendshipHelperService) {
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
         * then gets the pending requests from the server where the invited is the user with the id of userId.
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
            $scope.getPendingRequests();
        };

        /**
         * Gets the pending requests from the server where the invited is the user with the id of userId 
         * then distributes the result into distributedResult and if the count of pending requests less then
         * resultPerPage sets currentPage to 1.
         */
        $scope.getPendingRequests = function () {
            UserFriendshipHelperService.getPendingRequests($scope.userId).then(function (response) {
                $scope.distributedResult = UserFriendshipHelperService.distributeUsers($scope.userId, response.data, $scope.pagination.resultPerPage);
                $scope.pagination.countOfResult = response.data.length;
                if ($scope.pagination.countOfResult <= $scope.pagination.resultPerPage) {
                    $scope.pagination.currentPage = 1;
                }
            });
        };

        /**
         * On handleNewFriendshipRequestWebsocket event gets the pending requests
         * by calling getPendingRequests().
         */
        $scope.$on('handleNewFriendshipRequestWebsocket', function () {
            $scope.getPendingRequests();
        });

        /**
         * Accepts the request of the user with id of userId then refresh the pending requests 
         * and broadcasts handleAcceptFriend event.
         * 
         * @param {Integer} userId
         */
        $scope.acceptFriend = function (userId) {
            UserFriendshipHelperService.acceptFriend(userId).then(function (result) {
                $scope.getPendingRequests();
                $rootScope.$broadcast('handleAcceptFriend');
            });
        };

    }]);

