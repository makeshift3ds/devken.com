/**
*	Dependences
*
*	Request.HTML
*	DatePicker
*	Accordian
*	SqueezeBox
* 	FancyUpload
* 	Fx.Scroll
* 	Fx.Transitions
*	ZebraTables
*	MooScroller
*   Assets
* 	MooTools.Lang
*/

function setSqueezeBox() {
	SqueezeBox.initialize({
			sizeLoading: {x: 200, y: 150},
			overlayOpacity: 0.3
	});

	SqueezeBox.assign($$('a.image_lightbox'));

	$$('a.ajax_lightbox').each(
		function(el,i) {
			el.addEvent('click',function() {
				SqueezeBox.open(el.get('href'), {
					size: {x: el.get('lbx'), y: el.get('lby')}
				});
				return false;
			});
		}
	);

	$$('a.iframe_lightbox').each(
		function(el,i) {
			SqueezeBox.assign(el, {
					size: {x: el.get('lbx'), y: el.get('lby')},
					handler: 'iframe'
			});
		}
	);
}


/**
* create a tab structure -
*/
function setTabs() {
	var tabs = $$('*[tab]');		// get all the tab links
	var selected_tab = null;		// the currently selected tab

	var selected_tabs = new Hash();
	var transitions = new Array();

	// go through all the found tabs
	if(tabs.length > 0) {
		tabs.each(
			function(el) {
				if(!el.getProperty('default_tab')) {
					if(el.getProperty('tab_style') == 'fade') {
						$(el.getProperty('tab')).setStyle('opacity',0);
					} else {
						$(el.getProperty('tab')).setStyle('display','none');		// if not hide the contents (though they should be hidden using css already)
					}
				} else {
					selected_tabs.set(el.get('tab_group'),el);	// save the selected tab with its tab_group
					$(el.getProperty('tab')).setStyle('display','block');
					$(el.getProperty('tab')).setStyle('opacity',100);
					el.addClass('tab_selected');
				}

				// check for a transition type
				if(el.getProperty('transition')) {
					// these are required for a transition to run properly
					var trans = new Hash();
					trans.set('transition',el.getProperty('transition'));
					trans.set('tab_group',el.getProperty('tab_group'));
					trans.set('transition_speed',el.getProperty('transition_speed'));
					transitions.push(trans);
				}

				el.set(
					{
						'events' : {
							'mouseover' : function() {		// on mouseover set the pointer and give it some style
											this.setStyle('cursor','pointer');
											this.addClass('tab_selected');
										},
							'mouseout' : function() {		// on mouseout set the cursor to normal and return the style (if it is not selected)
											this.setStyle('cursor','normal');
											if(selected_tabs.get(this.getProperty('tab_group')) != this) this.removeClass('tab_selected');
										},
							'click' : function() {			// on click hide the current, show the chosen and add some style
											if(selected_tabs.get(this.getProperty('tab_group')) != this) {
												if(selected_tabs.get(this.getProperty('tab_group'))) {
													if(this.getProperty('tab_style') == 'fade') {
														$(this.getProperty('tab')).setStyle('display','block').fade('in');
														$(selected_tabs.get(this.getProperty('tab_group')).getProperty('tab')).fade('out');
													} else {
														$(this.getProperty('tab')).setStyle('display','block');
														$(selected_tabs.get(this.getProperty('tab_group')).getProperty('tab')).setStyle('display','none');
													}
													this.addClass('tab_selected');
													selected_tabs.get(this.getProperty('tab_group')).removeClass('tab_selected');
												}
												selected_tabs.set(this.getProperty('tab_group'),this);
											}
										}
							}
					});
			});
	}

	if(transitions.length > 0) {
		transitions.each(function(transition_options,i) {
			if(transition_options.get('transition') == 'switch') {
				var tab_links=$$("*[tab_group="+transition_options.get('tab_group') +"]");	// do a periodical for the tab group
				var int=1;
				function nextTab(){
					tab_links[int].fireEvent("click");
					int++;
					if(int==tab_links.length){
						int=0;
					}
				}

				if(tab_links.length > 0) {
					var spd = transition_options.get('transition_speed') ? transition_options.get('transition_speed') : 7000;
					inval=nextTab.create({periodical:spd});
					inval();
				}
			}
		});
	}
}


