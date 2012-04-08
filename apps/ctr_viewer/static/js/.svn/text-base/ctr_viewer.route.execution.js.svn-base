if (undefined == U.CtrViewer){
	U.CtrViewer = new function(){}();
}

U.CtrViewer.Execution = function(){
	function initialize(){		
		U.Common.listen('ctr_viewer.execution.execute.loading', U.CtrViewer.Execution.show_loading);
		U.Common.listen('ctr_viewer.execution.execute.loaded', U.CtrViewer.Execution.reset_loaded);		
		reset_loaded();
	}
	
	function reset_loaded(){
		$('#div_exec_query').corner();	
		$('#route_exec_loading').css('display', 'none');
	}
	
	function show_loading(){
		$('#div_exec_query').css('display', 'none');
		$('#div_exec_return').css('display', 'none');
		$('#div_exec_debug').css('display', 'none');
		$('#route_exec_loading').css('display', 'block');
	}
	
	return {
		initialize: initialize,
		reset_loaded: reset_loaded,
		show_loading: show_loading
	}
}();

//----------------------------------
$(document).ready( function () {
	U.CtrViewer.Execution.initialize();
});