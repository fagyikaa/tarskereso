'use strict';

App.config(['$stateProvider', '$urlRouterProvider', function ($stateProvider, $urlRouterProvider) {
        // Redirect to search after login
        $urlRouterProvider.when('', '/search');
        $stateProvider

                // Search
                .state('search', {
                    url: '/search',
                    //dont need stateparams here but url as simple property will be cached even with timestamp...
                    templateUrl: function ($stateParams) {
                        return Routing.generate('core_user_search', {_locale: Translator.locale, _ts: new Date().getTime()});
                    },
                    data: {pageTitle: Translator.trans('page_head.page_title', {}, 'search'), pageSubTitle: '', pageTitleTranslatorKey: 'page_head.page_title', pageTitleTranslationDomain: 'search'},
                    controller: 'SearchController',
                    resolve: {
                        deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                                return $ocLazyLoad.load({
                                    name: 'App',
                                    insertBefore: '#ng_load_plugins_before', // load the above css files before '#ng_load_plugins_before'
                                    files: [
                                        '/bundles/coreuser/css/Search/search.css',
                                        '/bundles/coreuser/js/Search/SearchController.js',
                                        '/bundles/coreuser/js/Search/SearchFormController.js',
                                        '/bundles/coreuser/js/Services/UserSearchHelperService.js'
                                    ]
                                });
                            }]
                    }
                })
                // Show friendships
                .state('friends', {
                    url: '/friends/{userId:int}/{tab:string}',
                    templateUrl: function ($stateParams) {
                        return Routing.generate('core_user_show_friends', {_locale: Translator.locale, userId: $stateParams.userId, _ts: new Date().getTime()});
                    },
                    data: {pageTitle: Translator.trans('page_head.page_title', {}, 'friendship'), pageSubTitle: '', pageTitleTranslatorKey: 'page_head.page_title', pageTitleTranslationDomain: 'friendship'},
                    controller: 'ShowFriendsController',
                    resolve: {
                        deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                                return $ocLazyLoad.load({
                                    name: 'App',
                                    insertBefore: '#ng_load_plugins_before', // load the above css files before '#ng_load_plugins_before'
                                    files: [
                                        '/bundles/coreuser/js/Friendship/ShowFriendsController.js',
                                        '/bundles/coreuser/js/Friendship/ShowFriendsAcceptedController.js',
                                        '/bundles/coreuser/js/Friendship/ShowFriendsPendingController.js',
                                        '/bundles/coreuser/js/Friendship/ShowFriendsBlockedController.js'
                                    ]
                                });
                            }]
                    }
                })
                // Show messages
                .state('messages', {
                    url: '/messages/{userId:int}/{target:string}',
                    templateUrl: function ($stateParams) {
                        return Routing.generate('core_message_show_conversations', {_locale: Translator.locale, userId: $stateParams.userId, _ts: new Date().getTime()});
                    },
                    data: {pageTitle: Translator.trans('page_head.page_title', {}, 'conversation'), pageSubTitle: '', pageTitleTranslatorKey: 'page_head.page_title', pageTitleTranslationDomain: 'conversation'},
                    controller: 'ShowConversationsController',
                    resolve: {
                        deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                                return $ocLazyLoad.load({
                                    name: 'App',
                                    insertBefore: '#ng_load_plugins_before', // load the above css files before '#ng_load_plugins_before'
                                    files: [
                                        '/bundles/coremessage/css/Conversation/conversation.css',
                                        '/bundles/coremessage/js/Conversation/ShowConversationsController.js',
                                        '/bundles/coremessage/js/Conversation/ConversationListController.js',
                                        '/bundles/coremessage/js/Conversation/ConversationController.js'
                                    ]
                                });
                            }]
                    }
                })
                // User Profile
                .state('profile', {
                    url: '/profile/{userId:int}',
                    templateUrl: function ($stateParams) {
                        return Routing.generate('core_user_profil_skeleton', {_locale: Translator.locale, userId: $stateParams.userId, _ts: new Date().getTime()});
                    },
                    data: {pageTitle: '', pageSubTitle: '', pageTitleTranslatorKey: '', pageTitleTranslationDomain: ''},
                    controller: 'UserProfileController',
                    resolve: {
                        deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                                return $ocLazyLoad.load([
                                    {
                                        name: 'angularFileUpload',
                                        files: [
                                            '/assets/vendor/angular-file-upload/dist/angular-file-upload.min.js'
                                        ]
                                    },
                                    {
                                        name: 'App',
                                        insertBefore: '#ng_load_plugins_before', // load the above css files before '#ng_load_plugins_before'
                                        files: [
                                            '/bundles/coreuser/css/Profile/profile.css',
                                            '/bundles/coreuser/css/Profile/main.css',
                                            '/bundles/coremedia/css/Image/upload.css',
                                            '/bundles/coreuser/js/Profile/UserProfileController.js',
                                            '/bundles/coreuser/js/Services/UserProfileHelperService.js',
                                            '/bundles/coremedia/js/Image/UploadImageModalController.js',
                                            '/bundles/coremedia/js/Image/ViewImageModalController.js',
                                            '/bundles/coremedia/js/Image/UploadImageFormController.js',
                                            '/bundles/coremedia/js/Services/MediaImageHelperService.js',
                                            '/bundles/coremedia/js/Directives/DisplayPreviewOfSelectedFileDirective.js'
                                        ]
                                    }
                                ]);
                            }]
                    }
                })
                // User Profile introduction
                .state('profile.introduction', {
                    url: '/introduction',
                    controller: 'UserProfileIntroductionController',
                    templateUrl: function ($stateParams) {
                        return Routing.generate('core_user_profile_introduction', {_locale: Translator.locale, userId: $stateParams.userId, _ts: new Date().getTime()});
                    },
                    data: {pageTitle: Translator.trans('page_titles.introduction', {}, 'profile'), pageSubTitle: '', pageTitleTranslatorKey: 'page_titles.introduction', pageTitleTranslationDomain: 'profile'},
                    resolve: {
                        deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                                return $ocLazyLoad.load({
                                    name: 'App',
                                    insertBefore: '#ng_load_plugins_before', // load the above css files before '#ng_load_plugins_before'
                                    files: [
                                        '/bundles/coreuser/js/Profile/UserProfileIntroductionController.js'
                                    ]
                                });
                            }]
                    }
                })
                // User Profile ideal
                .state('profile.ideal', {
                    url: '/ideal',
                    controller: 'UserProfileIdealController',
                    templateUrl: function ($stateParams) {
                        return Routing.generate('core_user_profile_ideal', {_locale: Translator.locale, userId: $stateParams.userId, _ts: new Date().getTime()});
                    },
                    data: {pageTitle: Translator.trans('page_titles.ideal', {}, 'profile'), pageSubTitle: '', pageTitleTranslatorKey: 'page_titles.ideal', pageTitleTranslationDomain: 'profile'},
                    resolve: {
                        deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                                return $ocLazyLoad.load({
                                    name: 'App',
                                    insertBefore: '#ng_load_plugins_before', // load the above css files before '#ng_load_plugins_before'
                                    files: [
                                        '/bundles/coreuser/js/Profile/UserProfileIdealController.js'
                                    ]
                                });
                            }]
                    }
                })
                // User Profile gallery
                .state('profile.gallery', {
                    url: '/gallery',
                    controller: 'UserProfileGalleryController',
                    templateUrl: function ($stateParams) {
                        return Routing.generate('core_user_profile_gallery', {_locale: Translator.locale, userId: $stateParams.userId, _ts: new Date().getTime()});
                    },
                    data: {pageTitle: Translator.trans('page_titles.gallery.main', {}, 'profile'), pageSubTitle: '', pageTitleTranslatorKey: 'page_titles.gallery.main', pageTitleTranslationDomain: 'profile'},
                    resolve: {
                        deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                                return $ocLazyLoad.load([{
                                        name: 'App',
                                        insertBefore: '#ng_load_plugins_before', // load the above css files before '#ng_load_plugins_before'
                                        files: [
                                            '/bundles/coreuser/js/Profile/UserProfileGalleryController.js',
                                            '/bundles/coreuser/js/Profile/UserProfileGalleryPublicController.js',
                                            '/bundles/coreuser/js/Profile/UserProfileGalleryPrivateController.js'
                                        ]
                                    }]);
                            }]
                    }
                })
                // User Profile settings
                .state('profile.settings', {
                    url: '/settings',
                    controller: 'UserProfileSettingsController',
                    templateUrl: function ($stateParams) {
                        return Routing.generate('core_user_profile_settings', {_locale: Translator.locale, userId: $stateParams.userId, _ts: new Date().getTime()});
                    },
                    data: {pageTitle: Translator.trans('page_titles.settings', {}, 'profile'), pageSubTitle: '', pageTitleTranslatorKey: 'page_titles.settings', pageTitleTranslationDomain: 'profile'},
                    resolve: {
                        deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                                return $ocLazyLoad.load({
                                    name: 'App',
                                    insertBefore: '#ng_load_plugins_before', // load the above css files before '#ng_load_plugins_before'
                                    files: [
                                        '/bundles/coreuser/js/Profile/UserProfileSettingsController.js',
                                        '/bundles/coreuser/js/Profile/UserProfileSettingsChangePasswordFormController.js',
                                        '/bundles/coreuser/js/Directives/PasswordVerifyDirective.js',
                                        '/bundles/corecommon/js/Services/CommonHelperService.js'
                                    ]
                                });
                            }]
                    }
                })
                //Edit user roles
                .state('profile.editRole', {
                    url: '/edit/role',
                    controller: 'EditRoleController',
                    templateUrl: Routing.generate('admin_core_user_show_edit_role', {_locale: Translator.locale}),
                    data: {pageTitle: Translator.trans('page_titles.edit_role', {}, 'profile'), pageSubTitle: '', pageTitleTranslatorKey: 'page_titles.edit_role', pageTitleTranslationDomain: 'profile'},
                    resolve: {
                        deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                                return $ocLazyLoad.load([
                                    {
                                        name: 'ivh.treeview',
                                        files: [
                                            '/assets/vendor/angular-ivh-treeview/dist/ivh-treeview-theme-basic.css',
                                            '/assets/vendor/angular-ivh-treeview/dist/ivh-treeview.css',
                                            '/assets/vendor/angular-ivh-treeview/dist/ivh-treeview.js'
                                        ]
                                    }
                                ]).then(
                                        function () {
                                            return $ocLazyLoad.load([
                                                {
                                                    name: 'App',
                                                    insertBefore: '#ng_load_plugins_before', // load the above css files before '#ng_load_plugins_before'
                                                    files: [
                                                        '/bundles/coreuser/css/AdminManageRole/role-set.css',
                                                        '/bundles/coreuser/js/AdminManageRole/DetailedRoleSetShowModalController.js',
                                                        '/bundles/coreuser/js/AdminManageRole/EditRoleController.js',
                                                        '/bundles/coreuser/js/Services/AdminManageRoleHelperService.js'
                                                    ]
                                                }
                                            ]);
                                        }
                                );
                            }]
                    }
                })
                // RoleSet editing
                .state('adminRoleSets', {
                    url: '/admin/show/role/sets',
                    controller: 'ShowRoleSetsController',
                    templateUrl: Routing.generate('admin_core_user_show_role_sets', {_locale: Translator.locale}),
                    data: {pageTitle: Translator.trans('role_set.page_title', {}, 'role'), pageSubTitle: Translator.trans('role_set.page_subtitle', {}, 'role'), pageTitleTranslatorKey: 'role_set.page_title', pageTitleTranslationDomain: 'role'},
                    resolve: {
                        deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                                return $ocLazyLoad.load([
                                    {
                                        name: 'ivh.treeview',
                                        files: [
                                            '/assets/vendor/angular-ivh-treeview/dist/ivh-treeview-theme-basic.css',
                                            '/assets/vendor/angular-ivh-treeview/dist/ivh-treeview.css',
                                            '/assets/vendor/angular-ivh-treeview/dist/ivh-treeview.js'
                                        ]
                                    }
                                ]).then(
                                        function () {
                                            return $ocLazyLoad.load([
                                                {
                                                    name: 'App',
                                                    insertBefore: '#ng_load_plugins_before', // load the above css files before '#ng_load_plugins_before'
                                                    files: [
                                                        '/bundles/coreuser/css/AdminManageRole/role-set.css',
                                                        '/bundles/coreuser/js/AdminManageRole/ShowRoleSetsController.js',
                                                        '/bundles/coreuser/js/AdminManageRole/NewRoleSetShowModalController.js',
                                                        '/bundles/coreuser/js/AdminManageRole/NewRoleSetFormController.js',
                                                        '/bundles/coreuser/js/AdminManageRole/DetailedRoleSetShowModalController.js',
                                                        '/bundles/coreuser/js/AdminManageRole/EditRoleSetShowModalController.js',
                                                        '/bundles/coreuser/js/AdminManageRole/EditRoleSetFormController.js',
                                                        '/bundles/coreuser/js/Services/AdminManageRoleHelperService.js',
                                                        '/bundles/corecommon/js/Services/CommonHelperService.js'
                                                    ]
                                                }
                                            ]);
                                        }
                                );
                            }]
                    }
                })
                //Admin active users
                .state('adminActiveUsers', {
                    url: '/admin/users/active',
                    data: {pageTitle: Translator.trans('all_users.page_title', {}, 'admin_users'), pageSubTitle: Translator.trans('all_users.page_subtitle', {}, 'admin_users'), pageTitleTranslatorKey: 'all_users.page_title', pageTitleTranslationDomain: 'admin_users'},
                    controller: 'AdminActiveUsersController',
                    templateUrl: Routing.generate('admin_core_user_active_users', {_locale: Translator.locale}),
                    resolve: {
                        deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                                return $ocLazyLoad.load([
                                    {
                                        name: 'App',
                                        insertBefore: '#ng_load_plugins_before', // load the above css files before '#ng_load_plugins_before'
                                        files: [
                                            '/bundles/coreuser/js/AdminUser/AdminActiveUsersController.js'
                                        ]
                                    }
                                ]);
                            }]
                    }
                })
                //Admin all users
                .state('adminAllUsers', {
                    url: '/admin/users/all',
                    data: {pageTitle: Translator.trans('active_users.page_title', {}, 'admin_users'), pageSubTitle: Translator.trans('active_users.page_subtitle', {}, 'admin_users'), pageTitleTranslatorKey: 'active_users.page_title', pageTitleTranslationDomain: 'admin_users'},
                    controller: 'AdminAllUsersController',
                    templateUrl: Routing.generate('admin_core_user_all_users', {_locale: Translator.locale}),
                    resolve: {
                        deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                                return $ocLazyLoad.load([
                                    {
                                        name: 'App',
                                        insertBefore: '#ng_load_plugins_before', // load the above css files before '#ng_load_plugins_before'
                                        files: [
                                            '/bundles/coreuser/js/AdminUser/AdminAllUsersController.js'
                                        ]
                                    }
                                ]);
                            }]
                    }
                });
    }]);
