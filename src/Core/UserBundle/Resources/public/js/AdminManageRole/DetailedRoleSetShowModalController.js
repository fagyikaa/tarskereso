'use strict';

App.controller('DetailedRoleSetShowModalController', ['$scope', '$uibModalInstance', 'roleSetTree', function ($scope, $uibModalInstance, roleSetTree) {
    $scope.roleSetTree = roleSetTree;
    
    /**
     * Closes this modal instance
     */
    $scope.close = function () {
        $uibModalInstance.dismiss('close');
    };

}]);





