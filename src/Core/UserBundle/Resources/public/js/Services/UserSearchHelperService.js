'use strict';

App.factory('UserSearchHelperService', ['$rootScope', function ($rootScope) {
        var service = {};
        service.hasAnyFilterFunction;
        
        /**
         * Broadcasts handleSearchResult event with the object containing result under result key.
         * 
         * @param {Reponse} result
         */
        service.searchResultSuccess = function(result) {
            $rootScope.$broadcast('handleSearchResult', {result: result});
        };
        
        /**
         * Saves hasAnyFilterFunction and broadcasts handleSetHasAnyFilter event.
         * 
         * @param {Function} hasAnyFilterFunction
         */
        service.setHasAnyFilter = function(hasAnyFilterFunction) {
            service.hasAnyFilterFunction = hasAnyFilterFunction;
            $rootScope.$broadcast('handleSetHasAnyFilter');
        };

        return service;
    }]);


