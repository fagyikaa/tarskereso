'use strict';

App.factory('CommonHelperService', ['$rootScope', 'dialogs', function ($rootScope, dialogs) {
        var service = {};

        /**
         * Returns a confirmation modal
         * 
         * @param {String} header
         * @param {String} msg
         * @returns {modal}
         */
        service.confirmModal = function (header, msg) {
            if (angular.isUndefined(header)) {
                header = Translator.trans('common.confirm_modal.header');
            }
            if (angular.isUndefined(msg)) {
                msg = Translator.trans('common.confirm_modal.message');
            }

            return dialogs.confirm(header, msg);
        };

        return service;
    }]);


