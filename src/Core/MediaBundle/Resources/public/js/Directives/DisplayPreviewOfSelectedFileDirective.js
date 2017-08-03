App.directive('displayPreviewOfSelectedFile', ['$window', function ($window) {
        var helper = {
            support: !!($window.FileReader && $window.CanvasRenderingContext2D),
            isFile: function (item) {
                return angular.isObject(item) && item instanceof $window.File;
            },
            isImage: function (file) {
                var type = '|' + this.getExtension(file) + '|';
                return '|jpg|png|jpeg|bmp|gif|'.indexOf(type) !== -1;
            },
            getExtension: function (file) {
                return file.type.slice(file.type.lastIndexOf('/') + 1);
            },
            getImage: function (file, src) {
                return src;
            }
        };
        return {
            restrict: 'A',
            template: '<canvas>',
            link: function (scope, element, attributes) {
                if (!helper.support)
                    return;
                var params = scope.$eval(attributes.displayPreviewOfSelectedFile);
                if (!helper.isFile(params.file))
                    return;
                var canvas = element.find('canvas');
                var reader = new FileReader();
                reader.onload = onLoadFile;
                reader.readAsDataURL(params.file);
                function onLoadFile(event) {
                    var img = new Image();
                    img.onload = onLoadImage;
                    img.src = helper.getImage(params.file, event.target.result);
                }

                function onLoadImage() {
                    var params = scope.$eval(attributes.displayPreviewOfSelectedFile);

                    var width = (!helper.isImage(params.file)) ? this.width : $('.modal-content').innerWidth() * 0.47;
                    var height = this.height / this.width * width;


                    canvas.attr({width: width, height: height});
                    canvas[0].getContext('2d').drawImage(this, 0, 0, width, height);
                }
            }
        };
    }]);

