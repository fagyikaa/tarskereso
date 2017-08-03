'use strict';

App.controller('UserProfileGalleryController', ['$scope', 'MediaImageHelperService', function ($scope, MediaImageHelperService) {
        $scope.userId;
        $scope.allowedMimeTypes;
        $scope.maxFileSize;

        $scope.openViewImageModal = function (imageId) {
            MediaImageHelperService.openViewModal(imageId);
        };
        
        $scope.init = function (userId) {
            $scope.userId = userId;
            MediaImageHelperService.getFileFilters().then(function (obj) {
                $scope.allowedMimeTypes = obj.allowedMimeTypes;
                $scope.maxFileSize = obj.maxFileSize;
            });
        };

        /**
         * Opens the image uploading modal.
         * 
         */
        $scope.openUploadModal = function () {
            MediaImageHelperService.openUploadModal($scope.userId, 'false', $scope.maxFileSize, $scope.allowedMimeTypes);
        };

    }]);


