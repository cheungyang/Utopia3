U.CtrViewer = function(){
	function initialize(){
		U.Common.listen('ctr_viewer.selector.change', U.CtrViewer.load_configuration);		
		U.Common.listen('ctr_viewer.configuration.submit', U.CtrViewer.execute_route);
	}
	
	function load_configuration(obj){
		$.ajax({
		    url: 'ctr_viewer/module/route_configuration/'+obj.value+'?jscss=0',
		    type: 'GET',
		    data: '',
		    dataType: 'html',
		    success: function(data){ 				
				$('#mod_configuration').html(data);
				U.Common.trigger(this, 'ctr_viewer.selector.change.loaded');
			},
			beforeSend: function(){
				U.Common.trigger(this, 'ctr_viewer.selector.change.loading');
			},
			error: function(){}
		});
	}
	
	function execute_route(obj){
		var method = $('#mod_configuration #form_configuration_method #submit_method')[0].value;	
		var url = "";
		$('#mod_configuration #form_configuration_url').find(':input').each(function(i){
			url = url+this.value+"/";			
		});
		var params = $('#mod_configuration #form_configuration_params').serialize(); 
		
		$.ajax({
		    url: 'ctr_viewer/module/route_execution?jscss=0',
		    type: 'POST',
		    data: 'url='+url+'&method='+method+'&params='+params,
		    dataType: 'html',
		    success: function(data){ 				
				$('#mod_execution').html(data);
				U.Common.trigger(this, 'ctr_viewer.execution.execute.loaded');
			},
			beforeSend: function(){
				U.Common.trigger(this, 'ctr_viewer.execution.execute.loading');
			},
			error: function(){}
		});
	}

	return {
		initialize: initialize,
		load_configuration: load_configuration,
		execute_route: execute_route
	}
}();

//----------------------------------
$(document).ready( function () {
	U.CtrViewer.initialize();
	$('#hd').corner();
	$('#div_sidebar').corner();
});