import flash.external.*;
import com.mosesSupposes.fuse.*;
ZigoEngine.simpleSetup(Shortcuts,Fuse,PennerEasing,FuseFMP);

base_url = 'http://173.203.95.74/images/product_images/page/';	// production
//base_url = '../product_images/page/';	// testing

loader_mc._alpha=0;
loading_bar._alpha=0;

ExternalInterface.addCallback("loadImage",this,loadImage);

function loadClip(clip,loader_mc,debug) {
	var mcLoader:MovieClipLoader = new MovieClipLoader();
	var listener:Object = new Object();
	listener.onLoadStart = function(target:MovieClip) {
		if(debug) trace('***Load Start - Clip:'+clip+' - Loader :'+loader_mc);
		loading_bar._width = 1;
		loading_bar.fadeIn();
		loader_mc.fadeOut();
	};
	listener.onLoadProgress = function(target:MovieClip, bytesLoaded:Number, bytesTotal:Number):Void  {
		var percent:Number = Math.round(bytesLoaded*100/bytesTotal);
		if(debug) trace('* Percent: '+percent+'%');
		loading_bar._width = percent*2;
		//loading_text.text = percent+"%";
	};
	listener.onLoadInit = function(target:MovieClip) {
		if(debug) trace('***Load Complete- Clip:'+clip+' - Loader :'+loader_mc);
		loader_mc.fadeIn();
		loading_bar.fadeOut();
	};
	mcLoader.addListener(listener);
	mcLoader.loadClip(clip,loader_mc);
}

function loadImage(clip) {
	loadClip(base_url+clip,loader_mc);
	// testText.text = clip;
}

loadImage(imgSrc,loader_mc);