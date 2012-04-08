U = new function(){}();

U.Common = function(){
	var events = [];
	
	function event_listen(event_name, target_function){
		if ($.isArray(events[event_name])){
			events[event_name].push(target_function);
		} else {
			events[event_name] = [target_function];
		}
		//console.debug('listening '+event_name);
	}
	
	function event_trigger(obj, event_name){
		if ($.isArray(events[event_name])){
			for(i in events[event_name]) {
				//console.debug('triggering '+event_name+' func '+events[event_name][i]);
				events[event_name][i](obj);
			}
		} else {
			//console.debug('triggering '+event_name+', but no potential target');
		}
	}
	
	return {
		listen: event_listen,
		trigger: event_trigger
	}
}();