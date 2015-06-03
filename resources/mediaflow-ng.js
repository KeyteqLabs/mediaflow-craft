var mediaflow = angular.module('mediaflow', ['angularFileUpload', 'mfImageCrop']);

mediaflow.filter('sizeConverter', function () {
    return function (size, precision) {
        precision = precision || 0;

        if (!size) { return ''; }

        if (isNaN(size)) {
            console.log('Error: Not a number.');
            return '';
        }

        var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        var posttxt = 0;

        if (size < 1024) {
            return Number(size) + " " + sizes[posttxt];
        }
        while (size >= 1024) {
            posttxt++;
            size = size / 1024;
        }

        var power = Math.pow(10, precision);
        var poweredVal = Math.ceil(size * power);

        size = poweredVal / power;

        return size + " " + sizes[posttxt];
    }
});

mediaflow.controller('MediaFlowCtrl', function ($scope, $http, $upload) {
    $scope.connection = true;
    $scope.media = [];
    $scope.spin = true;
    $scope.searchText = '';
    $scope.testConnection = function () {
        $http.get('/admin/mediaflow/check').success(function (result) {
            $scope.connection = result;
        });
    };
    $scope.testConnection();

    $scope.getMedia = function (search) {
        var url = '/admin/mediaflow/media';
        var config = {};
        if (search) config.params = {q: search};
        return $http.get(url, config).success(function(response) {
            return response.map(function(i) {
                i.uploaded = new Date(i.uploaded * 1000);
                return i;
            });
        });
    };
});


mediaflow.directive('showFocus', ['$timeout', function($timeout) {
    return function(scope, element, attrs) {
        scope.$watch(attrs.showFocus, function(showing) {
            $timeout(function() {
                showing && element[0].focus();
            });
        }, true);
    };
}]);

mediaflow.controller('MediaFlowFieldCtrl', function ($scope, $http, $upload) {
    $scope.showMedia = false;
    $scope.selected = false;

    var updateMedia = function(search) {
        $scope.spin = true;
        $scope
            .getMedia(search)
            .success(function(results) {
                $scope.media.splice
                    .bind($scope.media, 0, $scope.media.length - 1)
                    .apply($scope.media, results);
                $scope.spin = false;
            });
    };
    $scope.triggerUpdate = function() {
        updateMedia();
    };

    var timeout;
    $scope.$watch('searchText', function(searchText, ov) {
        if (searchText !== ov) {
            if (timeout) clearTimeout(timeout);
            timeout = setTimeout(updateMedia, 250, searchText);
        }
    });

    $scope.select = function(medium) {
        $scope.selected = medium;
        $scope.showMedia = false;
    };

    $scope.triggerFileSelect = function($event) {
        var $target = $event.currentTarget;
        if (!$target) { return; }
        var $parent = $target.parentNode;
        if (!$parent) { return; }
        var $el = $parent.querySelector('[type="file"]');
        if (!$el) { return; }
        $el.click();
    };

    $scope.onFileSelect = function($files) {
        $scope.spin = true;
        for (var i = 0; i < $files.length; i++) {
            var $file = $files[i];
            $scope.upload = $upload.upload({
                url: '/admin/mediaflow/upload',
                file: $file
            }).then(function(args) {
                $scope.media.unshift(args.data);
                $scope.spin = false;
            }, function(args) {
                console.log('err', args);
                $scope.spin = false;
            });
        }
    }
});

mediaflow.controller('MediaFlowBrowseCtrl', function ($scope, $http, $upload) {
    $scope.view = 'list';

    var updateMedia = function(search) {
        $scope.spin = true;
        $scope
            .getMedia(search)
            .success(function(results) {
                $scope.media.splice
                    .bind($scope.media, 0, $scope.media.length - 1)
                    .apply($scope.media, results);
                $scope.spin = false;
            });
    };
    updateMedia();

    var timeout;
    $scope.$watch('searchText', function(searchText, ov) {
        if (searchText !== ov) {
            if (timeout) clearTimeout(timeout);
            timeout = setTimeout(updateMedia, 250, searchText);
        }
    });

    $scope.onFileSelect = function($files) {
        $scope.spin = true;
        for (var i = 0; i < $files.length; i++) {
            var $file = $files[i];
            $scope.upload = $upload.upload({
                url: '/admin/mediaflow/upload',
                file: $file
            }).then(function(args) {
                $scope.media.unshift(args.data);
                $scope.spin = false;
            }, function(args) {
                console.log('err', args);
                $scope.spin = false;
            });
        }
    }
});
