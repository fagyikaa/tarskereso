'use strict';

App.controller('ViewImageModalController', ['$scope', '$rootScope', '$http', '$uibModalInstance', 'MediaImageHelperService', 'imageId', function ($scope, $rootScope, $http, $uibModalInstance, MediaImageHelperService, imageId) {
        $scope.imageId = imageId;
        $scope.image;
        $scope.stars;
        $scope.error;

        /**
         * Sets error to false and calls getImage().
         */
        $scope.init = function () {
            $scope.error = false;
            $scope.getImage();
        };

        /**
         * Fetches the image with imageId from the server. If the response has vote property
         * then sets stars to vote's stars.
         */
        $scope.getImage = function () {
            $scope.error = false;
            $http.get(Routing.generate('api_core_media_get_image', {_locale: Translator.locale, imageId: $scope.imageId}))
                    .then(function (response) {
                        $scope.image = response.data.image;
                        if (response.data.hasOwnProperty('vote')) {
                            $scope.stars = response.data.vote.stars;
                        } else {
                            $scope.stars = 0;
                        }
                    }, function (response) {
                        $scope.error = response.data;
                    });
        };

        /**
         * Updates the current images property property to data. Returns the error message
         * if validation fails. If image is profile image or private image (may due to the edition)
         * then broadcasts handleImageEditSuccess.
         * 
         * @param {String} data
         * @param {String} property
         */
        $scope.updateImage = function (data, property) {
            var params = {
                imageId: $scope.imageId,
                data: data,
                property: property
            };

            return $http.post(Routing.generate('api_core_media_edit_image_data', {_locale: Translator.locale}), params)
                    .then(function (response) {
                        $scope.image = response.data;
                        if (property === 'isProfile' || property === 'isPrivate') {
                            $rootScope.$broadcast('handleImageEditSuccess');
                        }
                    }, function (response) {
                        var result = response.data;
                        
                        if (angular.isDefined(result) && result.hasOwnProperty('data') && result.data.constructor === Array && angular.isDefined(result.data[0])) {
                            return result.data[0];
                        } else if (angular.isDefined(result) && result.hasOwnProperty('message')) {
                            return result.message;
                        }

                        return result;
                    });
        };

        /**
         * If data is undefined returns '-', if true then returns true message for checkbox, 
         * return false message otherwise.
         * 
         * @param {String} data
         * @returns {String}
         */
        $scope.getLabelForCheckbox = function (data) {
            if (angular.isUndefined(data)) {
                return '-';
            } else if (data === true) {
                return Translator.trans('view_image.modal.x_edit.checkbox_true', {}, 'gallery');
            } else {
                return Translator.trans('view_image.modal.x_edit.checkbox_false', {}, 'gallery');
            }
        };

        /**
         * Posts a vote request to the server. If stars is 0 then removes the current vote (if user has already voted).
         * 
         * @param {Integer} stars
         */
        $scope.vote = function (stars) {
            $scope.error = false;
            $http.post(Routing.generate('api_core_media_vote_on_image', {_locale: Translator.locale, imageId: $scope.imageId, stars: $scope.stars}))
                    .then(function (response) {
                        $scope.image.voteAverage = response.data.image.voteAverage;
                    }, function (response) {
                        $scope.error = response.data;
                    });
        };

        /**
         * Deletes this image from the server. In case of success handleImageRemoveSuccess event
         * is broadcasted and the modal is closed, error set to response.data otherwise.
         */
        $scope.remove = function () {
            $scope.error = false;
            $http.delete(Routing.generate('api_core_media_remove_image', {_locale: Translator.locale, imageId: $scope.imageId}))
                    .then(function (response) {
                        $rootScope.$broadcast('handleImageRemoveSuccess');
                        $scope.close();
                    }, function (response) {
                        $scope.error = response.data;
                    });
        };

        /**
         * Closes this modal instance with the 'message' message
         *
         * @param {String} message
         */
        $scope.close = function (message) {
            $uibModalInstance.close(message);
        };

    }]);


