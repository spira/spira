angular.module('userService', [])
    .factory('userService', function (apiService) {


        // Private methods, namespaced for code clarity
        var privateMethods = {

            _getAllUsers: function(){

                return apiService.get('/users').then(function(res){
                    return res.data;
                }).catch(function(err){
                    console.error(err);
                });

            }

        };

        var publicMethods = function(){

            return {

                getAllUsers: function(){
                    return privateMethods._getAllUsers();
                }

            };

        };

        return publicMethods();
    })
;