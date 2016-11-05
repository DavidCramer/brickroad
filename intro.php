<div class="wrap about-wrap">
	<h1>Welcome to Brickroad <?php echo BRICKROAD_VER; ?></h1>
	<div class="description">Building your own plugins just got easier.</div><br>
	
	<!-- <a href="admin.php?page=brickroad-admin" class="button">Close Intro</a> -->

	<h2 class="nav-tab-wrapper">
		<a class="nav-tab nav-tab-active" href="#whats_new">What’s New</a>	
		<a class="nav-tab" href="#changelog">Changelog</a>
		<a class="nav-tab" href="#credits">Credits</a>
		<a href="?page=brickroad-admin" class="button-primary" style="margin: 2px 20px;">Close Intro</a>
	</h2>

	<div id="whats_new" class="feature-section col three-col">
		<div class="banner col-1">
			<ul>
				<!-- Welcome -->
				<li> 
					<div class="slidePanel slide-panel-intro">
						<div class="slide-text-panel">
							<p>Build your own plugins or simpy add something special.</p>
						</div>
					</div>
				</li>

				<!-- Post Type -->
				<li> 
					<div class="slidePanel slide-panel-posttype">
						<div class="slide-text-panel">
							<p>Set shortcodes and widgets as post-types to make reusable configurations, or create a dedicated Post-Type element.</p>
						</div>
					</div>
				</li>

				<!-- metabox -->
				<li> 
					<div class="slidePanel slide-panel-meta">
						<div class="slide-text-panel">
							<p>Create custom metaboxes for existing Post-Types to capture additional data or simple create a templated panel.</p>
						</div>
					</div>
				</li>

				<!-- Settings Page -->
				<li> 
					<div class="slidePanel slide-panel-settings">
						<div class="slide-text-panel">
							<p>Effortlessly create custom settings pages to conveniently capture setup values.</p>
						</div>
					</div>
				</li>

				<!-- Clean Code -->
				<li> 
					<div class="slidePanel slide-panel-clean">
						<div class="slide-text-panel">
							<p>Exported code adheres to the WordPress coding standard, is GPLv2 and can be distributed freely under this license.</p>
						</div>
					</div>
				</li>

				<!-- Get Started -->
				<li> 
					<div class="slidePanel slide-panel-end">
						<div class="slide-text-panel-end">
							<p>Starting to build your own WordPress plugins has never been easier!</p>
							<p style="margin-top:85px;"><a href="admin.php?page=brickroad-admin" class="button button-primary">Get started and create something new</a></p>
						</div>
					</div>
				</li>
			</ul>
		</div>
		<div class="about-brickroad-admin col-2">
			<h3>A whole new core</h3>
			<p>This version of Brickroad sees a complete rebuild of the internal rendering of elements (shortcodes, widgets etc.).</p>
			<p>Previously, your elements where stored in the database and loaded as needed. The code was then parsed and executed.</p>
			<p>While your source code is still saved to database, activated elements code is complied down to a stand-alone plugin called "Brickroad Elements" which is completely independent of Brickroad. This means your elements run faster and is more secure.</p>
			<p>Exporting complies a stand-alone plugin of your own chosen name with the selected elements contained. This allows you to then distribute your plugin for sale, free or within a theme.</p>
		</div>		
	</div>

	<div id="changelog" class="feature-section hidden">
		<h3>Changes in <?php echo BRICKROAD_VER; ?></h3>
		<ol>
			<li>Metabox Storage: Single values & Array. <span class="description">Single values are saved as custom fields using the field slug as teh key, while array based are saved as a single custom field using the element slug.</span> </li>
			<li>Widget customizer panel. <span class="description">Compatability for the widget customizer in 3.9 +.</span> </li>
			<li>Settings page group tabs. <span class="description">Alignment issues fixed when the last group has less fields than the first.</span> </li>
			<li>Improved Field Type Element. <span class="description">Behaves more like a native field. Still some issues with scripts. sorry.</span> </li>
			<li>CSS fix for wrapping the title basr on long shortcodes.</li>
			<li>Fix for Shortcode Builder button not working</li>
			<li>Minor Bug fixes. <span class="description">Many little bugs that cause some issues on 3.9.1</span> </li>
		</ol>

	</div>
	<div id="credits" class="feature-section hidden">
		<h3>External Libraries</h3>
		<p class="description">Brickroad makes use of these additional libraries.</p>
		<a href="http://codemirror.net/">CodeMirror</a>,
		<a href="http://getbootstrap.com/">Bootstrap</a>,
		<a href="https://github.com/Desertsnowman/BaldrickJS/">Baldrick.js</a>,
		<a href="http://labs.abeautifulsite.net/jquery-minicolors/">jQuery MiniColors</a>,
		<a href="https://github.com/simontabor/jquery-toggles">jQuery Toggles</a>,
		<a href="http://www.eyecon.ro/bootstrap-datepicker">Bootstrap Datepicker</a>,
		<a href="http://loopj.com/jquery-simple-slider/">jQuery Simple Slider</a>,

		<a href="http://fortawesome.github.io/Font-Awesome/">Font Awesome</a> and
		<a href="http://unslider.com/">Unslider</a>.
		<br>
		<h3>Thank you.</h3>
		<p>Special thanks to everyone who has purchased Brickroad! Without your support, it this plugin would not be what it is today.</p>
	</div>



</div>
<script type="text/javascript">
jQuery(function($){
	var unslider = $('.banner').unslider({
		speed: 500,               //  The speed to animate each slide (in milliseconds)
		delay: 50000,              //  The delay between slide animations (in milliseconds)
		complete: function() {},  //  A function that gets called after every slide animation
		keys: true,               //  Enable keyboard (left, right) arrow shortcuts
		dots: true,               //  Display dot navigation
		fluid: false              //  Support responsive design. May break non-responsive designs
	});
	
	$('.intro-next').click(function() {
		var fn = this.className.split(' ')[1];
		unslider.data('unslider')[fn]();
	});

	$('.nav-tab-wrapper').on('click', '.nav-tab', function(){
		var clicked = $(this);

		$('.nav-tab.nav-tab-active').removeClass('nav-tab-active');
		$('.feature-section').hide();
		clicked.addClass('nav-tab-active');
		$(clicked.attr('href')).show();

	});
	$('.auto-verify-key').trigger('click');
});
</script>