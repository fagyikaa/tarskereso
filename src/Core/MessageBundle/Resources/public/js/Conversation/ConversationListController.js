'use strict';

App.controller('ConversationListController', ['$rootScope', '$scope', '$http', '$q', function ($rootScope, $scope, $http, $q) {
        $scope.currentUserId;
        $scope.target;
        $scope.conversationList;
        $scope.errorMessage;
        $scope.deferred;
        $scope.loading;
        $scope.selectedConversationId;
        $scope.filtering = {};

        /**
         * Sets errorMessage to false, loading to true, currentUserId and calls getConversationList.
         * After the response arrived loading to false and resolves the promise with the partner's id
         * or rejects it if the target is last and the current user don't have any conversations. Also
         * sorts the conversationList accorrding to the alst message's createdAt time.
         * 
         * @param {Integer} currentUserId
         * @param {String} target
         */
        $scope.init = function (currentUserId, target) {
            $scope.errorMessage = false;
            $scope.loading = true;
            $scope.conversationList = [];
            $scope.currentUserId = currentUserId;
            $scope.deferred = $q.defer();
            $scope.getConversationList().then(function () {
                $scope.loading = false;
                if ('last' === target) {
                    if ($scope.conversationList.length === 0) {
                        $scope.deferred.reject();
                    } else {
                        $scope.conversationList.sort(function (a, b) {
                            return new Date(b.conversation.lastMessage.createdAt) - new Date(a.conversation.lastMessage.createdAt);
                        });
                        $scope.selectedConversationId = $scope.conversationList[0].conversation.id;
                        $scope.deferred.resolve($scope.conversationList[0].partner.id);
                    }
                } else {
                    angular.forEach($scope.conversationList, function (object, key) {
                        if (object.partner.id === parseInt(target)) {
                            $scope.selectedConversationId = object.conversation.id;
                        }
                    });
                    $scope.deferred.resolve(target);
                }
            });
        };

        /**
         * Returns how much time has elapsed since createdAt.
         * 
         * @param {String} createdAt
         */
        $scope.formatCreatedAt = function (createdAt) {
            return moment(createdAt).fromNow();
        };

        /**
         * Iterates over the conversationList and extend it with profilePictureSrc property
         * and saves to it the src of the profile picture of the partner.
         */
        $scope.initProfilePictureSrcs = function () {
            angular.forEach($scope.conversationList, function (object, key) {
                object.partner['profilePictureSrc'] = Routing.generate('api_core_user_serve_profile_image_thumbnail', {userId: object.partner.id, size: 50, _locale: Translator.locale, _ts: new Date().getTime()});
            });
        };

        /**
         * Saves to the conversationList's profilePictureSrc property the src of 
         * the profile picture of the partner in the given conversation. This function
         * is used for refresh the profile picture.
         * 
         * @param {Object} conversation
         */
        $scope.addProfilePictureSrcToConversation = function (conversation) {
            conversation.partner['profilePictureSrc'] = Routing.generate('api_core_user_serve_profile_image_thumbnail', {userId: conversation.partner.id, size: 50, _locale: Translator.locale, _ts: new Date().getTime()});
        };

        /**
         * Broadcasts handleOpenConversation with the given conversation's partner's id
         * and set selectedConversationId to the conversation's id thus displays the conversation.
         * 
         * @param {Object} conversation
         */
        $scope.openConversation = function (conversation) {
            $rootScope.$broadcast('handleOpenConversation', {partnerId: conversation.partner.id});
            $scope.selectedConversationId = conversation.conversation.id;
        };

        /**
         * Fetches from the server the conversation list of the user and calls initProfilePictureSrcs.
         * 
         */
        $scope.getConversationList = function () {
            $scope.errorMessage = false;
            return $http.get(Routing.generate('api_core_message_get_conversation_list', {_locale: Translator.locale, userId: $scope.currentUserId}))
                    .then(function (response) {
                        $scope.conversationList = response.data;
                        $scope.initProfilePictureSrcs();
                    }, function (response) {
                        $scope.errorMessage = response.data;
                    });
        };

        /**
         * Refreshes the list view of the conversation with the id of conversationId.
         * Iterates over the list and if the conversation is already in the list then updates
         * that, pushes to the list otherwise.
         * 
         * @param {Integer} conversationId
         */
        $scope.refreshListOfConversation = function (conversationId) {
            $http.get(Routing.generate('api_core_message_get_conversation_for_list', {_locale: Translator.locale, currentUserId: $scope.currentUserId, conversationId: conversationId}))
                    .then(function (response) {
                        var conversation = response.data;
                        $scope.addProfilePictureSrcToConversation(conversation);

                        var found = false;
                        angular.forEach($scope.conversationList, function (object, key) {
                            if (object.conversation.id === conversation.conversation.id) {
                                found = true;
                                $scope.conversationList[key].conversation.lastMessage = conversation.conversation.lastMessage;

                                if ($scope.selectedConversationId !== conversation.conversation.id) {
                                    $scope.conversationList[key].unreadMessagesCount = conversation.unreadMessagesCount;
                                }
                            }
                        });

                        if (false === found) {
                            $scope.conversationList.push(conversation);
                        }
                    }, function (response) {
                    });
        };

        /**
         * On handleNewMessage event refreshes the corresponding conversation's lsit view.
         */
        $scope.$on('handleNewMessage', function (eventArgs, conversationIdObject) {
            $scope.refreshListOfConversation(conversationIdObject.conversationId);
        });

        /**
         * On handleConversationControllerLoaded broadcasts handleConversationListPromise
         * event with a promise object which will resolve the partner's id on initialization.
         * It's needed because the user can get here by the menu and also by clicking on the 
         * unread messages notification.
         */
        $scope.$on('handleConversationControllerLoaded', function () {
            $rootScope.$broadcast('handleConversationListPromise', {promise: $scope.deferred.promise});
        });

        /**
         * On handleConversationSeen sets the corresponding conversation's unreadMessagesCount
         * to 0.
         */
        $scope.$on('handleConversationSeen', function (eventArgs, conversationIdObject) {
            angular.forEach($scope.conversationList, function (object, key) {
                if (object.conversation.id === conversationIdObject.conversationId) {
                    object.unreadMessagesCount = 0;
                }
            });
        });

        /**
         * On handleNewMessageWebsocket refreshes the message's related conversation's
         * list view.
         */
        $scope.$on('handleNewMessageWebsocket', function (eventArgs, messageObject) {
            if ($scope.currentUserId === $rootScope.userId) {
                $scope.refreshListOfConversation(messageObject.message.conversationId);
            }
        });

    }]);

