function setMediaUploader() {
	if(!$('media-list')) {
			return;
	}
	var mediauploader = new FancyUpload3.Attach('media-list', '#media-attach, #media-attach2',  {
			path: '../assets/plugins/fancyUpload/source/Swiff.Uploader.swf',
			url: 'media_handler.php?mode=upload_media',
			fileSizeMax: 100 * 1024 * 1024,
			multiple: false,
			typeFilter: {
					'Videos (*.flv, *.mp4)' : '*.flv; *.mp4;',
					//'Images (*.jpg, *.gif, *.png, *.jpeg)' : '*.jpg; *.gif; *.png; *.jpeg;',
					//'Documents (*.doc, *.docx, *.pdf, *.xls, *.txt, *.wpd, *.wps, *.csv, *.pps, *.ppt, *.pptx, *.wks, *.xlsx, *.indd, *.pct, *.qxd, *.zip, *.rar, *.sit, *.sitx, *.qxp, *.zipx, *.bin)' : '*.doc; *.docx; *.pdf; *.xls; *.txt; *.wpd; *.wps; *.csv; *.pps; *.ppt; *.pptx; *.wks; *.xlsx; *.indd; *.pct; *.qxd; *.zip; *.rar; *.sit; *.sitx; *.qxp; *.zipx; *.bin;',
					//'Audio (*.mp3, *.aiff, *.wav)' : '*.mp3; *.aiff; *.wav;'
			},
			verbose: false,
			onLoad : function(files) {
				$('media-attach2').destroy();
			},
			onSelectFail: function(files) {
				files.each(function(file) {
					new Element('li', {
						'class': 'file-invalid',
						events: {
							click: function() {
								this.destroy();
							}
						}
					}).adopt(
						new Element('span', {html: file.validationErrorMessage || file.validationError})
					).inject(this.list, 'bottom');
				}, this);
			},

			onFileSuccess: function(file,response) {
				var json = new Hash(JSON.decode(response, true) || {});
				if (json.get('status') == '0') {
					file.ui.element.addClass('file-failed');
					file.ui.element.set('html', '<strong>Error:</strong> ' + (json.get('error') ? (json.get('error')) : response));
					file.ui.element.addEvents({
							'click' : function() {
									this.destroy();
							}
					});
				} else {
					file.ui.element.highlight('#e6efc2');
					var filename = ($('media_filename').get('value') != '') ? $('media_filename').get('value')+','+json.get('filename') : json.get('filename');
					$('media_filename').set('value',filename);
					if($chk($('media-attach'))) $('media-attach').destroy();
				}
			},

			onFileError: function(file) {
				file.ui.cancel.set('html', 'Retry').removeEvents().addEvent('click', function() {
					file.requeue();
					return false;
				});
				new Element('span', {
					html: file.errorMessage,
					'class': 'file-error'
				}).inject(file.ui.cancel, 'after');
			},

			onFileRequeue: function(file) {
				file.ui.element.getElement('.file-error').destroy();
				file.ui.cancel.set('html', 'Cancel').removeEvents().addEvent('click', function() {
					file.remove();
					return false;
				});
				this.start();
			}

	});
		return mediauploader;
}


