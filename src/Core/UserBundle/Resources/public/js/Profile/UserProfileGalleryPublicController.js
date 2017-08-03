'use strict';

App.controller('UserProfileGalleryPublicController', ['$scope', 'MediaImageHelperService', '$http', function ($scope, MediaImageHelperService, $http) {
        $scope.userId;
        $scope.publicPhotos = [];
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
            if (image.isPrivate === false) {
                $scope.getPublicPhotos();
            }
        });

        $scope.$on('handleImageEditSuccess', function () {
            $scope.getPublicPhotos();
        });

        $scope.$on('handleImageRemoveSuccess', function () {
            $scope.getPublicPhotos();
        });

        $scope.init = function (userId) {
            $scope.userId = userId;

            $scope.pagination.nextText = Translator.trans('common.pagination.next', {}, 'messages');
            $scope.pagination.previousText = Translator.trans('common.pagination.prev', {}, 'messages');
            $scope.pagination.currentPage = 1;
            $scope.pagination.photosPerPage = 12;
            $scope.pagination.maxSize = 5;
            $scope.getPublicPhotos();
        };

        $scope.getPublicPhotos = function () {
            $http.get(Routing.generate('api_core_media_get_public_images_for_user', {userId: $scope.userId, _locale: Translator.locale}))
                    .then(function (response) {
                        var photos = MediaImageHelperService.sortByUploadedAtDesc(response.data);
                        $scope.pagination.countOfPhotos = photos.length;
                        $scope.publicPhotos = MediaImageHelperService.distributePhotos(photos, $scope.pagination.photosPerPage);
                    }, function (response) {
                    });
        };

    }]);


