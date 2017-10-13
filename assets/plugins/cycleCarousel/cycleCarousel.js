// cycle gallery code
var cycleCarousel = new Class({
	options: {
		activeClass: 'active',
		btPrev: 'a.btn-prev',
		btNext: 'a.btn-next',
		pagerLinks: 'ul.tabset li',
		slidesHolder: 'div',
		slider: '.slide',
		slides: 'li',
		nextBtns: '.steps-nav > a',
		switchTime: 3000,
		duration : 2000,
		stopAfterClick:false,
		autoRotation:false
	},

	// create class
	initialize: function(element, options){
		this.setOptions(options);
		var _this = this;

		if (this.options.btNext) this.next = element.getElement(this.options.btNext);
		else this.next = false;

		if (this.options.btPrev) this.prev = element.getElement(this.options.btPrev);
		else this.prev = false;

		if (this.options.pagerLinks) this.pagerLinks = element.getElements(this.options.pagerLinks);
		else this.pagerLinks = false;

		if (this.options.nextBtns) this.nextBtns = element.getElements(this.options.nextBtns);
		else this.nextBtns = false;

		this.animated = false;
		this.holder = element.getElement(this.options.slidesHolder);
		this.slider = this.holder.getElement(this.options.slider);
		this.slides = this.holder.getElements(this.options.slides);
		this.stepWidth = this.slides[0].getSize().x;
		this.switchTime = this.options.switchTime;
		this.activeClass = this.options.activeClass;
		this.autoRotation = this.options.autoRotation;
		this.stopAfterClick = this.options.stopAfterClick;

		// gallery init
		this.duration = this.options.duration;
		this.slideCount = this.slides.length;
		this.oldIndex = 0;
		this.currentIndex = 0;
		this.timer = false;
		this.direction = false;

		// slides init
		this.slider.setStyles({position:'relative',height:this.slider.getSize().y, width:this.stepWidth});
		this.slides.each(function(slide, ind){
			if(this.currentIndex == ind) slide.setStyles({opacity:1,display:'block'});
			else slide.setStyles({opacity:0,display:'none'});
		}.bind(this));

		// mouseover
		_this.addEvent('mouseover', function(){
			if (_this.timer) clearInterval(_this.timer);
		}).addEvent('mouseout', function(){
			_this.autoSlide();
		});

		if(_this.stopAfterClick) {
			$$('body')[0].addEvent('click',function(){
				_this.autoRotation = false;
				clearTimeout(_this.timer);
			});
			window.addEvent('scroll',function(){
				_this.autoRotation = false;
				clearTimeout(_this.timer);
			});
		}

		// gallery control
		if (this.next) {
			this.next.addEvent('click', function(){
				if (!_this.animated) {
					if(_this.stopAfterClick) {
						_this.autoRotation = false;
						clearTimeout(_this.timer);
					}
					_this.nextSlide();
				}
				return false;
			});
		}
		if (this.prev) {
			this.prev.addEvent('click', function(){
				if (!_this.animated) {
					if(_this.stopAfterClick) {
						_this.autoRotation = false;
						clearTimeout(_this.timer);
					}
					_this.prevSlide();
				}
				return false;
			});
		}
		if (this.pagerLinks) {
			this.pagerLinks.each(function(link,ind){
				link.addEvent('click', function(){
					if (!_this.animated && _this.currentIndex!=ind) {

						if(_this.stopAfterClick) {
							_this.autoRotation = false;
							clearTimeout(_this.timer);
						}
						_this.direction = (_this.currentIndex < ind);
						_this.oldIndex = _this.currentIndex;
						_this.currentIndex = ind;
						_this.switchSlide();
					}
					return false;
				});
			});
		}

		if (this.nextBtns) {
			this.nextBtns.each(function(link,ind){
				link.addEvent('click', function(){
					if (!_this.animated) {
						if(_this.stopAfterClick) {
							_this.autoRotation = false;
							clearTimeout(_this.timer);
						}
						_this.nextSlide();
					}
					return false;
				});
			});
		}

		// autoslide
		if (this.options.autoRotation) {
			this.autoSlide();
		}
	},
	nextSlide: function(){
		this.oldIndex = this.currentIndex
		if(this.currentIndex < this.slideCount-1) this.currentIndex++;
		else this.currentIndex = 0;
		this.direction = true;
		this.switchSlide();
	},
	prevSlide: function(){
		this.oldIndex = this.currentIndex
		if(this.currentIndex < this.slideCount-1) this.currentIndex++;
		else this.currentIndex = 0;
		this.direction = false;
		this.switchSlide();
	},
	refreshPager: function() {
		// refresh pagination
		if (this.pagerLinks) {
			this.pagerLinks.each(function(link,ind){
				if(ind != this.currentIndex) {
					link.removeClass(this.activeClass);
				} else {
					link.addClass(this.activeClass);
				}
			}.bind(this));
		}
	},
	switchSlide: function(){
		var _d = (this.direction ? -1 : 1);
		var oldSlide = this.slides[this.oldIndex];
		var newSlide = this.slides[this.currentIndex];

		// fade elements
		oldSlide.removeClass(this.activeClass).setStyles({opacity:1,display:'block'});
		newSlide.addClass(this.activeClass).setStyles({opacity:0,display:'block'});
		var fxFixHeight = new Fx.Morph(this.slider, {duration: this.duration});
		var fxFadeIn = new Fx.Morph(newSlide, {duration: this.duration});
		var fxFadeOut = new Fx.Morph(oldSlide, {duration: this.duration, onComplete:function(){
			oldSlide.setStyles({display:'none'});
			fxFadeIn.start({opacity:1});
		}});
		fxFadeOut.start({opacity:0});
		fxFixHeight.start({height:newSlide.getSize().y});
		this.autoSlide();
		this.refreshPager();
	},
	autoSlide : function(){
		if(this.autoRotation) {
			var _this = this;
			if(this.timer) clearTimeout(this.timer);
			this.timer = setInterval(function(){_this.nextSlide()}, this.options.switchTime);
		}
	},

	// add options and events
	Implements : [Options, Events]
});