'use strict';

App.controller('UploadImageFormController', ['$scope', 'MediaImageHelperService', function ($scope, MediaImageHelperService) {
        $scope.data = {};
        $scope.errors = {};
        $scope.isProfile;
       
        /**
         * Sets whether the uploading was triggered by profile picture upload. Sends isChildFormInvalid()
         * function to UploadImageModalController via MediaImageHelperService service.
         * 
         * @param {Boolean} isProfile
         */
        $scope.init = function(isProfile) {
            $scope.isProfile = isProfile;
            MediaImageHelperService.setIsChildFormValid($scope.isChildFormInvalid);
        };    
        
        /**
         * Deletes those properties from data which are false. It's required because
         * symfony's form treats properties as false only if those are missing from the request.
         * 
         * @param {Object} data
         */
        $scope.deleteFalseProperties = function (data) {
            angular.forEach(data, function(value, key) {
                if (value === false) {
                    delete data[key];
                }
            });
        };

        /**
         * When user adds an acceptable file reset form.
         */
        $scope.$on('handleFileAdded', function () {
            $scope.resetForm();
        });

        /**
         * Searches for the CSRF token of the form and deletes form data except the token.
         * If isProfile true then also set back that property to true.
         */
        $scope.resetForm = function () {
            for (var prop in $scope.data) {
                if (prop.indexOf('_token') > -1) {
                    var token = $scope.data[prop];
                    $scope.data = {};
                    $scope.data[prop] = token;
                }
            }
            if ($scope.isProfile){
                $scope.data['core_media_image[isProfile]'] = true;
            }
            $scope.core_media_image.$setPristine();
        };

        /*
         * The submit button in the modal's footer is disabled according to this function. Returns false if the form can be submitted.
         * 
         * @returns {Boolean}
         */
        $scope.isChildFormInvalid = function () {
            return $scope.core_media_image.$invalid;
        };

        /**
         * Calls deleteFalseProperties() and sends form data to UploadImageModalController.
         */
        $scope.$on('handleImageUpload', function () {
            $scope.deleteFalseProperties($scope.data);
            MediaImageHelperService.setFormData($scope.data);
        });

    }]);


