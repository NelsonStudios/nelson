define(['jquery'], function($) {

/**
 * Lazy load for images and BGs
 * @param $lazys - all .js-lazy elements to load
 * @param complete - function executed when all images loaded (default no nothing)
 * @param wait - if true only wait state will be added, no actual load (default to false)
 * @constructor
 */
LazyLoad = function ( $lazys, complete, wait ) {
	var _this = this;
	// Set default values
	if ( typeof $lazys === 'undefined' ) {
		$lazys = $('.js-lazy');
	}
	if ( !complete ) {
		complete = function () {}
	}
	if ( typeof wait === 'undefined' ) {
		wait = false;
	}
	this.wait = wait;
	this.$lazy = $lazys.not('.js-lazy-processed');

	this.complete = complete;
	this.need_to_load = this.$lazy.length;
	this.loaded = 0;

	// Bind resize handler
	$(window).resize(function () {
		_this.on_resize ();
	});

	// Add class
	if ( !this.wait ) {
		this.$lazy.addClass('js-lazy-processed');
	}

	// Add wait state
	this.load_wait();
	this.on_resize();
	if ( !wait ) {
		// Begin to load
		//this.load_next();
		this.load_all ();
	}
};

/**
 * Window resize handler
 */
LazyLoad.prototype.on_resize = function () {
	var _this = this;
	this.$lazy.each(function () {
		var $this = $(this);
		var w = parseInt($this.data('width'));
		var h = parseInt($this.data('height'));
		if ( w && h ) {
			if ( $this.hasClass('js-lazy-cover')) {
				// Handle cover images
				var cw = $this.parent().width();
				var ch = $this.parent().height();
				var offs = $this.parent().offset();
				var fw, fh, px, py;
				if ( w/h > cw/ch ) {
					//console.log('portrait');
					// portrait
					fh = ch;
					fw = w * fh / h;
					py = 0;
					px = ( cw - fw ) / 2;
				} else {
					//console.log('landscape');
					// landscape
					fw = cw;
					fh = h * fw / w;
					px = 0;
					py = ( ch - fh ) / 2;
				}
				px += offs ? offs.left : 0;
				py += offs ? offs.top : 0;
				//console.log(w,h,fw,fh,px,py);
				$(this).css('background-size', ''+fw+'px '+fh+'px');
				$(this).css('background-position', ''+Math.round(px)+'px '+Math.round(py)+'px');
			}
		}
		if ( $this.data('video') ) {
			var ratio = $this.data('video-ratio');
			if ( ratio ) {
				var $video = $this.find('video');
				if ( $video.length ) {
					var h = $this.height();
					var w = $this.width();
					if ( w / h > ratio ) {
						$video.width(w);
						$video.height(w / ratio);
					} else {
						$video.height(h);
						$video.width(h * ratio);
					}
				}
			}
		}
		// Resize preload state img
		if ( $this.is('img') ) {
			_this.image_resize ( $this );
		}
	});
};

/**
 * Resize preload state image for single image
 * @param $img - jQuery element with image
 */
LazyLoad.prototype.image_resize = function ( $img ) {
	if ( !$img.hasClass('js-lazy-loaded') ) {
		// If not loaded
		$img.css('height','auto');
		var w = parseInt($img.attr('width'));
		var h = parseInt($img.attr('height'));
		setTimeout(function(){
			$img.attr ( 'style', 'height:' + ( h * $img.width() / w ) + 'px !important' );
		},1);
	} else {
		$img.removeAttr('style');
	}
};

/**
 * Set wait state to all Lazy images (throbber)
 */
LazyLoad.prototype.load_wait = function () {
	//console.log ( 'lazy wait' );
	var _this = this;
	this.$lazy.not('.js-lazy-wait').not('.js-lazy-silent').each(function () {
		var $this = $(this);
		$this.addClass('js-lazy-wait');
		if ( $this.is('img') ) {
			$this.attr('src', 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
			_this.image_resize ( $this );
			$this.parent().append($('<div class="wait"></div>'));
		} else {
			$this.parent().append('<div class="wait"></div>');
		}
	});
	$(window).resize();
};

LazyLoad.prototype.load_all = function () {
	var _this = this;
	this.$lazy.each(function() {
		var $next = $(this);
		var bg = $next.data('bg');
		var video = $next.data('video');
		if ( bg ) {
			var IMG = new Image();
			IMG.onload = function () {
				//_this.on_resize ();
				//$next.finish().animate({ opacity: 0 }, { duration: 400, complete: function () {
				$next.finish().fadeOut(400, function(){
					if ( $next.is('img') ) {
						$next.attr( 'src', bg );
					} else {
						$next.css( 'background-image', 'url('+bg+')' );
					}
					//_this.on_resize ();
					//$next.stop().animate({ opacity: 1 }, { duration: 400, complete: function () {
					$next.finish().fadeIn(400, function(){
						$next.addClass('js-lazy-loaded');
						$next.removeAttr('style');
						if ( !$next.is('img') ) {
							$next.css( 'background-image', 'url('+bg+')' );
						}
						$(window).resize();
					});
					if ( video ) {
						var r = new XMLHttpRequest();
						r.onload = function() {
							//_this.un_wait ( $next );
							$next.addClass('js-lazy-video-loaded');
							$next.finish();
							$video[0].src = URL.createObjectURL(r.response);
							$video[0].play();
							setTimeout(function(){
								$video.fadeIn(300);
							},300);

							$(window).resize();

							//_this.loaded++;
							//_this.load_next();
						};
						var loop = !$next.hasClass('js-lazy-video-no-loop');
						//var fade_on_end = $next.hasClass('js-lazy-video-fade-on-end');
						var $video = $('<video style="display: none"'+(loop?' loop':'')+' muted></video>');
						$video.hide();
						$next.append($video);
						r.open("GET", video);
						r.responseType = "blob";
						r.send();
					}// else {
						_this.un_wait ( $next );
					//}
					//_this.loaded++;
					//_this.load_next();
				});
				//console.log ( 'lazy loaded', _this.loaded+1, _this.need_to_load );
				//if ( !video ) {
				_this.loaded++;
				/*setTimeout(function() {
					_this.load_next();
				}, 50 );*/
				//}
			};
			IMG.onerror = function () {
				console.log ( 'lazy error', _this.loaded );
				$next.addClass('js-lazy-loaded').addClass('js-lazy-loaded-error');
				$(window).resize();
				_this.un_wait ( $next );
				_this.loaded ++;
				_this.load_next();
			};
			IMG.src = bg;
		} else {
			_this.un_wait ( $next );
			/*setTimeout ( function () {
				_this.load_next();
			}, 50 );*/
		}
	});
};

/**
 * Load next image
 */
LazyLoad.prototype.load_next = function () {
	var _this = this;
	if ( this.need_to_load <= this.loaded ) {
		//console.log ( 'lazy complete' );
		this.complete();
		return;
	}
	//console.log ( 'lazy next', this.loaded+1, this.need_to_load );

	var $next = this.$lazy.eq(this.loaded);
	var bg = $next.data('bg');
	var video = $next.data('video');
	if ( bg ) {
		var IMG = new Image();
		IMG.onload = function () {
			//_this.on_resize ();
			//$next.finish().animate({ opacity: 0 }, { duration: 400, complete: function () {
			$next.finish().fadeOut(400, function(){
				if ( $next.is('img') ) {
					$next.attr( 'src', bg );
				} else {
					$next.css( 'background-image', 'url('+bg+')' );
				}
				//_this.on_resize ();
				//$next.stop().animate({ opacity: 1 }, { duration: 400, complete: function () {
				$next.finish().fadeIn(400, function(){
					$next.addClass('js-lazy-loaded');
					$next.removeAttr('style');
					if ( !$next.is('img') ) {
						$next.css( 'background-image', 'url('+bg+')' );
					}
					$(window).resize();
				});
				if ( video ) {
					var r = new XMLHttpRequest();
					r.onload = function() {
						_this.un_wait ( $next );
						$next.addClass('js-lazy-video-loaded');
						$next.finish();
						$video[0].src = URL.createObjectURL(r.response);
						$video[0].play();
						setTimeout(function(){
							$video.show();
						},150);

						$(window).resize();

						//_this.loaded++;
						//_this.load_next();
					};
					var loop = !$next.hasClass('js-lazy-video-no-loop');
					//var fade_on_end = $next.hasClass('js-lazy-video-fade-on-end');
					var $video = $('<video style="display: none"'+(loop?' loop':'')+' muted></video>');
					$video.hide();
					$next.append($video);
					r.open("GET", video);
					r.responseType = "blob";
					r.send();
				} else {
					_this.un_wait ( $next );
				}
				//_this.loaded++;
				//_this.load_next();
			});
			//console.log ( 'lazy loaded', _this.loaded+1, _this.need_to_load );
			//if ( !video ) {
				_this.loaded++;
				setTimeout(function() {
					_this.load_next();
				}, 50 );
			//}
		};
		IMG.onerror = function () {
			console.log ( 'lazy error', _this.loaded );
			$next.addClass('js-lazy-loaded').addClass('js-lazy-loaded-error');
			$(window).resize();
			_this.un_wait ( $next );
			_this.loaded ++;
			_this.load_next();
		};
		IMG.src = bg;
	} else {
		this.un_wait ( $next );
		setTimeout ( function () {
			_this.load_next();
		}, 50 );
	}
};

LazyLoad.prototype.un_wait = function ( $lazy ) {
	$lazy.parent().find('.wait').fadeOut(400, function () {
		$(this).remove();
	});
};

$.fn.lazy_load = function ( complete ) {
	if ( typeof complete !== 'function' ) {
		complete = function () {};
	}
	var lazy = new LazyLoad ( this, complete, false );
	return this;
};
$.fn.lazy_wait = function () {
	var lazy = new LazyLoad ( this, function() {}, true );
	return this;
};

});