'use strict';

App.controller('EditRoleSetFormController', ['$rootScope', '$scope', '$http', 'AdminManageRoleHelperService', function ($rootScope, $scope, $http, AdminManageRoleHelperService) {

        $scope.data = {};
        $scope.errors = {};
        $scope.roleTree = [{}];
        $scope.selectedRoles = [];
        $scope.selectedRolesLabel = [];
        $scope.selectedRolesPromise = {};
        $scope.roleTreeLoading = true;
        $scope.isRoleTreePristine = true;
        $scope.superAdminWarning = false;
        $scope.roleSetId = null;

        /**
         * Gets the full role tree, then when the detailed role list of the actual RoleSet is available updates the role tree, the selectedRoles
         * and the selectedRolesLabel array. When finished hides the loading message of the tree.
         */
        $scope.init = function (roleSetId) {
            $scope.roleSetId = roleSetId;
            $http.get(Routing.generate('admin_api_core_user_get_full_role_tree', {_locale: Translator.locale}))
                    .then(function (response) {
                        $scope.roleTree = response.data;
                        $scope.selectedRolesPromise = $scope.$parent.getActualSelectedRolesPromise();
                        $scope.selectedRolesPromise.then(function (selectedRolesWithTranslations) {
                            $scope.selectedRoles = selectedRolesWithTranslations.roleSets;
                            angular.forEach($scope.selectedRoles, function (role, key) {
                                $scope.selectedRolesLabel.push(selectedRolesWithTranslations.roleTranslations[role]);
                            });

                            $scope.superAdminWarning = AdminManageRoleHelperService.traverseTreeAndUpdate($scope.roleTree, $scope.selectedRoles, $scope.isRoleTreePristine);
                            $scope.roleTreeLoading = false;
                        }, function (reason) {
                        }, function (update) {
                        });
                    }, function (response) {
                    });
        };

        /**
         * Triggers every time a checkbox is checked/unchecked. If a checkbox is checked then set it's and it's children selected attribute to true, 
         * opens the actual branch of the tree and push the selected roles to the selectedRoles array, and their labels to the selectedRolesLabel array.
         * If a checkbox is unchecked then set it's and it's parents selected attributes to false and removes the roles and their labels from the selectedRoles 
         * and selectedRolesLabel array. Also sets the isRoleTreePristine variable to false.
         * In the end calls traverseTreeAndUpdate method to make changes on the whole tree if necessary. 
         * 
         * @param {Node} ivhNode
         * @param {Boolean} ivhIsSelected
         * @param {ivhTree} ivhTree
         */
        $scope.treeChanged = function (ivhNode, ivhIsSelected, ivhTree) {
            $scope.isRoleTreePristine = false;

            AdminManageRoleHelperService.updateTreeAndSelectedRolesOnChange(ivhIsSelected, ivhNode, $scope.roleTree, $scope.selectedRoles, $scope.selectedRolesLabel);
            $scope.superAdminWarning = AdminManageRoleHelperService.traverseTreeAndUpdate($scope.roleTree, $scope.selectedRoles, $scope.isRoleTreePristine);
        };

        /**
         * Returns true if the form is invalid, false if the form is valid.
         * 
         * @returns {Boolean}
         */
        $scope.$parent.isChildFormInvalid = function () {
            var name = $scope.data['role_set[name]'];
            var isNameUnique = $scope.$parent.isRoleSetNameUniqueExceptItself($scope.data['role_set[name]']);
            var isRoleSetUnique = $scope.$parent.isRoleSetUniqueExceptItself($scope.selectedRoles);
            var selectedRolesCount = $scope.selectedRoles.length;

            AdminManageRoleHelperService.updateFormValidity($scope.role_set, name, isNameUnique, isRoleSetUnique, $scope.superAdminWarning, selectedRolesCount);

            return $scope.role_set.$invalid;
        };

        //Submit the form on this event, this event is broadcasted by EditRoleSetShowModal when the ok button clicked
        $scope.$on('handleEditRoleSetSubmit', function () {
            if (!$scope.$parent.isChildFormInvalid()) {
                $scope.submit();
            }
        });

        /**
         * Post the form to edit the RoleSet.
         */
        $scope.submit = function (path) {
            $scope.data['role_set[roles]'] = $scope.selectedRoles;
            $http.post(Routing.generate('admin_api_core_user_submit_edit_role_set_form', {roleSetId: $scope.roleSetId, _locale: Translator.locale}), $.param($scope.data), {
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            })
                    .then(function (response) {
                        $rootScope.$broadcast('handleNewRoleSetCreated');
                        $scope.$parent.close();
                    }, function (response) {
                        if (response.status == 400) {
                            var responseData = response.data;
                            if (angular.isDefined(responseData) && responseData.hasOwnProperty('data') && responseData.data.hasOwnProperty('errors')) {
                                $scope.errors.global = responseData.data.errors;
                            }
                            if (angular.isDefined(responseData) && responseData.hasOwnProperty('data') && responseData.data.hasOwnProperty('form')) {
                                $scope.errors.fields = responseData.data.form.children;
                            }
                        }
                    });
        };
    }]);

