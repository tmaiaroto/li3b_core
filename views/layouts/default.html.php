<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:og="http://ogp.me/ns#" xmlns:fb="http://ogp.me/ns/fb#">
<head>
	<?php echo $this->html->charset();?>
	<?php $title = $this->title() ? $this->title():''; ?>
	<title><?=$title ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="">
	<meta name="author" content="">
	<?php
		echo $this->html->style(array('/li3b_core/css/bootstrap.min.css', '/li3b_core/css/bootstrap-responsive.min.css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/smoothness/jquery-ui.css'), array('inline' => true));
	?>
	<!--[if lt IE 9]>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
	<script>!window.jQuery && document.write('<script src="/li3b_core/js/jquery/jquery-1.7.2.min.js"><\/script>')</script>
	<?php
		echo $this->html->script(array(
			'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.11/jquery-ui.min.js',
			'/li3b_core/js/bootstrap.min.js'
		), array('inline' => true));
	?>
	<?php
		echo $this->scripts();
		echo $this->styles();
	?>
	<?php // because there is a navbar. this adds spacing ?>
	<style type="text/css">
		body {
			padding-top: 60px;
			padding-bottom: 40px;
		}
	</style>
	<?php echo $this->html->link('Icon', null, array('type' => 'icon')); ?>
	<script>if(typeof(hljs) !== 'undefined') { hljs.initHighlightingOnLoad(); }</script>
</head>
<body>
	<?=$this->_render('element', 'navbar', array('user' => $this->request()->user), array('library' => 'li3b_core')); ?>
	<div class="container">
		<?php echo $this->content(); ?>
		<?=$this->_render('element', 'footer', array(), array('library' => 'li3b_core')); ?>
	</div><!--/.container-->
		<?php
	/**
	 * Handle some social media JS SDKs, such as Facebook's, if the application has configured them.
	 */
	if(isset($this->request()->social)) {
	?>
		<?php if(isset($this->request()->social['facebook']) && isset($this->request()->social['facebook']['appId'])) { ?>
			<?php
			// Get all the options and set some defaults.
			$fbOptions = $this->request()->social['facebook'] += array(
				//'channelUrl' => false,
				'status' => true, // check login status
				'cookie' => true, // enable cookies to allow the server to access the session
				'xfbml' => true // parse XFBML
			);
			?>
			<div id="fb-root"></div>
			<script type="text/javascript">
				window.fbAsyncInit = function() {
					FB.init({<?php
						$i = 1;
						$totalFbOptions = count($fbOptions);
						foreach($fbOptions as $key => $value) {
							$lineEnd = ($i < $totalFbOptions) ? ', ':'';
							if(is_bool($value)) {
								$value = ($value === true) ? 'true':'false';
								echo $key . ': ' . $value . $lineEnd;
							} else {
								echo $key . ': "' . $value . '"' . $lineEnd;
							}
							$i++;
						}
						?>});
					if(typeof(fbReady) == 'function') { fbReady(); }
				};

				// Load the SDK Asynchronously
				(function(d){
					var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
					if (d.getElementById(id)) {return;}
					js = d.createElement('script'); js.id = id; js.async = true;
					js.src = "//connect.facebook.net/en_US/all.js";
					ref.parentNode.insertBefore(js, ref);
				}(document));
			</script>
		<?php } // end Facebook include ?>
	<?php } // end social SDKs ?>
			
	<?php
	/**
	 * Handle Google Analytics if configured.
	 */
	if(isset($this->request()->googleAnalytics) && isset($this->request()->googleAnalytics['code']) && isset($this->request()->googleAnalytics['domain'])) {
	?>
	<script type="text/javascript">
		// GA
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

		ga('create', '<?=$this->request()->googleAnalytics['code']; ?>', '<?=$this->request()->googleAnalytics['domain']; ?>');
		ga('send', 'pageview');
	</script>
	<?php } // end Google Analytics ?>
	<?=$this->html->flash(); ?>
</body>
</html>