function setSubnavs() {
	/* associate sublinks with their parents */
	/* example <a href='#' nav='myDivID' />Navigation</a><div id='myDivID'>Sublinks</div> */

	var linkTimer;		// subnav hide timer
	var currentNav;		// last subnav shown
	var shownNav;
	var shownLink;
	var links = $$('ul#admin_links li','.submenus a.tan','.submenus a.blue');	// get the navigation links with defined subnavs

	links.each(			// go through all the found links
		function(el,i) {
			var subnav = $(el.getProperty('subnav'));	// get the id of the subnav from the attribute 'nav'
			el.store('flag','0');

			if(el.get('class') == 'selected') {
				el.store('flag','1');
				shownNav = subnav;
				shownLink = el;
			}

			if(subnav) {
				subnav.store('nav', el);
				el.addEvents(
					{
						'mouseover': function(){
											if(shownLink) {
												if(shownNav) shownNav.hide();
												shownLink.removeClass('selected');
											}
											$clear(linkTimer);					// clear the linkTimer
											if(currentNav) {
												currentNav.hide();		// hide the previous subnav
												if(currentNav.retrieve('nav') != shownNav) currentNav.retrieve('nav').removeClass('selected');
											}
											el.addClass('selected');	// add the selected class
											el.setStyles({'cursor':'pointer'});
											currentNav = subnav;					// set it globally
											subnav.show();						// show the current subnav
									 },
						'mouseout' : function() {
											linkTimer = (
												function(){
													currentNav.hide();
													if(el != shownNav) currentNav.retrieve('nav').removeClass('selected');
													if(shownLink) {
														shownLink.addClass('selected');
														if(shownNav) shownNav.show();
													}
												}
											).delay(250,currentNav);
									 }
					}
				);

				subnav.addEvents(
					{
						'mouseover' : function() {
										$clear(linkTimer);					// clear the timer
									  },
						'mouseout' :  function() {
										if(currentNav && this.retrieve('nav').retrieve('flag') != '1') {
											linkTimer = (
															function(){
																currentNav.setStyle('display','none');
																if(el != shownNav) currentNav.retrieve('nav').removeClass('selected');
																if(shownLink) {
																	shownLink.addClass('selected');
																	if(shownNav) shownNav.show();
																}
															}
														).delay(250,currentNav);
									  }
								  }
					}
				);
			} else {
				el.addEvents(
					{
						'mouseover': function(){
											if(currentNav) {
												currentNav.hide();		// hide the previous subnav
												if(currentNav.retrieve('nav') != shownNav) currentNav.retrieve('nav').removeClass('selected');
											}
											if(shownLink && shownLink != this) {
												linkTimer = (
															function(){
																if(shownNav) shownNav.hide();
																shownLink.removeClass('selected');
															}
														).delay(50,shownNav);
											}
											el.addClass('selected');	// add the selected class
											el.setStyles({'cursor':'pointer'});
									 },
						'mouseout' : function() {
											el.removeClass('selected');
										}
					}
				);
			}
		}
	);
}


var cbCheck;

