// init page
window.addEvent('domready', function() {
	initGallery();
	//tabs();
	//initCycle();
});

// countdown init
function initGallery() {
	if($('show-tab')) {
		$$('div.example .item').each(function(obj,i){
			var _gallery = new fadeGallery(obj,{
				pager1: 'ul.pages a',
				pager2: 'div.switch li',
				slides: 'ul.slideset li',
				autoSlide: false,
				duration : 500
			});
		});
	}
}

// fade gallery module
var fadeGallery = new Class({
	options: {
		pager1: 'div.pager1 .switch-link',
		pager2: 'div.pager2 .switch-link',
		slides: 'ul.slidelist li',
		activeClass: 'active',
		autoSlide: false,
		switchTime: 3000,
		duration : 500
	},

	// create class
	initialize: function(element, options){
		this.setOptions(options);
		var _this = this;
s
		this.slideset = element.getElements(this.options.slides);
		this.slidesCount = this.slideset.length;
		this.pager1 = element.getElements(this.options.pager1);
		this.pager2 = element.getElements(this.options.pager2);
		this.autoRotation = this.options.autoSlide;
		this.activeClass = this.options.activeClass;
		this.switchTime = this.options.switchTime;

		// slides
		this.duration = this.options.duration;
		this.currentIndex = 0;
		this.oldIndex = 0;
		this.timer = false;

		// gallery init
		this.slideset.each(function(_slide, _index){
			if(_index != _this.currentIndex) _slide.removeClass(_this.activeClass).setStyles({display:'none',opacity:0});
			else _slide.addClass(_this.activeClass).setStyles({display:'block',opacity:1});
		});

		// init control
		if(this.pager1.length) {
			this.pager1.each(function(item,ind){
				item.addEvent('click', function(){
					_this.numSlide(ind);
					return false;
				});
			});
		}
		if(this.pager2.length) {
			this.pager2.each(function(item,ind){
				item.addEvent('click', function(){
					_this.numSlide(ind);
					return false;
				});
			});
		}

		// start autoslide
		if (this.options.autoSlide) {
			this.autoSlide();
		}
	},
	refreshPagination:function(){
		var _this = this;
		if(this.pager1.length) {
			this.pager1.each(function(item,ind){
				if(ind == _this.currentIndex) item.addClass(_this.activeClass);
				else item.removeClass(_this.activeClass);
			});
		}
		if(this.pager2.length) {
			this.pager2.each(function(item,ind){
				if(ind == _this.currentIndex) item.addClass(_this.activeClass);
				else item.removeClass(_this.activeClass);
			});
		}
	},
	numSlide: function(ind){
		if(this.currentIndex != ind) {
			this.oldIndex = this.currentIndex;
			this.currentIndex = ind;
			this.switchSlide();
		}
	},
	nextSlide: function(){
		this.oldIndex = this.currentIndex;
		if(this.currentIndex < this.slidesCount-1) this.currentIndex++;
		else this.currentIndex = 0;
		this.switchSlide();
	},
	prevSlide: function(){
		this.oldIndex = this.currentIndex;
		this.currentIndex--;
		if (this.currentIndex < 0) this.currentIndex = this.slidesCount-1;
		this.switchSlide();
	},
	switchSlide:function(){
		var _oldSlide = this.slideset[this.oldIndex].removeClass(this.activeClass).setStyles({opacity:1,display:'block'});
		var _newSlide = this.slideset[this.currentIndex].addClass(this.activeClass).setStyles({opacity:0,display:'block'});
		// fade elements
		var fxFadeOut = new Fx.Morph(_oldSlide, {duration: this.duration, onComplete:function(){_oldSlide.setStyles({display:'none'});}});
		var fxFadeIn = new Fx.Morph(_newSlide, {duration: this.duration});
		fxFadeOut.start({opacity:0});
		fxFadeIn.start({opacity:1});
		this.autoSlide();
		this.refreshPagination();
	},
	autoSlide: function(){
		if(this.autoRotation) {
			var _this = this;
			if(this.timer) clearTimeout(this.timer);
			this.timer = setTimeout(function(){_this.nextSlide()}, this.switchTime);
		}
	},
	// add options and events
	Implements : [Options, Events]
});