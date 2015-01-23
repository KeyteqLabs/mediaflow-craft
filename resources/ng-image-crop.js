angular.module('mfImageCrop', []).directive('mfImageCrop', function() {
    var objectify = function(data) {
        var result = {};
        if (typeof data === 'string') {
            try { result = JSON.parse(data); }
            catch (e) {}
        }
        return result;
    };
    return {
        templateUrl: '/admin/resources/mediaflow/ng-image-crop.html',
        scope: {
            selected: '='
        },
        controller: function($scope) {
            var ctrl = this;
            ctrl.current = {name:null,size:[]}
            ctrl.stopPropagation = function(e) {
                e.preventDefault();
                e.stopPropagation();
            }
            ctrl.saveCrop = function(e,s,c) {
                ctrl.stopPropagation(e);
                var selection = [c.x, c.y, c.w, c.h].map(Math.round);
                if (!$scope.selected.version) {
                    $scope.selected.version = {};
                }
                $scope.selected.version[ctrl.current.name] = {
                    coords: selection,
                    width: ctrl.current.size[0],
                    height: ctrl.current.size[1]
                }
                // Rawr
                $scope.$apply();
            }
            ctrl.defaultOptions = function(w,h) {
                return {
                    aspectRatio: w/h,
                    minSize: [w,h]
                }
            };
        },
        link: function($scope, $element, attrs, ctrl) {
            $scope.versions = objectify(attrs.versions);
            if (!('version' in $scope.selected)) {
                $scope.selected.version = {};
            }

            var onInitialize = function() {
                this.container.on('cropstart cropmove', ctrl.stopPropagation);
                this.container.on('cropend', ctrl.saveCrop);
                // TODO PR to Jcrop 2.x to fucking fix this
                this.container.find('button').attr('type', 'button');
            };

            var $crop = $($element[0]).find('.cropper img');
            var setCrop = function(selection, size) {
                var width = size[0], height = size[1];
                $crop.Jcrop({
                    aspectRatio: width / height,
                    boxWidth: 600,
                    boxHeight: 400,
                    setSelect: (selection || [0, 0, width, height])
                }, onInitialize);
            };

            $scope.crop = function(name) {
                var size = ctrl.current.size = $scope.versions[name];
                $scope.active = ctrl.current.name = name;
                var crops = $scope.selected.version;
                var selection = crops && name in crops ? crops[name].coords : null;
                setCrop(selection, size);
                setCrop(selection, size);
            }
            setTimeout(function() {
                for (var version in $scope.versions) {
                    $scope.crop(version);
                    break;
                }
            },100);
        }
    };
});