var ZebraTable = new Class({
		//implements
		Implements: [Options,Events],

		//options
		options: {
			elements: 'table.list-table',
			cssEven: 'even',
			cssOdd: 'odd',
			cssHighlight: 'highlight',
			cssMouseEnter: 'mo'
		},

		//initialization
		initialize: function(options) {
			//set options
			this.setOptions(options);
			//zebra-ize!
			$$(this.options.elements).each(function(table) {
				this.zebraize(table);
			},this);
		},

		//a method that does whatever you want
		zebraize: function(table) {
			//for every row in this table...
			table.getElements('tr').each(function(tr,i) {
				//check to see if the row has th's
				//if so, leave it alone
				//if not, move on
				if(tr.getFirst().get('tag') != 'th') {
					//set the class for this based on odd/even
					var options = this.options, klass = i % 2 ? options.cssEven : options.cssOdd;
					// kenCode - don't change the class if it is preset to highlight
					if(!tr.hasClass(options.cssHighlight)) tr.addClass(klass);
					//start the events!
					if(tr.getProperty('checkbox')) {
						$(tr.getProperty('checkbox')).addEvent('click',function(e) {
							cbCheck = true;
						});
					}
					tr.addEvents({
						//mouseenter
						mouseenter: function () {
							if(!tr.hasClass(options.cssHighlight)) tr.addClass(options.cssMouseEnter).removeClass(klass);
						},
						//mouseleave
						mouseleave: function () {
							if(!tr.hasClass(options.cssHighlight)) tr.removeClass(options.cssMouseEnter).addClass(klass);
						},
						//click
						click: function() {
							//if it is currently not highlighted
							if(!tr.hasClass(options.cssHighlight)) {
								tr.removeClass(options.cssMouseEnter).addClass(options.cssHighlight);
							} else {
								tr.addClass(options.cssMouseEnter).removeClass(options.cssHighlight);
							}
							if(tr.getProperty('checkbox')) {
								if(!cbCheck) $(tr.getProperty('checkbox')).checked = !$(tr.getProperty('checkbox')).checked;
								cbCheck = false;
							}
						}
					});
				}
			},this);
		}
	});

function setToggles() {
	var els = $$('.toggle');
	if($chk(els)) {
		els.each(function(el,i) {
			el.set(
					{
						'events' : {
							'mouseover' : function() {		// on mouseover set the pointer and give it some style
											this.setStyle('cursor','pointer');
										},
							'mouseout' : function() {		// on mouseout set the cursor to normal and return the style (if it is not selected)
											this.setStyle('cursor','normal');
										},
							'click' : function() {			// on click hide the current, show the chosen and add some style
											new Fx.Reveal($(this.getProperty('target'))).toggle();
										}
						}
					}
			);
		});
	}
}
/**
* set default form text values and make them disappear when the form is selected.
*/
function setTextDefaults() {
	var txts = $$('*[default]');
	txts.each(
		function(el,i) {
			var def = el.get('default');	// get the default value
			el.set({'value' : def});
			el.addEvents({
					'focus' : function() {
						if(def == 'password') el.set({'type' : 'password'});
						if(el.get('value') == def) el.set({'value' : ''});
					},
					'blur' : function() {
						if(def == 'password' && el.get('value') == '') el.set({'type' : 'text'});
						if(el.get('value') == '') el.set({'value' : def});
					}
			});
		}
	);
}

function navigationEdit() {
	$('url').addEvent('change',function(e) {
		(this.options[this.selectedIndex].get('value') == 'url') ? $('href').show() : $('href').hide();
	});
	$('url').fireEvent('change');
}

function eventEdit() {
	$('remover').addEvent('change',function(e) {
		(this.checked) ? $('removal_date_container').show() : $('removal_date_container').hide();
	});
	$('remover').fireEvent('change');
}

function setDeletes() {
		$$('a[html^=Delete]').each(function(el) {
				el.addClass('delete_link');
		});
}

function checkIfEmpty(field,error) {
	if($(field).get('value') == '') {
		$(field).setStyles({
			'background-color' : '#e7b6b6'
			});
		return error+'<br />';
	} else {
		$(field).setStyles({
					'background-color' : '#FFF'
			});
		return '';
	}
}

