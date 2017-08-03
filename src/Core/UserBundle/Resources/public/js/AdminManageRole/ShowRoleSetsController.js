'use strict';

// Remove the built in  ivhTreeviewCheckboxHelper directive
App.config(function ($provide) {
    $provide.decorator('ivhTreeviewCheckboxHelperDirective', function ($delegate) {
        $delegate.shift();
        return $delegate;
    });
});

// Add custom checkbox directive
App.directive('ivhTreeviewCheckboxHelper', function () {
    return {
        scope: {
            node: '=ivhTreeviewCheckboxHelper'
        },
        require: '^ivhTreeview',
        link: function (scope, element, attrs, trvw) {
            scope.trvw = trvw;
        },
        template: '<input type="checkbox" ng-model="node.selected" ng-change="trvw.onCbChange(node, node.selected)"/>'
    };
});

App.config(function (ivhTreeviewOptionsProvider) {
    ivhTreeviewOptionsProvider.set({
        twistieCollapsedTpl: '<span class="fa fa-plus font-blue"></span>',
        twistieExpandedTpl: '<span class="fa fa-minus font-green"></span>',
        twistieLeafTpl: '<span class="fa fa-map-marker font-purple"></span>'
    });
});

App.controller('ShowRoleSetsController', ['$scope', '$http', 'DTOptionsBuilder', 'DTColumnBuilder', 'DTColumnDefBuilder', '$uibModal', 'CommonHelperService', 'AdminManageRoleHelperService', function ($scope, $http, DTOptionsBuilder, DTColumnBuilder, DTColumnDefBuilder, $uibModal, CommonHelperService, AdminManageRoleHelperService) {

        $scope.modalAnimationsEnabled = true;
        $scope.roleSets = {};
        $scope.limit = 5;

        /**
         * Triggers when a new RoleSet has been created and refresh the data of the DataTables.
         */
        $scope.$on('handleNewRoleSetCreated', function () {
            $scope.init();
        });

        /**
         * Gets the persisted RoleSets.
         */
        $scope.init = function () {
            $http.get(Routing.generate('admin_api_core_user_get_role_sets', {_locale: Translator.locale}))
                    .then(function (response) {
                        $scope.roleSets = response.data;

                        angular.forEach($scope.roleSets, function (value, key) {
                            $scope.roleSets[key].limit = $scope.limit;
                        });

                    }, function (response) {
                    });
        };

        //Set pagination type with display length and to order the table according to deletedAt column
        $scope.dtOptions = DTOptionsBuilder.newOptions()
                .withBootstrap().withOption('order', [2, 'asc']);


        $scope.dtOptions.initComplete = function () {
            $('select').select2({minimumResultsForSearch: -1});
        };
        //Can't sort by list of roles and functions(viev, edit, delete)
        $scope.dtColumnDefs = [
            DTColumnDefBuilder.newColumnDef(1).notSortable(),
            DTColumnDefBuilder.newColumnDef(3).notSortable()
        ];

        /**
         * Opens the new RoleSet creater modal.
         */
        $scope.openAddNewRoleSetModal = function () {
            var modalInstance = $uibModal.open({
                animation: $scope.modalAnimationsEnabled,
                templateUrl: Routing.generate('admin_core_user_show_new_role_set_modal', {_locale: Translator.locale, _ts: new Date().getTime()}),
                windowClass: 'app-modal-window',
                controller: 'NewRoleSetShowModalController',
                size: 'lg'
            });
        };

        /**
         * Opens a modal showing the detailed role tree of the given roleSet. Detailed means that it contains not only the actually persisted roles
         * but the subroles of these too.
         * 
         * @param {RoleSet} roleSet
         */
        $scope.showDetailedRoleSet = function (roleSet) {
            var modalInstance = AdminManageRoleHelperService.showDetailedRoleSet(roleSet.id, true);
        };

        /**
         * Shows up a confirmation dialog and removes the given roleSet from the server if the user confirms.
         * 
         * @param {RoleSet} roleSet
         */
        $scope.deleteRoleSet = function (roleSet) {
            var header = Translator.trans('role_set.delete_confirm_modal.header', {}, 'role');
            var msg = Translator.trans('role_set.delete_confirm_modal.message', {}, 'role');
            var dlg = CommonHelperService.confirmModal(header, msg);
            dlg.result.then(function (response) {
                $http.delete(Routing.generate('admin_api_core_user_remove_role_set', {roleSetId: roleSet.id, _locale: Translator.locale}))
                        .then(function (response) {
                            $scope.init();
                        }, function (response) {
                        });
            }, function (btn) {
            });

            return;
        };

        /**
         * Opens the RoleSet editing modal.
         * 
         * @param {RoleSet} roleSet
         */
        $scope.editRoleSetModal = function (roleSet) {
            var modalInstance = $uibModal.open({
                animation: $scope.modalAnimationsEnabled,
                templateUrl: Routing.generate('admin_core_user_show_edit_role_set_modal', {roleSetId: roleSet.id, _locale: Translator.locale, _ts: new Date().getTime()}),
                windowClass: 'app-modal-window',
                controller: 'EditRoleSetShowModalController',
                resolve: {
                    roleSet: function () {
                        return roleSet;
                    }
                },
                size: 'lg'
            });
        };
    }]);


