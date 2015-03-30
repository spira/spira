angular.module('siteProgressService', [
    'ngProgress' // UX module for indicating loading progress
])
    .provider('siteProgressService', function(){

        var siteProgressProvider = this;

        this.$get = function (ngProgress, $q, $timeout) {

            ngProgress.height('4px');
            ngProgress.color('#3498DB');

            var siteProgress = {

                running: false, //status bar is in use
                stopped: false, //status bar has stopped (but probably still in use)
                completing: false, //status bar is animating completion

                /**
                 * Start, or resume the status bar
                 * @returns {boolean}
                 */
                start: function(){
                    if (this.completing){
                        return false;
                    }
                    if (!this.running && ngProgress.status() === 0){
                        this.running = true;
                        ngProgress.start();
                        return true;
                    }else if (this.stopped){
                        this.stopped = false;
                        ngProgress.start();
                        return true;
                    }
                    return false;
                },

                /**
                 * Stop the status bar
                 * @returns {boolean}
                 */
                stop: function(){
                    if (this.running){
                        this.stopped = true;
                        ngProgress.stop();
                        return true;
                    }
                    return false;
                },

                /**
                 * Complete the status bar
                 * @returns {boolean}
                 */
                complete: function(){
                    if (this.running && !this.completing){
                        this.completing = true;
                        ngProgress.complete();

                        $timeout(function () { //wait for the animation to finish before setting status
                            siteProgress.completing = false;
                            siteProgress.running = false;
                            siteProgress.stopped = false;
                        }, 1600);

                        return true;
                    }
                    return false;
                },


                /**
                 * Return to beginning
                 * @returns {boolean}
                 */
                reset: function(){
                    if (this.running){
                        ngProgress.reset();
                        this.running = false;
                        this.stopped = false;
                        return true;
                    }
                    return false;
                },

                /**
                 * Get the status of the progress bar
                 * @returns {int}
                 */
                status: function(){
                    return ngProgress.status();
                },

                /**
                 * Set the progress value of the progress bar
                 * @param value
                 * @returns {boolean}
                 */
                set: function(value){
                    if (this.running){
                        ngProgress.set(value);
                        this.running = true;
                        this.stopped = true;
                        return true;
                    }
                    return false;
                },

                /**
                 * Object for managing stacks of promises, specifically for handling the siteProgress
                 */
                promiseStack : (function(){

                    var stackProcessing = false,
                        stack = []
                        ;

                    var promiseStack =  {

                        /**
                         * Push an item on to the stack. Can be done at any time.
                         * Handles starting of the progress and reverting for every push, and final completion
                         * @param promise
                         */
                        push : function(promise){
                            stack.push(promise);

                            if (!stackProcessing){

                                siteProgress.start();

                                this.processStack().then(function(){ //process the stack

                                    siteProgress.complete(); //send slider to end

                                    stackProcessing = false;
                                });

                            }else{ //the stack is still being processed, push back the slider to represent progress going backwards

                                var currentProgress = siteProgress.status(), //get the current status (int / 100)
                                    fallBackTo = currentProgress - currentProgress * (currentProgress/120) //set it back to a point based on current progress
                                    ;

                                siteProgress.set(fallBackTo); //set the position
                                siteProgress.start(); //start moving again

                            }


                        },

                        /**
                         * Process the stack. Resolves promise when stack is empty. Stack can be dynamic
                         * @returns {promise|Promise.promise|Q.promise}
                         */
                        processStack : function(){

                            stackProcessing = true;

                            var deferred = $q.defer();

                            var processingItems = _.clone(stack); //copy array

                            $q.all(processingItems)['finally'](function(){ //when the current stack has processed

                                stack = _.difference(stack, processingItems); //remove the processed items

                                if (stack.length === 0){
                                    deferred.resolve(true); //stack completed
                                }else{
                                    deferred.resolve(promiseStack.processStack()); //resolve with another promise
                                }

                            });

                            return deferred.promise;

                        }

                    };

                    return promiseStack;

                })() //end promiseStack

            };


            return siteProgress;

        }; //end service definition
    })
;