'use strict';

App.controller('EditRoleSetShowModalController', ['$scope', '$http', '$uibModalInstance', '$q', 'roleSet', 'AdminManageRoleHelperService', function ($scope, $http, $uibModalInstance, $q, roleSet, AdminManageRoleHelperService) {
        $scope.roleSets = {};
        $scope.roleTranslations = {};
        $scope.roleSet = roleSet;
        $scope.isChildFormInvalid;//Initialised in the child controller
        $scope.roleSetPromise = $q.defer();

        /**
         * Gets the roles and their translations of the RoleSets with every subroles too and collects the roles and their translations
         * into individual associative arrays.
         * The arrays will looks like: (RoleSetName => (ROLE1, ROLE2, ...), ...), (ROLE => TranslationOfRole, ...)
         * These arrays then are resolved to the roleSetPromise promise.
         */
        $scope.init = function () {
            $http.get(Routing.generate('admin_api_core_user_get_undeleted_detailed_role_sets', {_locale: Translator.locale}))
                    .then(function (response) {
                        angular.forEach(response.data, function (rolesWithTranslation, roleSetName) {
                            $scope.roleSets[roleSetName] = new Array();
                            angular.forEach(rolesWithTranslation, function (role, translation) {
                                $scope.roleSets[roleSetName].push(role);
                                $scope.roleTranslations[role] = translation;
                            });
                        });
                        $scope.roleSetPromise.resolve({
                            roleSets: $scope.roleSets[$scope.roleSet.name],
                            roleTranslations: $scope.roleTranslations
                        });
                        delete $scope.roleSets[$scope.roleSet.name];
                    }, function (response) {
                        $scope.roleSetPromise.reject(null);
                    });
        };

        /**
         * Returns the roleSetPromise which will be (if not yet) resolved with the detailed roles of the actual RoleSet and their translations.
         * 
         * @returns {$q@call;defer.promise}
         */
        $scope.getActualSelectedRolesPromise = function () {
            return $scope.roleSetPromise.promise;
        };

        /**
         * Checks whether the selectedRoles array contains the same roles (order doesnt matter) as any of the persisted RoleSets.
         * During the checking not only the actual contained roles of the RoleSets are examined, but every subroles of the role chains as well.
         * The checking is excluding the actual RoleSet.
         * 
         * @param {Array} selectedRoles
         * @returns {Boolean}
         */
        $scope.isRoleSetUniqueExceptItself = function (selectedRoles) {
            return AdminManageRoleHelperService.isRoleSetUnique($scope.roleSets, selectedRoles); 
        };

        /**
         * Checks if the name is unique among the persisted RoleSets except the actual one.
         * 
         * @param {String} roleSetName
         * @returns {Boolean}
         */
        $scope.isRoleSetNameUniqueExceptItself = function (roleSetName) {
            return !$scope.roleSets.hasOwnProperty(roleSetName);
        };

        /**
         * Closes this modal instance
         */
        $scope.close = function () {
            $uibModalInstance.dismiss('close');
        };

        /**
         * Broadcasts handleEditRoleSetSubmit event which causes the submit of edit roleset form.
         */
        $scope.ok = function () {
            $scope.$broadcast('handleEditRoleSetSubmit');
        };
    }]);


