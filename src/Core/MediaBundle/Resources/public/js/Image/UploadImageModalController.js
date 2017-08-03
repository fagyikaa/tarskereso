'use strict';

App.controller('UploadImageModalController', ['$scope', '$rootScope', '$uibModalInstance', 'maxFileSize', 'allowedMimeTypes', 'FileUploader', 'MediaImageHelperService', function ($scope, $rootScope, $uibModalInstance, maxFileSize, allowedMimeTypes, FileUploader, MediaImageHelperService) {
        $scope.maxFileSize = maxFileSize;
        $scope.allowedMimeTypes = allowedMimeTypes;
        $scope.showWrongFormatError = false;
        $scope.showUploadError = false;
        $scope.isChildFormInvalid;
        $scope.formData;

        /**
         * Set isChildFormInvalid function reference when form is displayed.
         */
        $scope.$on('handleSetIsChildFormInvalid', function () {
            $scope.isChildFormInvalid = MediaImageHelperService.isChildFormInvalid;
        });

        /**
         * Broadcasted by the form to send it's form data via MediaImageHelperService. Adds
         * form data to the item's formData and starts uploading.
         */
        $scope.$on('handleSetUploadImageFormData', function () {
            $scope.formData = MediaImageHelperService.formData;
            $scope.uploader.queue[0].formData.push($scope.formData);
            $scope.uploader.queue[0].upload();
        });

        /**
         * Triggers click on file input thus file browser opens.
         */
        $scope.browse = function () {
            angular.element('#file-input').click();
        };

        /**
         * If upload returns error then clears file input.
         */
        $scope.handleUploadError = function () {
            angular.element('input[type="file"]').val(null);
        };

        /**
         * Removes the item from the queue and clears the input so users can select the same file again.
         *
         * @param {File|FileLikeObject} item
         */
        $scope.removeItem = function (item) {
            item.remove();
            angular.element('input[type="file"]').val(null);
        };

        /**
         * Checks if the selected file's type is allowed and the size is not greater than maxFileSize.
         *
         * @param {File|FileLikeObject} item
         * @param {Array} options
         * @returns {Boolean}
         */
        $scope.isAllowedFile = function (item, options) {
            if (jQuery.inArray(item.type, Object.keys($scope.allowedMimeTypes)) === -1) {
                return false;
            }
            return item.size <= $scope.maxFileSize * 1024 * 1024; // convert to byte
        };

        /**
         * Returns in an array the extension of allowed file types
         *
         * @returns {Array}
         */
        $scope.getAllowedFileTypes = function () {
            var types = [];
            angular.forEach($scope.allowedMimeTypes, function (value, key) {
                types.push(value);
            });

            return types;
        };

        /**
         * Closes this modal instance with the 'message' message
         *
         * @param {String} message
         */
        $scope.close = function (message) {
            $uibModalInstance.close(message);
        };

        /**
         * Called if the user tries to add not supported file. If showWrongFormatError is true then error message shows up.
         *
         */
        $scope.onWrongFileFormat = function () {
            $scope.showWrongFormatError = true;
            $scope.$apply();
        };

        /**
         * Broadcasts handleImageUpload event which causes the form controller to send back it's data.
         */
        $scope.upload = function () {
            $scope.$broadcast('handleImageUpload');
        };

        //The angular-file-uploader instance
        $scope.uploader = new FileUploader({
            autoUpload: false,
            queueLimit: 2
        });

        // FILTERS
        //Checks if the file's type is allowed and triggers onWrongFileFormat() if the type isnt acceptable.
        $scope.uploader.filters.push({
            name: 'filterMimeType',
            fn: function (item, options) {
                return $scope.isAllowedFile(item, options);
            }});

        // CALLBACKS
       
        /**
         * Removes the element from the queue and displays error message.
         * 
         * @param {File|FileLikeObject} item
         * @param {Array} filter
         * @param {Object} options
         */
        $scope.uploader.onWhenAddingFileFailed = function (item, filter, options) {
            $scope.uploader.removeFromQueue(0);
            if (!$scope.isAllowedFile(item, options)) {
                $scope.onWrongFileFormat();
                $scope.$apply();
            }
        };

        /**
         * Hides error messages and removes the previous item if there was one, then broadcasts handleFileAdded event.
         * 
         * @param {File|FileLikeObject} fileItem
         */
        $scope.uploader.onAfterAddingFile = function (fileItem) {
            //Only one image is allowed to upload at the same time however if the queueLimit is set to 1 then several bugs will appear
            //so the limit is 2 but the first element will be removed if user selects other files after the first one.
            if ($scope.uploader.queue.length > 1) {
                $scope.uploader.removeFromQueue(0);
            }
            $scope.showWrongFormatError = false;
            $scope.showUploadError = false;
            $scope.$broadcast('handleFileAdded');
        };
        
        /**
         * Broadcasts handleUploadImageSuccessevent and closes the modal.
         * 
         * @param {File|FileLikeObject} fileItem
         * @param {Object} response
         * @param {Integer} status
         * @param {Object} headers
         */
        $scope.uploader.onSuccessItem = function (fileItem, response, status, headers) {
            $rootScope.$broadcast('handleUploadImageSuccess', response);
            $scope.close();
        };
        
        /**
         * Clears file input, removes element from the queue and shows upload error message.
         * 
         * @param {File|FileLikeObject} fileItem
         * @param {Object} response
         * @param {Integer} status
         * @param {Object} headers
         */
        $scope.uploader.onErrorItem = function (fileItem, response, status, headers) {
            //If the server returns error clear the file input
            $scope.handleUploadError();
            //removes the item from the queue and shows the uploading error message
            $scope.uploader.removeFromQueue(0);
            $scope.showUploadError = true;
        };

    }]);


