var app = angular.module('app');

var AdItemController = function(adItemService){
	// this.links = "this should be a bunch of links";
	this.adItemService = adItemService;
};

AdItemController.prototype.display = function(url){
	var _this = this;
	_this.adItemService.Crawl(url)
		.then(function(response){
			_this.links = response.data;
		});

    _this.adItemService.Templates()
        .then(function(response){
            _this.banner = response.data;
        });
}

app.controller('adItemController', ['adItemService', AdItemController]);
