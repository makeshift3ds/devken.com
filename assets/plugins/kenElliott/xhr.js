/**
* Examples in plugins folder
*
**/
function setXHR(requests) {
	var requests = (requests) ? [requests] : $$('*[xhr_type]');
	requests.each(function(el,i) {

		// get the values from the custom attributes
		var xhr_type = el.getProperty('xhr_type');	// get the type of request (update, delete, insert)
		var trigger = $chk(el.getProperty('trigger')) ? $(el.getProperty('trigger')) : null;
		var success_element = $(el.getProperty('success_element'));
		var fail_element = $(el.getProperty('fail_element'));

		// Do this if the element is a FORM
		if(el.get('tag') == 'form') {
			var url = el.getProperty('action');				// the url from the form
		} else {
		// Do this if the element is anything else
			var hlink = el.getProperty('href').split('&');	// split the url up
			var url = hlink.shift();						// get the host information
			var query = hlink.join('&');					// whats left over is the query string
			trigger = el;									// set the trigger
		}

		trigger.set({
					'events': {
						'click' : function(e){
								htmlRequest(url,success_element,fail_element,query,0,xhr_type,el);
								e.stop();
						}
					}
		});
	}); // end requests.each
}

function htmlRequest(url,success_element,fail_element,query,preload,xhr_type,formElement) {
	if(!url) url = 'http://localhost/ajax';			// default request location
	if(!query) query = formElement.toQueryString();	// get the formElement attributes

	var htmlResponse = new Request.HTML({
							'method' : 'post',
							'url' : url,
							'async' : false
							});
	htmlResponse.addEvents({
					'onRequest' : function() {

					},
					'onSuccess' : function(responseTree,responseElements,responseHTML,responseJS) {
						// on update replace the element with a new copy
						if(xhr_type == 'update') {
							var updateElement = new Element('div',{'html' : responseHTML}).getChildren()[0].replaces($(success_element)); // replace the content with the new content received
							updateElement.getChildren()[0].highlight();
							if(updateElement.getProperty('xhr_type')) setXHR(updateElement);	// reset the ajax abilities for this element
						} else if(xhr_type == 'insert') {
							var updateElement = new Element('div',{'html' : responseHTML}).getChildren()[0].inject($(success_element)); // replace the content with the new content received
							updateElement.getChildren()[0].highlight();
							if(updateElement.getProperty('xhr_type')) setXHR(updateElement);	// reset the ajax abilities for this element
						} else if(xhr_type == 'delete') {
							$(success_element).fade().get('tween').chain(function() { this.element.destroy(); });	// destroy the element
						}
					},
					'onFailure' : function(t,x) {
						$('fail_element').setProperties({
								'html':'Communications failed.',
								'styles' : {
										'display' : 'block'
										}
								});
					}
				});
	htmlResponse.send(query);
}