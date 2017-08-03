'use strict';

var App = angular.module('App', [
    'IndexApp',
    'oc.lazyLoad',
    'ui.router',
    'xeditable',
    'ui.bootstrap',
    'dialogs.main',
    'dialogs.default-translations',
    'ngMessages',
    'vxWamp',
    'jkAngularRatingStars',
    'luegg.directives',
    'datatables',
    'datatables.bootstrap',
    'angular-loading-bar',
    'ui-notification'
]);

/*
 * Sets default header X-Requested-With attribute and RequestsErrorHandler to handle request errors.
 */
App.config(['$httpProvider', function ($httpProvider) {
        $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        $httpProvider.interceptors.push('RequestsErrorHandler');
    }]);

/**
 * Load the css files before a LINK element with this ID. Dynamic CSS files must be loaded between core and theme css files
 */
App.config(['$ocLazyLoadProvider', function ($ocLazyLoadProvider) {
        $ocLazyLoadProvider.config({
            cssFilesInsertBefore: 'ng_load_plugins_before'
        });
    }]);

/**
 * DataTables common rendering and options: use bootstrap, load translation and pagination type simple_numbers.
 */
App.run(function (DTDefaultOptions) {
    DTDefaultOptions.setBootstrapOptions({
        dom: "<'row'<'col-xs-12 col-sm-6'l><'col-xs-12 col-sm-6'f>r><'table-responsive't><'row'<'col-xs-12 col-sm-6'i><'col-xs-12 col-sm-6'p>>"
    });

    DTDefaultOptions.setLanguageSource("/bundles/corecommon/json/DataTables/languages/" + Translator.locale + ".json");
    DTDefaultOptions.setOption('pagingType', 'simple_numbers');
});

/**
 * Initializing websocket connection.
 */
App.config(function ($wampProvider) {
    if (_original_user_id > 0) {
        var userId = _original_user_id + ' impersonating';
    } else {
        var userId = _user_id;
    }

    $wampProvider.init({
        url: _websocket_url,
        realm: 'realm1',
        authid: userId,
        authmethods: ["wampcra"],
        initial_retry_delay: 10,
        onchallenge: function onchallenge(session, method, extra) {
            if (method === "wampcra") {
                return autobahn.auth_cra.sign(_user_hash, extra.challenge);
            } else {
                throw "Don't know how to authenticate using '" + method + "'";
            }
        }
    });
});

/**
 * Main controller
 */
