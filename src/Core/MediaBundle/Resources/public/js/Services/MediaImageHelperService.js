'use strict';

App.factory('MediaImageHelperService', ['$rootScope', '$http', '$q', '$uibModal', function ($rootScope, $http, $q, $uibModal) {
        var service = {};
        service.isChildFormInvalid;
        service.formData;
        service.allowedMimeTypes;
        service.maxFileSize;

        /**
         * Saves isChildFormInvalid function and broadcasts handleSetIsChildFormInvalid event
         * so it can be fetched.
         * 
         * @param {Function} isChildFormInvalid
         */
        service.setIsChildFormValid = function (isChildFormInvalid) {
            this.isChildFormInvalid = isChildFormInvalid;
            $rootScope.$broadcast('handleSetIsChildFormInvalid');
        };

        /**
         * Saves formData object and broadcasts handleSetUploadImageFormData event
         * so it can be fetched.
         * 
         * @param {Object} formData
         */
        service.setFormData = function (formData) {
            this.formData = formData;
            $rootScope.$broadcast('handleSetUploadImageFormData');
        };

        /**
         * Fetches from the server the allowed mime types and max file size and saves it, so the enxt time
         * it wont be fetched. Returns a promise which resolves an object with these values.  
         * 
         * @returns {Promise}
         */
        service.getFileFilters = function () {
            var deferred = $q.defer();
            if (angular.isUndefined(service.allowedMimeTypes) || angular.isUndefined(service.maxFileSize)) {
                $http.get(Routing.generate('api_core_media_get_allowed_mimetypes_and_file_size', {_locale: Translator.locale}))
                        .then(function (response) {
                            service.allowedMimeTypes = response.data.mimeTypes;
                            service.maxFileSize = response.data.maxFileSize;
                            deferred.resolve({
                                allowedMimeTypes: service.allowedMimeTypes,
                                maxFileSize: service.maxFileSize
                            });

                        }, function (response) {
                        });
            } else {
                deferred.resolve({
                    allowedMimeTypes: service.allowedMimeTypes,
                    maxFileSize: service.maxFileSize
                });
            }

            return deferred.promise;
        };

        /**
         * Opens the image uploading modal. 
         * 
         */
        service.openUploadModal = function (userId, isProfile, maxFileSize, allowedMimeTypes) {
            var modalInstance = $uibModal.open({
                animation: true,
                templateUrl: Routing.generate('core_media_upload_image_modal', {userId: userId, isProfile: isProfile, _locale: Translator.locale, _ts: new Date().getTime()}),
                windowClass: 'app-modal-window',
                controller: 'UploadImageModalController',
                resolve: {
                    maxFileSize: function () {
                        return maxFileSize;
                    },
                    allowedMimeTypes: function () {
                        return allowedMimeTypes;
                    }
                },
                size: 'lg'
            });
        };
        
        /**
         * Opens the image uploading modal.
         * 
         */
        service.openViewModal = function (imageId) {
            var modalInstance = $uibModal.open({
                animation: true,
                templateUrl: Routing.generate('core_media_view_image_modal', {imageId: imageId, _locale: Translator.locale, _ts: new Date().getTime()}),
                windowClass: 'app-modal-window',
                controller: 'ViewImageModalController',
                resolve: {
                    imageId: function () {
                        return imageId;
                    }
                },
                size: 'lg'
            });
        };

        /**
         * Returns the given array sorted by the contained objects' uploadedAt property DESC.
         * 
         * @param {array} photos
         * @returns {array}
         */
        service.sortByUploadedAtDesc = function (photos) {
            return photos.sort(function (a, b) {
                if (a.uploadedAt > b.uploadedAt) {
                    return -1;
                }
                if (a.uploadedAt < b.uploadedAt) {
                    return 1;
                }
                return 0;
            });
        };
        
        /**
         * Distributes the given photos in an array under integer keys starts from 1. 
         * The photos are grouped by splitCount. Each photo object expanded with thumbnailUrl property
         * which is the src of the related photo's thumbnail
         * 
         * @param {Array} photos
         * @param {Integer} splitCount
         * @returns {Array}
         */
        service.distributePhotos = function(photos, splitCount) {
            var distributedPhotos = [];
            var loopIndex = 0, arrayIndex = 0;
            angular.forEach(photos, function (value, key) {
                if (loopIndex % splitCount === 0) {
                    arrayIndex++;
                    distributedPhotos[arrayIndex] = [];
                }
                value['thumbnailUrl'] = Routing.generate('api_core_media_serve_image_thumbnail', {_locale: Translator.locale, size: 150, imageId: value.id});
               distributedPhotos[arrayIndex].push(value);
                loopIndex++;
            });
            
            return distributedPhotos;
        };

        return service;
    }]);


