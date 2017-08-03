'use strict';

App.controller('UserProfileGalleryPrivateController', ['$scope', 'MediaImageHelperService', '$http', function ($scope, MediaImageHelperService, $http) {
        $scope.userId;
        $scope.privatePhotos = [];
        $scope.pagination = {
            countOfPhotos: 0,
            nextText: '',
            previousText: '',
            currentPage: 0,
            photosPerPage: 0,
            maxSize: 0,
            showFirstLast: true
        };

        $scope.$on('handleUploadImageSuccess', function (eventArgs, image) {
            if (image.isPrivate === true) {
                $scope.getPrivatePhotos();
            }
        });

        $scope.$on('handleImageEditSuccess', function () {
            $scope.getPrivatePhotos();
        });

        $scope.$on('handleImageRemoveSuccess', function () {
            $scope.getPrivatePhotos();
        });

        $scope.init = function (userId) {
            $scope.userId = userId;

            $scope.pagination.nextText = Translator.trans('common.pagination.next', {}, 'messages');
            $scope.pagination.previousText = Translator.trans('common.pagination.prev', {}, 'messages');
            $scope.pagination.currentPage = 1;
            $scope.pagination.photosPerPage = 12;
            $scope.pagination.maxSize = 5;
            $scope.getPrivatePhotos();
        };

        $scope.getPrivatePhotos = function () {
            $http.get(Routing.generate('api_core_media_get_private_images_for_user', {userId: $scope.userId, _locale: Translator.locale}))
                    .then(function (response) {
                        var photos = MediaImageHelperService.sortByUploadedAtDesc(response.data);
                        $scope.pagination.countOfPhotos = photos.length;
                        $scope.privatePhotos = MediaImageHelperService.distributePhotos(photos, $scope.pagination.photosPerPage);
                    }, function (response) {
                    });
        };

    }]);