function checkIfNoMatch(var1,var2,error) {
		if($(var1).get('value') == '' && $(var2).get('value') == '') return ''; // both values are empty let someone else catch it
		if($(var1).get('value') != $(var2).get('value')) {
			$(var1).setStyles({'background-color':'#e7b6b6'});
			$(var2).setStyles({'background-color':'#e7b6b6'});
			return error;
		} else {
			$(var1).setStyles({'background-color':'#FFF'});
			$(var2).setStyles({'background-color':'#FFF'});
		}
		return '';
}

function checkRegistration() {
	var err='';
	err += checkIfEmpty('username','Username is required');
	err += checkIfEmpty('email','Email Address is required');
	err += checkIfEmpty('password','Password is required');
	err += checkIfEmpty('password2','Repeat Password is required');
	err += checkIfNoMatch('password','password2','Passwords do not match.');

	if(err) {
		err = '<p>Your submission could not be sent because an error has occured. Please update the following information.</p>'+err;
		var clientError = new Element('div',{'html' : err,'id' : 'client_error'}).inject($(document.body),'top');
		SqueezeBox.open($('client_error'), {
				handler: 'adopt',
				size: {x: $('client_error').getSize().x,y:$('client_error').getSize().y}
			});
		return false;
	}
}

function checkOut() {
	var err='';
	err += checkIfEmpty('firstName','First Name is required');
	err += checkIfEmpty('lastName','Last Name is required');
	err += checkIfEmpty('creditCardType','Credit Card Number is required');
	err += checkIfEmpty('creditCardNumber','Credit Card Number is required');
	err += checkIfEmpty('cvv2Number','Verification Code is required');
	err += checkIfEmpty('address','Billing Address is required');
	err += checkIfEmpty('city','Billing City is required');
	err += checkIfEmpty('state','Billing State/Province/Region is required');
	err += checkIfEmpty('zip','Billing Postal/Zip is required');
	err += checkIfEmpty('shipping_address','Shipping Address is required');
	err += checkIfEmpty('shipping_phone','Shipping Phone is required');
	err += checkIfEmpty('shipping_city','Shipping  City is required');
	err += checkIfEmpty('shipping_state','Shipping  State/Province/Region is required');
	err += checkIfEmpty('shipping_zip','Shipping Postal/Zip is required');

	if(err) {
		err = '<p>Your order  could not be sent because an error has occured. Please update the following information.</p>'+err;
		var clientError = new Element('div',{'html' : err,'id' : 'client_error'}).inject($(document.body),'top');
		SqueezeBox.open($('client_error'), {
				handler: 'adopt',
				size: {x: $('client_error').getSize().x,y:$('client_error').getSize().y}
			});
		return false;
	}
}

// Set tabs to scroll to an overflowed element
function setScrollTabs() {
	var scrollers = $$('*[scroller]');
	if(!scrollers[0]) return;
	var scroller = scrollers[0].get('scroller');
	var current_tab;

	var page_scroll = new Fx.Scroll(scroller, {
		wait: false,
		duration:1000,
		transition: Fx.Transitions.Quad.easeInOut,
		offset: {
			x: -72,
			y: 0
		}
	});

	scrollers.each(function(el,i) {
			el.addEvents({
					'mouseover' : function(e) {
						this.addClass('tab_selected');
					},
					'mouseout' : function(e) {
						if(current_tab != this) this.removeClass('tab_selected');
					},
					'click' : function(e) {
						page_scroll.toElement(this.get('target'));
						//console.log($(el.get('target')).getSize().y);
						var fixHeight = new Fx.Morph($(el.get('target')).getParent(), {duration: 500});
						fixHeight.start({height:$(this.get('target')).getSize().y+160});
						this.addClass('tab_selected');
						if(current_tab) current_tab.removeClass('tab_selected');
						current_tab = this;
					}
			});
			if(i==0) {
				el.fireEvent('click');
			}
	});
}