function setThumbNailUploader() {
	if(!$('thumb-list')) {
			return;
	}
	var thumbloader = new FancyUpload3.Attach('thumb-list', '#thumb-attach, #thumb-attach2',  {
			path: '../assets/plugins/fancyUpload/source/Swiff.Uploader.swf',
			url: 'media_handler.php?mode=upload_thumbnail',
			fileSizeMax: 100 * 1024 * 1024,
			multiple: false,
			typeFilter: {
					'Images (*.jpg, *.gif, *.png, *.jpeg)' : '*.jpg; *.gif; *.png; *.jpeg;',
			},
			verbose: false,
			onLoad : function(files) {
				$('thumb-attach2').destroy();
			},
			onSelectFail: function(files) {
				files.each(function(file) {
					new Element('li', {
						'class': 'file-invalid',
						events: {
							click: function() {
								this.destroy();
							}
						}
					}).adopt(
						new Element('span', {html: file.validationErrorMessage || file.validationError})
					).inject(this.list, 'bottom');
				}, this);
			},

			onFileSuccess: function(file,response) {
				var json = new Hash(JSON.decode(response, true) || {});
				if (json.get('status') == '0') {
					file.ui.element.addClass('file-failed');
					file.ui.element.set('html', '<strong>Error:</strong> ' + (json.get('error') ? (json.get('error')) : response));
					file.ui.element.addEvents({
							'click' : function() {
									this.destroy();
							}
					});
				} else {
					file.ui.element.highlight('#e6efc2');
					var filename = ($('thumb_filename').get('value') != '') ? $('thumb_filename').get('value')+','+json.get('filename') : json.get('filename');
					$('thumb_filename').set('value',filename);
					if($chk($('thumb-attach'))) $('thumb-attach').destroy();
				}
			},

			onFileError: function(file) {
				file.ui.cancel.set('html', 'Retry').removeEvents().addEvent('click', function() {
					file.requeue();
					return false;
				});

				new Element('span', {
					html: file.errorMessage,
					'class': 'file-error'
				}).inject(file.ui.cancel, 'after');
			},

			onFileRequeue: function(file) {
				file.ui.element.getElement('.file-error').destroy();

				file.ui.cancel.set('html', 'Cancel').removeEvents().addEvent('click', function() {
					file.remove();
					return false;
				});

				this.start();
			}

	});
	return thumbloader;
}


function setYoutubeThumbnailUploader() {
	if(!$('youtube-list')) {
			return;
	}
	var youtube = new FancyUpload3.Attach('youtube-list', '#youtube-attach, #youtube-attach2',  {
			path: '../assets/plugins/fancyUpload/source/Swiff.Uploader.swf',
			url: 'media_handler.php?mode=upload_thumbnail',
			fileSizeMax: 100 * 1024 * 1024,
			multiple: false,
			typeFilter: {
					'Images (*.jpg, *.gif, *.png, *.jpeg)' : '*.jpg; *.gif; *.png; *.jpeg;',
			},
			verbose: false,
			onLoad : function(files) {
				$('youtube-attach2').destroy();
			},
			onSelectFail: function(files) {
				files.each(function(file) {
					new Element('li', {
						'class': 'file-invalid',
						events: {
							click: function() {
								this.destroy();
							}
						}
					}).adopt(
						new Element('span', {html: file.validationErrorMessage || file.validationError})
					).inject(this.list, 'bottom');
				}, this);
			},

			onFileSuccess: function(file,response) {
				var json = new Hash(JSON.decode(response, true) || {});
				if (json.get('status') == '0') {
					file.ui.element.addClass('file-failed');
					file.ui.element.set('html', '<strong>Error:</strong> ' + (json.get('error') ? (json.get('error')) : response));
					file.ui.element.addEvents({
							'click' : function() {
									this.destroy();
							}
					});
				} else {
					file.ui.element.highlight('#e6efc2');
					var filename = ($('youtube_filename').get('value') != '') ? $('youtube_filename').get('value')+','+json.get('filename') : json.get('filename');
					$('youtube_filename').set('value',filename);
					if($chk($('youtube-attach'))) $('youtube-attach').destroy();
				}
			},

			onFileError: function(file) {
				file.ui.cancel.set('html', 'Retry').removeEvents().addEvent('click', function() {
					file.requeue();
					return false;
				});

				new Element('span', {
					html: file.errorMessage,
					'class': 'file-error'
				}).inject(file.ui.cancel, 'after');
			},

			onFileRequeue: function(file) {
				file.ui.element.getElement('.file-error').destroy();

				file.ui.cancel.set('html', 'Cancel').removeEvents().addEvent('click', function() {
					file.remove();
					return false;
				});

				this.start();
			}

	});
	return youtube;
}