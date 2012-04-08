if (undefined == U.CtrViewer){
	U.CtrViewer = new function(){}();
}

U.CtrViewer.Selector = function(){
	function initialize(){
		$('#select_route_select').bind('change', function(){ U.Common.trigger(this, 'ctr_viewer.selector.change')});
	}
	
	return {
		initialize: initialize
	}
}();

//----------------------------------
$(document).ready( function () {
	U.CtrViewer.Selector.initialize();
	$('fieldset').corner();
});