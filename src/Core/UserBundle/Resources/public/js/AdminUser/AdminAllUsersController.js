'use strict';

App.controller('AdminAllUsersController', ['$scope', '$compile', 'DTColumnBuilder', 'DTOptionsBuilder', '$filter', function ($scope, $compile, DTColumnBuilder, DTOptionsBuilder, $filter) {
        $scope.users = [];
        
        $scope.dtColumns = [
            DTColumnBuilder.newColumn('id').withTitle(Translator.trans('all_users.data_tables.id', {}, 'admin_users')),
            DTColumnBuilder.newColumn('username').withTitle(Translator.trans('all_users.data_tables.username', {}, 'admin_users')).renderWith(getNameToProfileLinkHtml),
            DTColumnBuilder.newColumn('enabled').withTitle(Translator.trans('all_users.data_tables.enabled', {}, 'admin_users')).renderWith(replaceBoolWithIconHtml),
            DTColumnBuilder.newColumn('createdAt').withTitle(Translator.trans('all_users.data_tables.created_at', {}, 'admin_users')).renderWith(dateToShortDateHtml),
            DTColumnBuilder.newColumn('isAdmin').withTitle(Translator.trans('all_users.data_tables.is_admin', {}, 'admin_users')).renderWith(replaceBoolWithIconHtml)
        ];
        $scope.dtOptions = DTOptionsBuilder
                .newOptions()
                //Order by createdAt
                .withOption('order', [3, 'desc'])
                .withOption('ajax', {
                    url: Routing.generate('admin_api_core_user_all_users_data_tables', {_locale: Translator.locale}),
                    type: 'POST'
                })
                .withDataProp('data')
                .withOption('processing', true)
                .withOption('serverSide', true)
                .withOption('createdRow', recompileRow)
                .withBootstrap();

        /**
         * If data is true then returns green fa-check icon, if false then red fa-ban icon.
         * 
         * @param {String} data
         * @param {String} type
         * @param {Object} full
         * @param {Object} meta
         * @returns {String}
         */
        function replaceBoolWithIconHtml(data, type, full, meta) {
            if (true === data) {
                return '<span><i class="fa fa-check font-green"></i></span>';
            } else {
                return '<span><i class="fa fa-ban font-red"></i></span>';
            }
        }

        /**
         * Converts the long date format to short date format.
         * 
         * @param {String} data
         * @param {String} type
         * @param {Object} full
         * @param {Object} meta
         * @returns {String}
         */
        function dateToShortDateHtml(data, type, full, meta) {
            return $filter('date')(data, 'short');
        }

        /**
         * Returns an html profile link that contains the user's username and profile picture
         * 
         * @param {String} data
         * @param {String} type
         * @param {Object} full
         * @param {Object} meta
         * @returns {String}
         */
        function getNameToProfileLinkHtml(data, type, full, meta) {
            return '<a class="link-no-decor" ui-sref="profile.introduction({ userId: ' + full.id + ' })"> \
                          <div> \
                              <img class="img-circle" src="' + $scope.getUserProfileImageSrc(full.id) + '" alt=""> \
                          ' + data + '\
                        </div> \
                      </a>';
        }

        /**
         * Returns the src of the given user's profile picture
         * 
         * @param {Integer} userId
         * @returns {String}
         */
        $scope.getUserProfileImageSrc = function (userId) {
            return Routing.generate('api_core_user_serve_profile_image_thumbnail', {userId: userId, size: 40, _locale: Translator.locale, _ts: new Date().getTime()});
        };

        /**
         * Compiles the given row so we can bind Angular directive to the DT
         * 
         * @param {Object} row
         * @param {String} data
         * @param {Integer} dataIndex
         */
        function recompileRow(row, data, dataIndex) {
            $compile(angular.element(row).contents())($scope);
        }

    }]);
