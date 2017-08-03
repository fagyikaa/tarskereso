'use strict';

App.factory('AdminManageRoleHelperService', ['$rootScope', 'ivhTreeviewBfs', '$http', '$uibModal', '$q', function ($rootScope, ivhTreeviewBfs, $http, $uibModal, $q) {
        var service = {};
        service.hasAnyFilterFunction;

        /**
         * Set the validity of the form. The form is valid if: 
         * -name is set and unique 
         * -at least 2 but not every roles are selected
         * -there are no role set with the same roles (isRoleSetUnique = true)
         */
        service.updateFormValidity = function (roleSetForm, name, isNameUnique, isRoleSetUnique, isEveryRoleSelected, selectedRolesCount) {
            roleSetForm.$setValidity('superAdmin', !isEveryRoleSelected);

            if (name && !isNameUnique) {
                roleSetForm['role_set[name]'].$setValidity('uniqueName', false);
            } else {
                roleSetForm['role_set[name]'].$setValidity('uniqueName', true);
            }

            if (!isRoleSetUnique) {
                roleSetForm['role_set[roles]'].$setValidity('roleSetUnique', false);
            } else {
                roleSetForm['role_set[roles]'].$setValidity('roleSetUnique', true);
            }

            if (selectedRolesCount <= 1) {
                roleSetForm['role_set[roles]'].$setValidity('rolesMin', false);
            } else {
                roleSetForm['role_set[roles]'].$setValidity('rolesMin', true);
            }
        };

        /**
         * If a checkbox is checked then set it's and it's children selected attribute to true, 
         * opens the actual branch of the tree and push the selected roles to the selectedRoles array, and their labels to the selectedRolesLabel array.
         * If a checkbox is unchecked then set it's and it's parents selected attributes to false and removes the roles and their labels from the selectedRoles 
         * and selectedRolesLabel array. 
         * 
         * @param {Boolean} ivhIsSelected
         * @param {Node} ivhNode          
         * @param {ivhTree} roleTree
         * @param {Array} selectedRoles
         * @param {Array} selectedRolesLabel
         */
        service.updateTreeAndSelectedRolesOnChange = function (ivhIsSelected, ivhNode, roleTree, selectedRoles, selectedRolesLabel) {
            if (ivhIsSelected) {
                ivhTreeviewBfs(ivhNode, function (node, parents) {
                    node.selected = true;
                    node.__ivhTreeviewExpanded = true;
                    if (selectedRoles.indexOf(node.value) === -1) {
                        selectedRoles.push(node.value);
                        selectedRolesLabel.push(node.label);
                    }
                });
            } else {
                ivhNode.selected = false;
                var index = selectedRoles.indexOf(ivhNode.value);
                if (index > -1) {
                    selectedRoles.splice(index, 1);
                    selectedRolesLabel.splice(index, 1);
                }
                ivhTreeviewBfs(roleTree, function (node, parents) {
                    if (node.value === ivhNode.value) {
                        angular.forEach(parents, function (parentNode, key) {
                            parentNode.selected = false;
                            var index = selectedRoles.indexOf(parentNode.value);
                            if (index > -1) {
                                selectedRoles.splice(index, 1);
                                selectedRolesLabel.splice(index, 1);
                            }
                        });
                    }
                });
            }
        };

        /**
         * Traverse the tree and checks/unchecks every appearance of the same roles according to selected roles. 
         * If isRoleTreePristine is passed and true then also opens the branches where checked nodes are.
         * 
         * @returns {Boolean} Whether every nodes are selected or not
         */
        service.traverseTreeAndUpdate = function (roleTree, selectedRoles, isRoleTreePristine = false) {
            var isEveryNodeSelected = true;
            ivhTreeviewBfs(roleTree, function (node, parents) {
                if (selectedRoles.indexOf(node.value) > -1) {
                    node.selected = true;
                    if (isRoleTreePristine) {
                        node.__ivhTreeviewExpanded = true;
                        angular.forEach(parents, function (parentNode, key) {
                            parentNode.__ivhTreeviewExpanded = true;
                        });
                    }
                } else {
                    isEveryNodeSelected = false;
                    node.selected = false;
                }
            });

            return isEveryNodeSelected;
        };

        /**
         * Checks whether the roleSet array contains the same roles (order doesnt matter) as any of the persisted RoleSets in baseRoleSets.
         * During the checking not only the actual contained roles of the RoleSets are examined, but every subroles of the role chains as well.
         * 
         * @param {Array} baseRoleSets
         * @param {Array} selectedRoles
         * @returns {Boolean}
         */
        service.isRoleSetUnique = function (baseRoleSets, roleSet) {
            for (var index in baseRoleSets) {
                if (baseRoleSets.hasOwnProperty(index) && (baseRoleSets[index].length === roleSet.length)) {
                    var containsTheSameRoles = true;
                    angular.forEach(baseRoleSets[index], function (role, arrayKey) {
                        if (!service.containsRole(role, roleSet)) {
                            containsTheSameRoles = false;
                        }
                    });
                    if (containsTheSameRoles) {
                        return false;
                    }
                }
            }
            return true;
        };

        /**
         * Checks whether the selectedRoles array contains the given role.
         * 
         * @param {String} role
         * @param {Array} rolesArray
         * @returns {Boolean}
         */
        service.containsRole = function (role, rolesArray) {
            for (var index in rolesArray) {
                if (rolesArray.hasOwnProperty(index) && rolesArray[index] === role) {
                    return true;
                }
            }
            return false;
        };

        /**
         * Opens a modal showing the detailed role tree of the given roleSet. Detailed means that it contains not only the actually persisted roles
         * but the subroles of these too.
         * 
         * @param {RoleSet} roleSet
         */
        service.showDetailedRoleSet = function (roleSetId, modalAnimation) {
            var deferred = $q.defer();

            $http.get(Routing.generate('admin_api_core_user_get_detailed_role_set_tree', {roleSetId: roleSetId, _locale: Translator.locale}))
                    .then(function (response) {
                        deferred.resolve(response.data);
                    }, function (response) {
                    });

            var modalInstance = $uibModal.open({
                animation: modalAnimation,
                templateUrl: Routing.generate('admin_core_user_show_detailed_role_set_modal', {_locale: Translator.locale, _ts: new Date().getTime()}),
                windowClass: 'app-modal-window',
                controller: 'DetailedRoleSetShowModalController',
                resolve: {
                    roleSetTree: function () {
                        return deferred.promise;
                    }
                },
                size: 'lg'
            });
            
            return modalInstance;
        };

        return service;
    }]);


