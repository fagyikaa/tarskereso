'use strict';
App.controller('ConversationController', ['$rootScope', '$scope', '$http', function ($rootScope, $scope, $http) {
        $scope.currentUserId;
        $scope.conversation;
        $scope.showNoConversationMessage;
        $scope.loading;
        $scope.errorMessage;
        $scope.postErrorMessage;
        $scope.data = {};
        $scope.otherUserId;
        $scope.currentUserProfilePictureSrc;
        $scope.otherUserProfilePictureSrc;
        $scope.offset;
        $scope.length;
        $scope.messagesCount;
        $scope.websocketMessagesCounter;
        $scope.messageUnderSending;

        /**
         * Sets errorMessage to false, loading to true, showNoConversationMessage to false
         * and the currentUserid then broadcasts handleConversationControllerLoaded event.
         * 
         * @param {int} currentUserId
         */
        $scope.init = function (currentUserId) {
            $scope.messageUnderSending = false;
            $scope.errorMessage = false;
            $scope.postErrorMessage = false;
            $scope.loading = true;
            $scope.showNoConversationMessage = false;
            $scope.currentUserId = currentUserId;
            $scope.offset = 0;
            $scope.length = 10;
            $scope.websocketMessagesCounter = 0;
            $rootScope.$broadcast('handleConversationControllerLoaded');
        };

        /**
         * Gets from the server the next slice of messages of this conversation
         */
        $scope.showMore = function () {
            $scope.errorMessage = false;
            $scope.offset++;

            $http.get(Routing.generate('api_core_message_get_conversation', {_locale: Translator.locale, currentUserId: $scope.currentUserId, otherUserId: $scope.otherUserId, offset: $scope.offset, length: $scope.length}))
                    .then(function (response) {
                        $scope.mergeMessagesIntoConversation(response.data.conversation.messages);
                        $scope.messagesCount = response.data.conversation.tempMessagesCount;
                    }, function (response) {
                        $scope.errorMessage = response.data;
                    });
        };

        /**
         * Merges messages into conversation's messages bearing in mind the order
         * 
         * @param {Array} messages
         */
        $scope.mergeMessagesIntoConversation = function (messages) {
            var union = messages.concat($scope.conversation.conversation.messages);

            for (var i = 0; i < union.length; i++) {
                for (var j = i + 1; j < union.length; j++) {
                    if (union[i].id === union[j].id) {
                        union.splice(j, 1);
                        j--;
                    }
                }
            }

            union.sort(function (a, b) {
                return a.id - b.id;
            });

            $scope.conversation.conversation.messages = union;
        };

        /**
         * If a new message arrives through websocket and it related to this conversation
         * then merges into the conversation and set seenAt on the backend. Also adjust the
         * required variables to work the showMore function properly. 
         */
        $scope.$on('handleNewMessageWebsocket', function (eventArgs, messageObject) {
            var newMessage = messageObject.message;
            if (newMessage.conversationId === $scope.conversation.conversation.id) {
                $scope.mergeMessagesIntoConversation([newMessage]);
                if ($rootScope.userId === $scope.currentUserId) {
                    $scope.setConversationMessagesSeenAt();
                }

                $scope.websocketMessagesCounter++;
                if (0 === $scope.websocketMessagesCounter % $scope.length) {
                    $scope.websocketMessagesCounter = 0;
                    $scope.offset++;
                }
            }
        });

        /**
         * Sets the current user's and the partner's profile picture src, the other user's id,
         * errorMessage to false and fetches from the server the conversation between them.
         * If the current user is not an admin then calls setConversationMessagesSeenAt.
         * 
         * @param {int} userId
         */
        $scope.loadConversation = function (userId) {
            $scope.otherUserId = userId;
            $scope.errorMessage = false;
            $scope.currentUserProfilePictureSrc = Routing.generate('api_core_user_serve_profile_image_thumbnail', {userId: $scope.currentUserId, size: 50, _locale: Translator.locale, _ts: new Date().getTime()});
            $scope.otherUserProfilePictureSrc = Routing.generate('api_core_user_serve_profile_image_thumbnail', {userId: $scope.otherUserId, size: 50, _locale: Translator.locale, _ts: new Date().getTime()});
            $http.get(Routing.generate('api_core_message_get_conversation', {_locale: Translator.locale, currentUserId: $scope.currentUserId, otherUserId: userId, offset: $scope.offset, length: $scope.length}))
                    .then(function (response) {
                        $scope.conversation = response.data;
                        $scope.messagesCount = response.data.conversation.tempMessagesCount;
                        $scope.loading = false;
                        if ($rootScope.userId === $scope.currentUserId) {
                            $scope.setConversationMessagesSeenAt();
                        }
                    }, function (response) {
                        $scope.errorMessage = response.data;
                        $scope.loading = false;
                    });
        };

        /**
         * Posts request to the server to set the recieverSeenAt property of the current 
         * conversation's messages which are unseen by the current user and broadcasts 
         * handleConversationSeen event with the conversation's id.
         */
        $scope.setConversationMessagesSeenAt = function () {
            $http.post(Routing.generate('api_core_message_set_conversation_messages_seen_at', {_locale: Translator.locale}), {currentUserId: $scope.currentUserId, otherUserId: $scope.otherUserId})
                    .then(function (response) {
                        $rootScope.$broadcast('handleConversationSeen', {conversationId: $scope.conversation.conversation.id});
                    }, function (response) {
                    });
        };

        /**
         * If the message's author is the current user then returns true, false otherwise.
         * 
         * @param {Object} message
         * @returns {Boolean}
         */
        $scope.isOwn = function (message) {
            return message.authorId === $scope.currentUserId;
        };

        /**
         * Returns the src of the message's author's profile picture.
         * 
         * @param {Object} message
         * @returns {String}
         */
        $scope.getProfilePictureSrc = function (message) {
            if ($scope.isOwn(message)) {
                return $scope.currentUserProfilePictureSrc;
            } else {
                return $scope.otherUserProfilePictureSrc;
            }
        };

        /**
         * Returns the formatted relative time from now.
         * 
         * @param {String} createdAt
         * @returns {String}
         */
        $scope.formatCreatedAt = function (createdAt) {
            return moment(createdAt).fromNow();
        };

        /**
         * Posts a message to the server for the current conversation. In case of error
         * errorMessage shown, in case of success the conversation is extended with the new message,
         * the form is reseted and handleNewMessage event occurs with the conversation's id.
         */
        $scope.sendMessage = function () {
            $scope.postErrorMessage = false;
            $scope.messageUnderSending = true;

            var data = {
                message: $scope.data.message,
                userId: $scope.otherUserId
            };

            $http.post(Routing.generate('api_core_message_post_message', {_locale: Translator.locale}), data)
                    .then(function (response) {
                        $scope.messageUnderSending = false;
                        if (false === $scope.conversation.conversation.hasOwnProperty('id')) {
                            $scope.conversation.conversation.id = response.data.conversation.id;
                        }
                        $scope.conversation.conversation.messages.push(response.data.message);

                        $rootScope.$broadcast('handleNewMessage', {conversationId: $scope.conversation.conversation.id});
                        $scope.data.message = '';
                        $scope.ctrl.message_form.$setPristine();
                    }, function (response) {
                        $scope.messageUnderSending = false;
                        if (response.status == 400) {
                            var result = response.data;

                            if (angular.isDefined(result) && result.hasOwnProperty('data') && result.data.constructor === Array && angular.isDefined(result.data[0])) {
                                $scope.postErrorMessage = result.data[0];
                            } else if (angular.isDefined(result) && result.hasOwnProperty('message')) {
                                $scope.postErrorMessage = result.message;
                            }
                        }
                    });
        };

        /**
         * On handleConversationListPromise event handles the promise in promiseObject which is
         * rejected if the target was 'last' but the user doesn't have any conversations yet and
         * resolved with the id of the partner user otherwise.
         */
        $scope.$on('handleConversationListPromise', function (eventArgs, promiseObject) {
            promiseObject.promise.then(function (target) {
                $scope.loadConversation(parseInt(target));
            }, function (reason) {
                $scope.loading = false;
                $scope.showNoConversationMessage = true;
            });
        });

        /**
         * On handleOpenConversation event calls loadConversation with the partner's id
         * which fetches the conversation from the server.
         */
        $scope.$on('handleOpenConversation', function (eventArgs, partnerIdObject) {
            $scope.loadConversation(parseInt(partnerIdObject.partnerId));
        });

    }]);

