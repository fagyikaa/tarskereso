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

angular.module('App').config(function (ivhTreeviewOptionsProvider) {
    ivhTreeviewOptionsProvider.set({
        twistieCollapsedTpl: '<span class="fa fa-plus font-blue"></span>',
        twistieExpandedTpl: '<span class="fa fa-minus font-green"></span>',
        twistieLeafTpl: '<span class="fa fa-map-marker font-purple"></span>'
    });
});

App.controller('EditRoleController', ['$scope', '$http', '$stateParams', 'ivhTreeviewBfs', 'AdminManageRoleHelperService', function ($scope, $http, $stateParams, ivhTreeviewBfs, AdminManageRoleHelperService) {
    $scope.userId = $stateParams.userId;
    $scope.roleTree = [{}];
    $scope.selectedRoles = [];
    $scope.selectedRolesLabel = [];
    $scope.roleSets = [];
    $scope.roleTreeLoading = true;
    $scope.isSelectedRolesPristine = true;
    $scope.superAdminWarning = false;
    $scope.superAdminNode = {};
    $scope.selectedRoleSets = {};
    $scope.showSuccessMessage = false;

    /**
     * Gets the full role tree with the corresponding nodes checked, and the roleSets with translations. Fills up the selectedRoles 
     * and selectedRolesLabel array. When finished hides the loading message of the tree.
     */
    $scope.init = function () {
        $http.get(Routing.generate('admin_api_core_user_get_role_tree_and_sets_for_user', {userId: $scope.userId, _locale: Translator.locale}))
                .then(function (response) {
                    $scope.roleTree = response.data.roleTree;
                    //Fill selectedRoles and selectedRolesLabel arrays and set to selected the children of the selected nodes
                    ivhTreeviewBfs($scope.roleTree, function (node, parents) {
                        if (node.selected && $scope.selectedRoles.indexOf(node.value) === -1) {
                            ivhTreeviewBfs(node, function (childNode, parents) {
                                $scope.updateSelectedRoles(childNode, true);
                            });
                        }
                        if (node.value === 'ROLE_SUPER_ADMIN') {
                            $scope.superAdminNode = node;
                        }
                    });
                    $scope.updateView();
                    $scope.roleSets = response.data.roleSets;
                    $scope.roleTreeLoading = false;
                }, function (response) {
                });
    };

    /**
     * Triggers every time a checkbox is checked/unchecked. If a checkbox is checked then opens the actual branch of the tree and pushes
     * the selected role and it's children to the selectedRoles array, and their labels to the selectedRolesLabel array.
     * If a checkbox is unchecked then removes the role and it's children and their labels from the selectedRoles 
     * and selectedRolesLabel array. Also sets the isSelectedRolesPristine variable to false.
     * In the end calls updateView method to make changes on the whole view if necessary. 
     * 
     * @param {Node} ivhNode
     * @param {Boolean} ivhIsSelected
     * @param {ivhTree} ivhTree
     */
    $scope.treeChanged = function (ivhNode, ivhIsSelected, ivhTree) {
        $scope.isSelectedRolesPristine = false;
        if (ivhIsSelected) {
            ivhTreeviewBfs(ivhNode, function (node, parents) {
                node.__ivhTreeviewExpanded = true;
                if (node.value !== 'ROLE_SUPER_ADMIN') {
                    $scope.updateSelectedRoles(node, true);
                }
            });
        } else {
            $scope.updateSelectedRoles(ivhNode, false);
            ivhTreeviewBfs($scope.roleTree, function (node, parents) {
                if (node.value === ivhNode.value) {
                    angular.forEach(parents, function (parentNode, key) {
                        $scope.updateSelectedRoles(parentNode, false);
                    });
                }
            });
        }
        $scope.updateView();
    };

    /**
     * Traverse the tree and checks/unchecks every appearance of the roles according to selected roles. 
     * Also checks if every node selected (or except ROLE_SUPER_ADMIN) and set showable the warning message and checks the root node.
     * If the tree is pristine also opens the branches where checked nodes are. Also updates the checkboxes of the roleSets thus if every role
     * of a roleSet is selected in the tree the checkbox of the roleSet will be checked as well.
     */
    $scope.updateView = function () {
        var everyNodeSelected = true;
        ivhTreeviewBfs($scope.roleTree, function (node, parents) {
            if ($scope.selectedRoles.indexOf(node.value) > -1) {
                node.selected = true;
                if ($scope.isSelectedRolesPristine) {
                    node.__ivhTreeviewExpanded = true;
                    angular.forEach(parents, function (parentNode, key) {
                        parentNode.__ivhTreeviewExpanded = true;
                    });
                }
            } else if (node.value !== 'ROLE_SUPER_ADMIN') {
                everyNodeSelected = false;
                node.selected = false;
            }
        });
        if (everyNodeSelected) {
            $scope.superAdminWarning = true;
            $scope.superAdminNode.selected = true;
        } else {
            $scope.superAdminWarning = false;
            $scope.superAdminNode.selected = false;
        }
        angular.forEach($scope.roleSets, function (roles, roleSet) {
            $scope.checkRoleSetWhichIsSelected(roleSet, roles);
        });
    };

    /**
     * Fires every time one of the roleSet's checkbox is touched. It removes/pushes the roles and it's children from the
     * selectedRoles and selectedRolesLabel array according to the touch is check or uncheck.
     * 
     * @param {String} roleSet
     */
    $scope.selectedRoleSetsChanged = function (roleSet) {
        $scope.isSelectedRolesPristine = false;
        angular.forEach($scope.roleSets[roleSet], function (role, translation) {
            $scope.updateSelectedRolesAccordingToTheHierarchy(role, $scope.selectedRoleSets[roleSet]);
        });
        $scope.updateView();
    };

    /**
     * Traverse the tree and if isChecked is true then pushes the value and translation of the given role and also it's children according to the hierarchy.
     * If isChecked is false then removes the given role and it's parents.
     * 
     * @param {String} role
     * @param {Boolean} isChecked
     */
    $scope.updateSelectedRolesAccordingToTheHierarchy = function (role, isChecked) {
        ivhTreeviewBfs($scope.roleTree, function (node, parents) {
            if (node.value === role && isChecked && $scope.selectedRoles.indexOf(role) === -1) {
                ivhTreeviewBfs(node, function (childNode, parents) {
                    childNode.__ivhTreeviewExpanded = true;
                    $scope.updateSelectedRoles(childNode, true);
                });
                angular.forEach(parents, function (parentNode, key) {
                    parentNode.__ivhTreeviewExpanded = true;
                });
            } else if (node.value === role && !isChecked && $scope.selectedRoles.indexOf(role) !== -1) {
                $scope.updateSelectedRoles(node, false);
                angular.forEach(parents, function (parentNode, key) {
                    $scope.updateSelectedRoles(parentNode, false);
                });
            }
        });
    };

    /**
     * If push is true then and the node's value isn't already contained in the selectedRoles then pushes the node's value and label (role's translation) 
     * to the selectedRoles and selectedRolesLabel array. If push is false then removes them.
     * 
     * @param {Node} node
     * @param {Boolean} push
     */
    $scope.updateSelectedRoles = function (node, push) {
        if (push && $scope.selectedRoles.indexOf(node.value) === -1) {
            $scope.selectedRoles.push(node.value);
            $scope.selectedRolesLabel.push(node.label);
        } else if (!push) {
            var index = $scope.selectedRoles.indexOf(node.value);
            if (index > -1) {
                $scope.selectedRoles.splice(index, 1);
                $scope.selectedRolesLabel.splice(index, 1);
            }
        }
    };

    /**
     * Checks if every roles of the given roleSet are selected and if yes then checks the checkbox of the given roleSet.
     * 
     * @param {String} roleSet
     * @param {Object} roles
     */
    $scope.checkRoleSetWhichIsSelected = function (roleSet, rolesAndId) {
        var shouldBeChecked = true;
        angular.forEach(rolesAndId, function (role, translationOrId) {
            if ($scope.selectedRoles.indexOf(role) === -1 && translationOrId !== 'id') {
                shouldBeChecked = false;
            }
        });
        
        if (shouldBeChecked) {
            $scope.selectedRoleSets[roleSet] = true;
        } else {
            $scope.selectedRoleSets[roleSet] = false;
        }
    };

    /**
     * Save user's changed roles.
     */
    $scope.submit = function () {
        $scope.showSuccessMessage = false;
        $http.post(Routing.generate('admin_api_core_user_submit_edit_user_role', {userId: $scope.userId, _locale: Translator.locale}), $scope.selectedRoles)
                .then(function (response) {
                     $scope.showSuccessMessage = true;                    
                }, function (response) {
                });
    };

    /**
     * Opens a modal showing the detailed role tree of the given role set. Detailed means that it contains not only the actually persisted roles
     * but the subroles of these too.
     * 
     * @param {Object} rolesAndId
     */
    $scope.openShowDetailedRoleSetModal = function (rolesAndId) {
        var modalInstance = AdminManageRoleHelperService.showDetailedRoleSet(rolesAndId.id, true);
    };

}]);

