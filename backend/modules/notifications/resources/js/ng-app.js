;(function() {
    var module = angular.module('notifications', []);

    module.factory('dataSource', function($q, $rootScope) {
        var ds = {
            sender_name : '',
            sender_email : '',
            reply_to_customers : false,
            send_as : 'html',
            notifications : [],
            loadData  : function(params) {
                var deferred = $q.defer();
                jQuery.ajax({
                    url  : ajaxurl,
                    type : 'POST',
                    data : jQuery.extend({ action : 'bookly_get_email_notifications_data' }, params),
                    dataType : 'json',
                    success  : function(response) {
                        if (response.success) {
                            ds.sender_name = response.data.sender_name;
                            ds.sender_email  = response.data.sender_email;
                            ds.reply_to_customers = response.data.reply_to_customers;
                            ds.send_as = response.data.send_as;
                            ds.notifications = response.data.notifications;
                        }
                        $rootScope.$apply(deferred.resolve);
                    },
                    error : function() {
                        $rootScope.$apply(deferred.resolve);
                    }
                });

                return deferred.promise;
            }
        };

        return ds;
    });

    module.controller('emailNotifications', function($scope, dataSource) {
        $scope.showTestEmailNotificationDialog = function(){
            showTestEmailNotificationDialog();
        }
    });

    module.controller('testEmailNotificationsDialogCtrl', function($scope, dataSource, $timeout) {
        $scope.loading = true;
        $scope.mailSentAlert = false;
        $scope.allNotifications = false;
        $scope.toEmail = 'admin@example.com';
        $scope.dataSource = dataSource;

        dataSource.loadData().then(function(){
            $scope.loading = false;
            $scope.allNotificationsChecked();
        });

        $scope.$watch('notifications', function(newVal, oldVal){
            $scope.allNotificationsChecked();
        }, true);

        $scope.toggleAllNotifications = function(){
            var active = $scope.allNotifications ? '1' : '0';
            angular.forEach($scope.dataSource.notifications, function(notification){
                notification.active = active;
            });
        };

        $scope.allNotificationsChecked = function(){
            var count = $scope.selectedNotificationsCount();
            var totalCount = Object.keys($scope.dataSource.notifications).length;
            $scope.allNotifications = count===totalCount;
        };

        $scope.notificationChecked = function(){
            $scope.allNotificationsChecked();
        };

        $scope.selectedNotificationsCount = function(){
            var count = 0;
            angular.forEach($scope.dataSource.notifications, function(notification){
                count += notification.active==='1'?1:0;
            });
            return count;
        };

        $scope.testEmailNotifications = function(){
        };

    });

})();

var showTestEmailNotificationDialog = function () {
    jQuery('#ab_test_email_notifications_dialog').modal('show');
};