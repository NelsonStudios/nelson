define(['jquery', 'lazy', 'jquery.transform', 'jquery.bez'], function($) {
/**
 * FeconSocialWidget
 * @param $wrap - .fecon-social-widget jQuery selector. Not .fsw-initialized
 * @constructor
 */
FeconSocialWidget = function ( $wrap ) {
	var _this = this;

	this.$wrap = $wrap;
	this.feed_url = this.$wrap.data('feed-url');	// URL for JSON data endpoint (will be loaded using AJAX)
	this.feed = [];		// Feed items (will be populated from JSON endpoint above)
	this.per_frame = 3;	// Items per frame
	if ( this.$wrap.hasClass('fsw-instagram')) {
		// 5 items per frame for Instagram widget
		this.per_frame = 5;
	}
	this.frame_current = -1;	// Current frame cursor (-1 - starting point)
	this.delayed_frame_set = null;	// Used to store user slider (prev/next) commands while widget is animating

	// Fill widget with default HTML code
	this.html_init();

	// Cache main elements
	this.$rotator = this.$wrap.find('.fsw-rotator');
	this.$slider = this.$wrap.find('.fsw-slider');
	this.$cta_more = this.$wrap.find('.fsw-cta-more');
	this.$cta_prev = this.$wrap.find('.fsw-cta-prev');
	this.$cta_close = this.$wrap.find('.fsw-cta-close');

	// Click on any preview item
	this.$wrap.on('click', '.fsw-item-preview', function () {
		// Run special handler
		_this.on_click_preview ( $(this) );
	});
	// Click on Close CTA
	this.$cta_close.on('click', function () {
		// If widget isn't animating
		if ( !_this.$wrap.hasClass('fsw-animating') || !_this.$wrap.hasClass('fsw-animating-slider') ) {
			// Close details block
			_this.close();
		}
	});
	// Click on More CTA
	this.$cta_more.on('click', function () {
		// Go to next slide
		_this.frame_next();
	});
	// Click on Prev CTA
	this.$cta_prev.on('click', function () {
		// Go to prev slide
		_this.frame_prev();
	});

	// Add initialized class to prevent double initialization
	this.$wrap.addClass('fsw-initialized');

	// Load feed data via AJAX
	this.feed_load();
};

/**
 * Special Click handler for all details blocks
 * @param $preview
 */
FeconSocialWidget.prototype.on_click_preview = function ( $preview ) {
	// If widget isn't animating
	if ( !this.$wrap.hasClass('fsw-animating') || !this.$wrap.hasClass('fsw-animating-slider') ) {
		// Get item
		var $item = $preview.closest('.fsw-item');
		// Execute open command for item
		this.open($item);
	}
};

/**
 * Fills widget with default HTML
 */
FeconSocialWidget.prototype.html_init = function () {
	this.$wrap.html('' +
		'<div class="fsw-rotator">' +
		'<div class="fsw-slider"></div>' +
		'<div class="fsw-cta fsw-cta-more" title="Show More" style="display: none;"></div>' +
		'<div class="fsw-cta fsw-cta-prev" title="Show Previous" style="display: none;"></div>' +
		'<div class="fsw-cta fsw-cta-close" title="Close" style="display: none;"></div>' +
		'</div>'
	);
};
/**
 * Create Frame(Slide) HTMl
 * @param frame - frame index
 * @returns {string}
 */
FeconSocialWidget.prototype.html_frame = function ( frame ) {
	// Get feed items start and end indexes for this frame, according to per_page value
	var i_start = frame * this.per_frame;
	var i_end = i_start + this.per_frame;
	// Frame/Slide wrapper
	var html = '<div class="fsw-screen fsw-screen-'+frame+'">';
	// In frame index counter
	var j = 0;
	// Loop through feed items of this frame
	for ( var i = i_start; i < i_end; i ++ ) {
		j ++;
		// Get feed item object
		var item = this.feed[i];
		// If there's no item
		if ( typeof item == 'undefined' ) {
			continue;
		}
		// Create feed item HTML
		html += '' +
			'<div class="fsw-item fsw-delta-'+j+' fsw-type-yt">' +
				'<div class="fsw-item-preview" title="'+item.title+'">' +
					'<div class="fsw-bg">' +
						'<div class="js-lazy" data-bg="'+item.image+'"></div>' +
					'</div>' +
					'<div class="fsw-item-overlay"></div>';
		if ( this.$wrap.hasClass('fsw-youtube')) {
			html += '' +
					'<div class="fsw-yt-play">' +
						'<div class="fsw-play"></div>' +
						'<div class="fsw-title">' + item.title + '</div>' +
					'</div>';
		}
		html += '' +
				'</div>' +
				'<div class="fsw-item-details" style="display: none;">';
		if ( this.$wrap.hasClass('fsw-youtube')) {
			//Youtube hashtag and mentions
			item.description = item.description.replace(/@(\S*)/g,'<a href="https://www.youtube.com/user/$1">@$1</a>');
			item.description = item.description.replace(/#(\S*)/g,'<a href="https://www.youtube.com/results?search_query=%23$1">#$1</a>');
			html += '' +
					'<div class="fsw-video-wrap" data-video-id="' + item.id + '"></div>' +
					'<div class="fsw-video-right">' +
						'<div class="fsw-video-right-title">' + item.title + '</div>' +
						'<div class="fsw-video-right-text">' + item.description + '</div>' +
					'</div>';
		}
		if ( this.$wrap.hasClass('fsw-instagram')) {
			//Instagram hashtag and mentions
			item.description = item.description.replace(/@(\S*)/g,'<a href="https://www.instagram.com/$1/">@$1</a>');
			item.description = item.description.replace(/#(\S*)/g,'<a href="https://www.instagram.com/explore/tags/$1">#$1</a>');
			html += '' +
					'<div class="fsw-photo">' +
						'<div class="js-lazy" data-bg="'+item.image+'"></div>' +
					'</div>' +
					'<div class="fsw-photo-right">' +
						'<div class="fsw-photo-text">' + item.description + '</div>' +
						'<div class="fsw-post-cta">' +
							'<a href="'+item.link+'" target="_blank">Jump to Post</a>' +
						'</div>' +
					'</div>';
		}
		html += '' +
				'</div>' +
				'<a href="'+item.link+'" class="fsw-mobile-link" target="_blank"></a>' +
			'</div>';
	}
	html += '</div>';
	return html;
};

/**
 * Check if given frame has next frame (based on feed data)
 * @param frame - frame index
 * @returns {boolean}
 */
FeconSocialWidget.prototype.frame_has_next = function ( frame ) {
	var i_next = ( frame + 1 ) * this.per_frame + 1;
	return typeof this.feed[i_next] !== 'undefined';
};
/**
 * Checks if given frame has previous frame
 * @param frame - frame index
 * @returns {boolean}
 */
FeconSocialWidget.prototype.frame_has_prev = function ( frame ) {
	return frame > 0;
};
/**
 * Sets given frame as current
 * Animates slider
 * @param frame - frame index
 * @param no_anim - if true no animation will be played (immediate set)
 */
FeconSocialWidget.prototype.frame_set = function ( frame, no_anim ) {
	var _this = this;

	// Show/Hide Next CTA
	if ( this.frame_has_next ( frame )) {
		this.$cta_more.fadeIn(200);
	} else {
		this.$cta_more.fadeOut(200);
	}
	// Show/Hide Prev CTA
	if ( this.frame_has_prev ( frame )) {
		this.$cta_prev.fadeIn(200);
	} else {
		this.$cta_prev.fadeOut(200);
	}

	// If Slider is animating
	if ( this.$wrap.hasClass('fsw-animating-slider') ) {
		// Save next frame to cache to animate after current animation ends
		this.delayed_frame_set = frame;
		// Exit
		return;
	}
	// Get next frame wrapper
	var $frame = this.$wrap.find('.fsw-screen-'+frame);
	// If next frame doesn't exist
	if ( !$frame.length ) {
		// Generate and append next frame
		$frame = this.frame_add(frame);
	}
	// Get current active frame
	var $active = this.$slider.find('.fsw-active');

	// If Animation
	if ( !no_anim ) {
		// If next frame is on the right
		if ( frame > this.frame_current ) {
			// Set animating flag
			this.$wrap.addClass('fsw-animating-slider');
			$frame.show();
			setTimeout(function () {
				$active.addClass('fsw-prev');
				$frame.addClass('fsw-active');
				setTimeout(function () {
					$active.hide().removeClass('fsw-active').removeClass('fsw-prev');
					// Reset animating flag
					_this.$wrap.removeClass('fsw-animating-slider');
					// If we have cached next frame
					if (_this.delayed_frame_set !== null) {
						// Go to cached next frame
						_this.frame_set(_this.delayed_frame_set);
						// Reset cache
						_this.delayed_frame_set = null;
					}
				}, 650);
			}, 50);
		}
		if ( frame < this.frame_current ) {
			// Set animating flag
			this.$wrap.addClass('fsw-animating-slider');
			$frame.addClass('fsw-prev').addClass('fsw-active');
			$frame.show();
			setTimeout(function () {
				$active.removeClass('fsw-active');
				$frame.removeClass('fsw-prev');
				setTimeout(function () {
					$active.hide();
					// Reset animating flag
					_this.$wrap.removeClass('fsw-animating-slider');
					// If we have cached next frame
					if (_this.delayed_frame_set !== null) {
						// Go to cached next frame
						_this.frame_set(_this.delayed_frame_set);
						// Reset cache
						_this.delayed_frame_set = null;
					}
				}, 650);
			}, 50);
		}
	// No animation
	} else {
		$frame.hide();
		$active.hide();
		$frame.addClass('fsw-active');
		setTimeout(function () {
			$frame.show();
		},10);
	}

	// Update current frame cursor
	this.frame_current = frame;
};

/**
 * Go to next frame
 */
FeconSocialWidget.prototype.frame_next = function () {
	var next = this.frame_current + 1;
	this.frame_set ( next );
};
/**
 * Go to prev frame
 */
FeconSocialWidget.prototype.frame_prev = function () {
	var next = this.frame_current - 1;
	this.frame_set ( next );
};
/**
 * Load feed data via AJAX
 */
FeconSocialWidget.prototype.feed_load = function () {
	var _this = this;
	// Add .wait preloader
	this.$slider.html('<div class="wait"></div>');
	// Perform AJAX request
	$.ajax({
		url: this.feed_url,
		method: 'GET',
		dataType: 'json',
		cache: false,
		success: function ( resp ) {
			// Save results to internal var
			_this.feed = resp;
			// Remove preloader
			_this.$slider.find('.wait').fadeOut(300, function(){
				$(this).remove();
			});
			// Set 1st frame without animation
			_this.frame_set ( 0, true );
		},
		error: function ( xhr, status, error ) {
			console.log('Feed ajax error: '+status);
			console.log(error);
		}
	});
};

/**
 * Generates new frame/slider wrapper and appends it to widget
 * @param frame - frame index
 * @returns {*|jQuery|HTMLElement}
 */
FeconSocialWidget.prototype.frame_add = function ( frame ) {
	var $frame = $(this.html_frame ( frame ));
	this.$slider.append ( $frame );
	$('.js-lazy').lazy_load();
	return $frame;
};

/**
 * Open item details shortcut
 * @param $item
 */
FeconSocialWidget.prototype.open = function ( $item ) {
	this.open_close( $item );
};

/**
 * Close item details shortcut
 */
FeconSocialWidget.prototype.close = function () {
	this.open_close();
};

/**
 * Open/Close feed item details
 * @param $item
 */
FeconSocialWidget.prototype.open_close = function ( $item ) {
	var _this = this;
	var x = 1;// Math.random() > 0.5;
	var rotate = 'rotate'+(x?'X':'Y');
	var css = {};
	css[rotate] = '90deg';
	// Set animating flag
	this.$wrap.addClass('fsw-animating');
	// Rotate OUT widget to hide all it's contents
	this.$rotator.animate( css, {
		duration: 160,
		easing: 'linear',
		complete: function () {
			// Update widget contents
			if ( $item ) {
				$item.find('.fsw-item-details').show();
				_this.$cta_close.show();
				_this.$cta_more.hide();
				_this.$cta_prev.hide();
				_this.video_insert($item);
			} else {
				_this.$wrap.find('.fsw-item-details').hide();
				_this.$cta_close.hide();
				_this.frame_set ( _this.frame_current );
				_this.video_remove(_this.$wrap.find('.fsw-item'));
			}
			// Set initial CSS for rotate IN
			css[rotate] = '-90deg';
			_this.$rotator.css(css);
			css[rotate] = '0deg';
			setTimeout(function () {
				// Rotate IN widget to show updated content
				_this.$rotator.animate( css, {
					duration: _this.$wrap.hasClass('fsw-nobounce') ? 400 : 600,
					easing: _this.$wrap.hasClass('fsw-nobounce') ? $.bez([0,0,.58,1]) : $.bez([.4,1.6,0,1]),
					complete: function () {
						// Reset animating flag
						_this.$wrap.removeClass('fsw-animating');
					}
				});
			},10);
		}
	});
};

/**
 * Helper to insert Youtube video <iframe> with autoplay option for YT widget
 * @param $item
 */
FeconSocialWidget.prototype.video_insert = function ( $item ) {
	var $vw = $item.find('.fsw-video-wrap');
	var video_id =  $vw.data('video-id');
	if ( video_id ) {
		$vw.html('<iframe width="560" height="315" src="https://www.youtube.com/embed/'+video_id+'?rel=0&showinfo=0&autoplay=1" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>');
	}
};

/**
 * Helper to remove <iframe> video for YT widget
 * @param $item
 */
FeconSocialWidget.prototype.video_remove = function ( $item ) {
	var $vw = $item.find('.fsw-video-wrap');
	$vw.html('');
};
});
