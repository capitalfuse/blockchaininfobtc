/**
 * 2011-2018 FicusOnline
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to ficus.online@gmail.com so we can send you a copy immediately.
 *
 * @author    FicusOnline <ficus.online@gmail.com>
 * @copyright 2011-2016 BlockchaininfoBTC
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of FicusOnline
 */

angular
    .module('bciInvoice', [
        'monospaced.qrcode'
    ])

    .config(function ($interpolateProvider) {

        $interpolateProvider.startSymbol('//');
        $interpolateProvider.endSymbol('//');
    })

    .controller('CheckoutController', function($window, $scope, $location, $interval, $rootScope) {
        var totalProgress = 100;
        var totalTime = 10*60; //10m
        $scope.progress = totalProgress;
        $scope.clock = totalTime;

        $scope.tick = function(cancel_url) {
            $scope.clock = $scope.clock-1;
            $scope.progress = Math.floor($scope.clock*totalProgress/totalTime);

            if($scope.clock == 0){
                //Refresh invoice page
                $window.location.href = cancel_url;
            }
        };

        $scope.init = function(invoice_status, invoice_addr, invoice_timestamp, base_websocket_url, final_url, cancel_url){

            if(invoice_status == -1){
                $scope.tick_interval  = $interval($scope.tick, 1000, 0, true, cancel_url);
                var btcs = new WebSocket(base_websocket_url);
                btcs.onopen = function() {
                    //invoice_addr is variable, so adopted "+variable+" format.
                    var data = { "op" : "addr_sub", "addr" : ""+invoice_addr+"" };
                    var message = JSON.stringify(data);
                    btcs.send(message);
                }
                btcs.onmessage = function(onmsg) {
                    var response = JSON.parse(onmsg.data);
                    var getOuts = response.x.out;
                    var countOuts = getOuts.length;
                    for(i = 0; i < countOuts; i++) {
                      //check every output to see if it matches specified address
                        var outAdd = response.x.out[i].addr;
                        var specAdd = invoice_addr;
                        if (outAdd == specAdd ) {
                            //var amount = response.x.out[i].value;
                            //var calAmount = amount / 100000000;
                            $window.location.href = final_url;
                        }
                    }
                }
            }
        };

        $scope.postData = function (cancel_url) {
            // use $.param jQuery function to serialize data from JSON
            $window.location.href = cancel_url;
        };
    });
    // .controller('WindowController', function($window, $scope, $location) {
    //     $window.onbeforeunload = function() {
    //         return 'Caution, are you sure you want to leave?';
    //     };
    // });
