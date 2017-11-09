$.reports.salesfeaturesAction = function (params) {
    this.setActiveTop('salesfeatures');
    $("#reportscontent").load('?plugin=salesfeatures' + this.getTimeframeParams() + (params ? '&' + params : ''));
}