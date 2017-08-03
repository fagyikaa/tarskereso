// Handles ajax errors
App.factory('RequestsErrorHandler', ['$q', '$window', '$injector', function ($q, $window, $injector) {

        return {
            // --- Response interceptor for handling errors globally ---
            responseError: function (rejection) {
                //Need this way due to circular reference
                var Notification = $injector.get('Notification');
                var errorTitle = null;
                var errorMessage = null;

                var rejectionData = angular.fromJson(rejection.data);
                if (rejectionData.hasOwnProperty('data')) {

                    var jsonOrFalse = tryParseJSON(rejectionData.data);
                    if (jsonOrFalse !== false) {

                        rejectionData.data = jsonOrFalse;

                        if (rejectionData.data.hasOwnProperty('errors') && rejectionData.data.errors.constructor === Array) {

                            var uniqueArray = rejectionData.data.errors.filter(function (item, pos) {
                                return rejectionData.data.errors.indexOf(item) === pos;
                            });

                            rejectionData.data.errors = uniqueArray;
                        }
                    }
                }

                switch (rejection.status) {
                    case 401:
                        $window.location.href = Routing.generate('core_user_index', {_locale: Translator.locale});
                        break;

                    case 403:
                        errorTitle = Translator.trans('common.notification.error.403.title');
                        errorMessage = Translator.trans('common.notification.error.403.message');
                        break;

                    case 404:
                        errorTitle = Translator.trans('common.notification.error.404.title');
                        errorMessage = Translator.trans('common.notification.error.404.message');
                        break;

                    case 500:
                        errorTitle = Translator.trans('common.notification.error.500.title');
                        errorMessage = Translator.trans('common.notification.error.500.message');
                        break;

                    case 503:
                        errorTitle = Translator.trans('common.notification.error.503.title');
                        errorMessage = Translator.trans('common.notification.error.503.message');
                        break;

                    default:
                        if (rejection.status >= 400 && rejection.status < 500) {
                            errorTitle = Translator.trans('common.notification.error.4xx.title', {status_code: rejection.status});
                            errorMessage = Translator.trans('common.notification.error.4xx.message');
                        } else if (rejection.status >= 500 && rejection.status < 600) {
                            errorTitle = Translator.trans('common.notification.error.5xx.title', {status_code: rejection.status});
                            errorMessage = Translator.trans('common.notification.error.5xx.message');
                        }
                }

                //Notify the error message if the exception's status code is above 400 and the showing of notify is enabled
                if (rejection.status >= 400 && (rejectionData.hasOwnProperty('showNotify') && rejectionData.showNotify)) {
                    // If the correct structure comes from the exception and the specific message has been set for the notify then it will popup in notify
                    if (rejectionData.hasOwnProperty('useMessageForNotify') && rejectionData.hasOwnProperty('message') && rejectionData.useMessageForNotify) {
                        errorMessage = rejectionData.message;
                    }

                    Notification.error({title: errorTitle, message: errorMessage});
                }

                return $q.reject(rejection);
            }
        };

        /**
         * Tries to parse jsonString. If jsonString is a valid JSON then returns the
         * parsed result, returns false otherwise.
         * 
         * @param {Mixed} jsonString
         * @returns {Array|Object|Boolean}
         */
        function tryParseJSON(jsonString) {
            try {
                var parsed = JSON.parse(jsonString);

                // Handle non-exception-throwing cases:
                // Neither JSON.parse(false) or JSON.parse(1234) throw errors, hence the type-checking,
                // but... JSON.parse(null) returns null, and typeof null === "object", 
                // so we must check for that, too. Thankfully, null is falsey, so this suffices:
                if (parsed && typeof parsed === "object") {
                    return parsed;
                }
            } catch (e) {
            }

            return false;
        };

    }]);