function setTestimonials() {
	var test_scroll = new Fx.Scroll(scroller, {
		wait: false,
		duration:1000,
		transition: Fx.Transitions.Quad.easeInOut,
		offset: {
			x: -72,
			y: 0
		}
	});
}

function setImageViewer() {
				imageViewer = new Swiff('images/viewers/imageViewer.swf',{
					width: 420,
					height: 234,
					container: $('swfContainer')
				});
				$('clicker').addEvents({
						'click' : function() {
									imageViewer.remote('loadImage','img295.jpg');
								 }
				});
}

function checkAccountInfo() {
		var err='';
		err += checkIfNoMatch('password','password2','Passwords do not match.');
		if(err) {
			err = '<p>Your account information could not be updated because an error has occured. Please update the following information.</p>'+err;
			var clientError = new Element('div',{'html' : err,'id' : 'client_error'}).inject($(document.body),'top');
			SqueezeBox.open($('client_error'), {
					handler: 'adopt',
					size: {x: $('client_error').getSize().x,y:$('client_error').getSize().y}
				});
			return false;
	}
}

var MooSwap = new Class({
	//implements
	Implements: [Options],

	options: {
		imgHoverPrefix: '_hover'
	},

	initialize: function(elements, options) {
		//set options
		this.setOptions(options);
		// Set elements
		this.setSwap(elements);
	},

	setSwap: function(elements) {
		var prefix = this.options.imgHoverPrefix
		// preload images array
		imgTemp = [];
		i = 0;
		$$(elements).each(function(el) {
			var mouseFx = new Fx.Tween(el, {duration: 240, wait: false});
			var holdSrc = el.getProperty('src');
			var extension = holdSrc.substring(holdSrc.lastIndexOf('.'),holdSrc.length);
			var newSrc = holdSrc.replace(extension, prefix + '' + extension);
			// set new image for preloading
			imgTemp[i] = new Asset.image(newSrc, {'alt':el.getProperty('alt'),'id':el.getProperty('id')});
			//imgTemp[i] = new Element('img', {'alt': el.getProperty('alt')}).set('src', newSrc);
			// default link on current img element
			var link = el;

			// check if there is a link a href parent
			var test = el.getParent('a');
			if (test) {
				var link = test.setProperty('id', '__link_' + i);
			}
			link.addEvents({
					mouseover: function() {
					el.setProperty('src', newSrc);
				},
				mouseout: function() {
					el.setProperty('src', holdSrc);
				}
			});

			i++;
		});
	}
});

// cycle carousel (example in  plugins/cycleCarousel)
function setCycleCarousel() {
	$$('div.cycle-carousel').each(function(obj,i){

		// element variables (not valid html) risk/reward ratio
		 duration =  obj.get('duration') ? obj.get('duration') : 500;
		 switchTime =  obj.get('switchTime') ? obj.get('switchTime') : 7000;
		 autoRotation =  obj.get('autoRotation') ? obj.get('autoRotation'): false;
		 stopAfterClick = obj.get('stopAfterClick') ? obj.get('stopAfterClick') : false;

		var _gallery = new cycleCarousel(obj,{
			pagerLinks: 'ul.tab-nav a',
			sliderHolder: 'div.tabs-holder',
			slider: 'ol.tabs',
			slides: 'li',
			nextBtns: '.steps-nav > a',
			duration: duration,
			switchTime: switchTime,
			autoRotation: autoRotation,
			stopAfterClick: stopAfterClick
		});
		_gallery.refreshPager();
		//_gallery.nextSlide();
	});
}

function fireEvent_OnClick(target) {
	if(document.dispatchEvent) { // W3C
		var oEvent = document.createEvent( "MouseEvents" );
		oEvent.initMouseEvent("click", true, true,window, 1, 1, 1, 1, 1, false, false, false, false, 0, target);
		target.dispatchEvent( oEvent );
	}
	else if(document.fireEvent) { // IE
		target.fireEvent("onclick");
	}
}