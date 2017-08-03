'use strict';

App.factory('UserProfileHelperService', ['$http', function ($http) {
        var service = {};

        /**
         * Updates user's property to data. category is introduction/ideal depends on the actual profile page.
         * Returning true in case of success, string in case of failure.
         * 
         * @param {String} data
         * @param {String} property
         * @param {Integer} userId
         * @param {String} category
         * @return {Promise}
         */
        service.updateUser = function (data, property, userId, category) {

            var params = {
                userId: userId,
                data: data,
                property: property,
                category: category
            };

            return $http.post(Routing.generate('api_core_user_edit_profile_data', {_locale: Translator.locale}), params)
                    .then(function (response) {
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
         * Fetches from the server the user's with userId ideal/introduction data depending on category.
         * If the current user can't edit the fields then readOnly is true. If they can then also fetches
         * every select values for the select fields.
         * 
         * @param {Integer} userId
         * @param {String} category
         * @returns {Promise}
         */
        service.getProfileData = function (userId, category) {
            return $http.get(Routing.generate('api_core_user_get_profile_data', {userId: userId, category: category, _locale: Translator.locale})).then(function (response) {
                var profileDatas = {};
                profileDatas.readOnly = response.data.readOnly;
                profileDatas.fieldsData = response.data.fieldsData;
                if (!profileDatas.readOnly) {
                    return $http.get(Routing.generate('api_core_user_get_possible_select_values_for_profile_datas', {category: category, _locale: Translator.locale})).then(function (response) {
                        profileDatas.possibleSelectValues = response.data;
                        return profileDatas;
                    }, function (response) {
                    });
                } else {
                    return profileDatas;
                }
            }, function (response) {
            });
        };

        /**
         * Translates the data of user's property or if data is undefined then default text.
         * 
         * @param {String} data
         * @param {String} property
         * @returns {String}
         */
        service.getTranslatedNameForSelect = function (data, property) {
            if (angular.isUndefined(data)) {
                return Translator.trans('x_edit.empty_data', {}, 'profile');
            } else if (property === 'gender') {
                return Translator.trans('ideal.gender.' + data, {}, 'profile');
            } else if (property === 'address') {
                return data;
            } else {
                return Translator.trans('personal.' + service.camelCaseToUnderLine(property) + '.' + service.camelCaseToUnderLine(data), {}, 'profile');
            }
        };

        /**
         * Transforms camelCased text to underLined.
         * 
         * @param {String} text
         * @returns {String}
         */
        service.camelCaseToUnderLine = function (text) {
            return text.replace(/(?:^|\.?)([A-Z])/g, function (x, y) {
                return "_" + y.toLowerCase()
            }).replace(/^_/, "");
        };

        return service;
    }]);


