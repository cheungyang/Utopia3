if (undefined == U.CtrViewer){
	U.CtrViewer = new function(){}();
}

U.CtrViewer.Configuration = function(){
	function initialize(){		
		U.Common.listen('ctr_viewer.selector.change.loaded', U.CtrViewer.Configuration.reset_submit);
		U.Common.listen('ctr_viewer.selector.change.loading', U.CtrViewer.Configuration.show_loading);
		reset_submit();
	}
	
	function reset_submit(){
		$('#submit_query').bind('click', function(){ U.Common.trigger(this, 'ctr_viewer.configuration.submit')});
		//$('#route_configuration_loading').css('display', 'none');
		$('#div_configuration fieldset').corner();
		$('#submit_query').corner();		
	}
	
	function show_loading(){
		$('#div_configuration form').css('display', 'none');
		$('#route_configuration_loading').css('display', 'block');
	}
	
	return {
		initialize: initialize,
		reset_submit: reset_submit,
		show_loading: show_loading
	}
}();

//----------------------------------
$(document).ready( function () {
	U.CtrViewer.Configuration.initialize();
});