App.controller('AppController', ['$scope', '$rootScope', '$http', '$uibModal', '$wamp', 'UserFriendshipHelperService', '$state', function ($scope, $rootScope, $http, $uibModal, $wamp, UserFriendshipHelperService, $state) {

        $rootScope.logoutPath = '';
        $rootScope.unseenFriendRequests;
        $rootScope.unseenConversationCount;
        $rootScope.isWSConnected = false;
        $rootScope.alreadySubscribed = false;

        /**
         * Initializes rootScope variables: unseenConversationCount,unseenFriendRequests, 
         * username, userId, logoutPath. Initializes profile picture src and gets the unseen
         * conversation count and friend requests.
         */
        $scope.appInit = function (username, id) {
            $rootScope.unseenConversationCount = 0;
            $rootScope.unseenFriendRequests = [];
            if (false === angular.isUndefined(username) && false === angular.isUndefined(id)) {
                $rootScope.username = username;
                $rootScope.userId = id;
            }
            $rootScope.logoutPath = Routing.generate('fos_user_security_logout', {_locale: Translator.locale});

            $scope.initProfileImageSrc();
            $rootScope.getUnseenFriendRequests();
            $rootScope.getUnseenConversationCount();
        };

        /**
         * Generates src for the current user's profile image thumbnail and saves it to
         * $rootScope.profileImage.
         */
        $scope.initProfileImageSrc = function () {
            $rootScope.profileImage = Routing.generate('api_core_user_serve_profile_image_thumbnail', {userId: $rootScope.userId, size: 43, _locale: Translator.locale, _ts: new Date().getTime()});
        };

        /**
         * Fetches from the server the unseen pending friend requests of the cuirrent user.
         */
        $rootScope.getUnseenFriendRequests = function () {
            UserFriendshipHelperService.getUnseenPendingRequests($rootScope.userId).then(function (response) {
                $rootScope.unseenFriendRequests = response.data;
            });
        };

        /**
         * Fetches from the server the current user's unseen conversations count (conversations
         * which contain messages which are unseen).
         */
        $rootScope.getUnseenConversationCount = function () {
            $http.get(Routing.generate('api_core_message_get_conversation_count_with_unseen_message'), {_locale: Translator.locale}).then(function (response) {
                $rootScope.unseenConversationCount = response.data;
            });
        };

        /**
         * If pending friends page is opened by the user refresh the unseen friend requests count by
         * fetching it from the server.
         */
        $scope.$on('handleUnseenPendingRequestsChecked', function () {
            $rootScope.getUnseenFriendRequests();
        });

        /**
         * If a conversation is opened by the user refresh the unseen conversation count by
         * fetching it from the server.
         */
        $scope.$on('handleConversationSeen', function () {
            $rootScope.getUnseenConversationCount();
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

        /*
         * When websocket connection opens set isWSConnected to true and subscribe to
         * message/new_message_frontend and user/new_friendship_request_frontend.
         */
        $scope.$on("$wamp.open", function (event, session) {
            $rootScope.isWSConnected = true;

            //If the connection closes then reopens then it would cause multiple registration of the subscription
            if (false === $rootScope.alreadySubscribed) {
                $wamp.subscribe('message/new_message_frontend', function (args) {
                    $rootScope.$broadcast('handleNewMessageWebsocket', {message: args[0]});

                    if ('messages' !== $state.current.name || ('messages' === $state.current.name && $state.params.userId !== $rootScope.userId)) {
                        $rootScope.getUnseenConversationCount();
                    }
                });
                
                $wamp.subscribe('user/new_friendship_request_frontend', function (args) {
                    $rootScope.$broadcast('handleNewFriendshipRequestWebsocket', {friendshipRequest: args[0]});
                    
                    if ('friends' !== $state.current.name || ('friends' === $state.current.name && 'pending' !== $state.params.tab)) {
                        $rootScope.getUnseenFriendRequests();
                    }
                });
                
                $rootScope.alreadySubscribed = true;
            }
        });

        /*
         * When websocket connection closes set isWSConnected to false.
         */
        $scope.$on("$wamp.close", function (event, data) {
            $rootScope.isWSConnected = false;
        });

        /*
         * Publish new state to the websocket server
         */
        $rootScope.$on('$stateChangeStart', function (event, toState, toParams, fromState, fromParams) {
            $wamp.publish('user/state_change', [toState.data.pageTitleTranslatorKey, toState.data.pageTitleTranslationDomain]);
        });


    }]);

/**
 * Run the app: set xedit's theme to bootstrap3, on state change error write details, 
 * open websocket connection.
 */
App.run(['$rootScope', '$state', 'editableOptions', '$wamp',
    function ($rootScope, $state, editableOptions, $wamp) {
        $rootScope.$state = $state; // state to be accessed from view
        editableOptions.theme = 'bs3'; // bootstrap3 theme. Can be also 'bs2', 'default'

        $rootScope.$on('$stateChangeError', function (event, toState, toParams, fromState, fromParams, error) {
            event.preventDefault();
            console.log('error:', error);
            console.log('toState:', toState);
            console.log('toParams:', toParams);
            console.log('fromState:', fromState);
            console.log('fromParams:', fromParams);
        });

        //start websocket connection
        $wamp.open();
    }
]);

/**
 * It's not a real directive rather some kind of config which handles functional links globally.
 * If the link contains ngClick directive or it's href attribute is empty then preventDefault.
 */
App.directive('a',
        function () {
            return {
                restrict: 'E',
                link: function (scope, elem, attrs) {
                    if (attrs.ngClick || attrs.href === '' || attrs.href === '#') {
                        elem.on('click', function (e) {
                            e.preventDefault();
                        });
                    }
                }
            };
        });

/**
 * Sets the dialog's translations.
 */
App.config(['$translateProvider', function ($translateProvider) {
        $translateProvider.translations('hu-HU', {
            DIALOGS_ERROR: Translator.trans('modal_service.dialogs_error'),
            DIALOGS_ERROR_MSG: Translator.trans('modal_service.dialogs_error_msg'),
            DIALOGS_CLOSE: Translator.trans('modal_service.dialogs_close'),
            DIALOGS_PLEASE_WAIT: Translator.trans('modal_service.dialogs_please_wait'),
            DIALOGS_PLEASE_WAIT_ELIPS: Translator.trans('modal_service.dialogs_please_wait_elips'),
            DIALOGS_PLEASE_WAIT_MSG: Translator.trans('modal_service.dialogs_please_Wait_msg'),
            DIALOGS_PERCENT_COMPLETE: Translator.trans('modal_service.dialogs_percent_complete'),
            DIALOGS_NOTIFICATION: Translator.trans('modal_service.dialogs_notification'),
            DIALOGS_NOTIFICATION_MSG: Translator.trans('modal_service.dialogs_notification_msg'),
            DIALOGS_CONFIRMATION: Translator.trans('modal_service.dialogs_confirmation'),
            DIALOGS_CONFIRMATION_MSG: Translator.trans('modal_service.dialogs_confirmation_msg'),
            DIALOGS_OK: Translator.trans('modal_service.dialogs_ok'),
            DIALOGS_YES: Translator.trans('modal_service.dialogs_yes'),
            DIALOGS_NO: Translator.trans('modal_service.dialogs_no')
        });
        $translateProvider.preferredLanguage(_translationLocale);
    }]);
