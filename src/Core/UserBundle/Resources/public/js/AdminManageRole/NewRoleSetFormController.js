'use strict';

App.controller('NewRoleSetFormController', ['$rootScope', '$scope', '$http', 'AdminManageRoleHelperService', function ($rootScope, $scope, $http, AdminManageRoleHelperService) {

        $scope.roleTreeLoading = true;
        $scope.isRoleTreePristine = true;
        $scope.data = {};
        $scope.errors = {};
        $scope.roleTree = [{}];
        $scope.selectedRoles = [];
        $scope.selectedRolesLabel = [];
        $scope.superAdminWarning = false;

        /**
         * Gets the full role tree and hide role tree loading message when done.
         */
        $scope.init = function () {
            $http.get(Routing.generate('admin_api_core_user_get_full_role_tree', {_locale: Translator.locale}))
                    .then(function (response) {
                        $scope.roleTree = response.data;
                        $scope.roleTreeLoading = false;
                    }, function (response) {
                    });
        };

        /**
         * Triggers every time a checkbox is checked/unchecked. If a checkbox is checked then set it's and it's children selected
         * attribute to true and push the roles and their translations into the selectedRoles and selectedRolesLabel array.
         * If a checkbox is unchecked then set it's and it's parents selected attributes to false and removes the roles and their translations 
         * from the selectedRoles and selectedRolesLabel array. Also opens the actual branch of the tree.
         * In the end calls traverseTreeAndUpdate method to make changes on the whole tree if necessary. 
         * 
         * @param {Node} ivhNode
         * @param {Boolean} ivhIsSelected
         * @param {ivhTree} ivhTree
         */
        $scope.treeChanged = function (ivhNode, ivhIsSelected, ivhTree) {
            $scope.isRoleTreePristine = false;

            AdminManageRoleHelperService.updateTreeAndSelectedRolesOnChange(ivhIsSelected, ivhNode, $scope.roleTree, $scope.selectedRoles, $scope.selectedRolesLabel);
            $scope.superAdminWarning = AdminManageRoleHelperService.traverseTreeAndUpdate($scope.roleTree, $scope.selectedRoles);
        };


        /**
         * Returns true if the form is invalid, false if the form is valid.
         * 
         * @returns {Boolean}
         */
        $scope.$parent.isChildFormInvalid = function () {
            var name = $scope.data['role_set[name]'];
            var isNameUnique = $scope.$parent.isRoleSetNameUnique($scope.data['role_set[name]']);
            var isRoleSetUnique = $scope.$parent.isRoleSetUnique($scope.selectedRoles);
            var selectedRolesCount = $scope.selectedRoles.length;

            AdminManageRoleHelperService.updateFormValidity($scope.role_set, name, isNameUnique, isRoleSetUnique, $scope.superAdminWarning, selectedRolesCount);

            return $scope.role_set.$invalid;
        };

        //Submit the form on this event, this event is broadcasted by NewRoleSetShowModal when the ok button clicked
        $scope.$on('handleNewRoleSetSubmit', function () {
            if (!$scope.$parent.isChildFormInvalid()) {
                $scope.submit();
            }
        });

        /**
         * Post the form to create a new RoleSet.
         */
        $scope.submit = function () {
            $scope.data['role_set[roles]'] = $scope.selectedRoles;
            $scope.errors = {};
            $http.post(Routing.generate('admin_api_core_user_submit_new_role_set_form', {_locale: Translator.locale}), $.param($scope.data), {
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

