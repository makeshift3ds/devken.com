
function setAccordionNav() {
	var cords = $(document.body).getElements('*[accordion]');	// get all the navigations (can have more than one) defined by accordion='true'
	cords.each(
		function (el,i) {
			var subs = el.getElements('*[toggler]');	// get all the subnavigations
			subs.each(
				function(el,i2) {
					var pos = null;
					var t = el.getElements('.'+el.get('toggler'));	// get the subnav togglers
					var p = el.getElements('.'+el.get('panes'));	// get the subnav elements
					pos = t[0].get('pos');
					var n = new Accordion(el, t, p,{
							opacity: false,
							onActive: function(toggler, element){
								active_accordion = this;
								toggler.addClass('toggler1_selected');
							},
							onBackground: function(toggler, element){
								toggler.removeClass('toggler1_selected');
							},
							alwaysHide : true,
							display: null
					});
					subnavs.push(n);
				}
			)

			var t = el.getElements('.'+el.get('toggler'));
			var p = el.getElements('.'+el.get('panes'));
			mainnav = new Accordion(el, t, p, {
					opacity: false,
					onActive: function(toggler, element){
					},
					onBackground: function(toggler, element){
						element.setStyle('height',element.getSize().y);
					},
					onComplete:function(toggler, element){
						this.elements[this.previous].setStyle('height','auto');
						if($chk(active_accordion)) {
							active_accordion.display(null);		// hide the last child subnav to be opened
						}
					},
					display: null
			});
			el.setStyle('display','block');
		}
	);
}