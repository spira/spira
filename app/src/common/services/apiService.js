angular.module('apiService', [])
    .factory('apiService', function ($rootScope, $http, $q, $window, $stateParams, $location, API_URL, siteProgressService) {


        // Private methods, namespaced for code clarity
        var privateMethod = {

            sendRequest: function (apiKey, method, url, data, requestHeaders, skipInterceptor) {

                requestHeaders = requestHeaders || {}; //default to object

                var defaultHeaders = {
                    'Content-Type' : 'application/json'
                };

                var headers = _.merge(requestHeaders, defaultHeaders);

                var saveConfig = {
                    method: method,
                    url:  apiKey + url,
                    data: data,
                    headers: headers,
                    responseType: 'json'
                };

                if (skipInterceptor){
                    saveConfig.skipInterceptor = true;
                }

                var resultPromise =  $http(saveConfig);

                siteProgressService.promiseStack.push(resultPromise);

                return resultPromise;

            }
        };

        var publicMethods = function(apiKey, skipInterceptor){

            return {

                // Alias CRUD functions
                options: function (url, data, headers) {
                    return privateMethod.sendRequest(apiKey, 'OPTIONS', url, data, headers, skipInterceptor);
                },

                get: function (url, data, headers) {
                    return privateMethod.sendRequest(apiKey, 'GET', url, data, headers, skipInterceptor);
                },

                head: function (url, data, headers) {
                    return privateMethod.sendRequest(apiKey, 'HEAD', url, data, headers, skipInterceptor);
                },

                put: function (url, data, headers) {
                    return privateMethod.sendRequest(apiKey, 'PUT', url, data, headers, skipInterceptor);
                },

                post: function (url, data, headers) {
                    return privateMethod.sendRequest(apiKey, 'POST', url, data, headers, skipInterceptor);
                },

                patch: function (url, data, headers) {
                    return privateMethod.sendRequest(apiKey, 'PATCH', url, data, headers, skipInterceptor);
                },

                remove: function (url, data, headers) {
                    return privateMethod.sendRequest(apiKey, 'DELETE', url, data, headers, skipInterceptor);
                },


                /**
                 * Convenience function for injecting a differing API base to the one set with API_URL
                 * Usage Example:
                 * apiService.api('something-api').get('/foo');
                 * @param apiPath
                 */
                api: function (apiPath) {
                    return publicMethods(apiPath, skipInterceptor);
                },

                skipInterceptor: function(){
                    return publicMethods(apiKey, true);
                },
                /**
                 * Helper function for drilling down into an API route and returning items along the way
                 */
                apiSearch : function(){

                    var self = this;

                    var routeSegments = [];

                    var buildUrl = function(){
                        var url = '/';

                        url += routeSegments.join('/');

                        return url;
                    };

                    return {
                        refine:  function(){
                            Array.prototype.push.apply(routeSegments, arguments);
                            return this;
                        },
                        execute: function(){
                            var url = buildUrl();
                            return publicMethods(apiKey).get(url);
                        }
                    };

                },


                uuid: function(){
                    return $window.lil.uuid();
                },

                isUuid: function(uuid){
                    return $window.lil.isUuid(uuid, 4);
                }
            };

        };

        return publicMethods(API_URL, false);
    })
;