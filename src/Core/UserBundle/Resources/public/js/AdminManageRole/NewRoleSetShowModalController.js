'use strict';

App.controller('NewRoleSetShowModalController', ['$scope', '$http', '$uibModalInstance', 'AdminManageRoleHelperService', function ($scope, $http, $uibModalInstance, AdminManageRoleHelperService) {
        $scope.roleSets = {};
        $scope.isChildFormInvalid;//Initialised in the child controller

        /**
         * Gets the roles and their translations of the RoleSets with every subroles too and collects only the roles to an associative array.
         * The array will looks like: (RoleSetName => (ROLE1, ROLE2, ...), ...)
         */
        $scope.init = function () {
            $http.get(Routing.generate('admin_api_core_user_get_undeleted_detailed_role_sets', {_locale: Translator.locale}))
                    .then(function (response) {
                        angular.forEach(response.data, function (rolesWithTranslation, roleSetName) {
                            $scope.roleSets[roleSetName] = new Array();
                            angular.forEach(rolesWithTranslation, function (role, translation) {
                                $scope.roleSets[roleSetName].push(role);
                            });
                        });
                    }, function (response) {
                    });
        };

        /**
         * Checks whether the selectedRoles array contains the same roles (order doesnt matter) as any of the persisted RoleSets.
         * During the checking not only the actual contained roles of the RoleSets are examined, but every subroles of the role chains as well.
         * 
         * @param {Array} selectedRoles
         * @returns {Boolean}
         */
        $scope.isRoleSetUnique = function (selectedRoles) {
            return AdminManageRoleHelperService.isRoleSetUnique($scope.roleSets, selectedRoles); 
        };

        /**
         * Checks if the name is unique among the persisted RoleSets.
         * 
         * @param {String} roleSetName
         * @returns {Boolean}
         */
        $scope.isRoleSetNameUnique = function (roleSetName) {
            return !$scope.roleSets.hasOwnProperty(roleSetName);
        };

        /**
         * Closes this modal instance
         */
        $scope.close = function () {
            $uibModalInstance.dismiss('close');
        };

        /**
         * Broadcasts handleNewRoleSetSubmit event which causes the submit of new roleset form.
         */
        $scope.ok = function () {
            $scope.$broadcast('handleNewRoleSetSubmit');
        };

    }]);


