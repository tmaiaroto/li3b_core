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
		echo $this->html->style(array('/li3b_core/css/bootstrap.min.css', '/li3b_core/css/bootstrap-responsive.min.css', '/li3b_core/css/font-awesome', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/smoothness/jquery-ui.css', '/li3b_core/css/jquery/tipsy.css', '/li3b_core/css/admin'), array('inline' => true));	
	?>
	<!--[if lt IE 9]>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
	<script>!window.jQuery && document.write('<script src="/li3b_core/js/jquery/jquery-1.7.2.min.js"><\/script>')</script>	
	<?php
		echo $this->html->script(array('https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.11/jquery-ui.min.js', '/li3b_core/js/jquery/jquery.tipsy.js', '/li3b_core/js/tiny_mce/tiny_mce.js', '/li3b_core/js/bootstrap.min.js', '/li3b_core/js/tiny_mce/jquery.tinymce.js'), array('inline' => true));
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
</head>
<body>
	<?=$this->_render('element', 'admin_navbar', array('user' => $this->request()->user), array('library' => 'li3b_core')); ?>
	<div class="container">
		<?php echo $this->content(); ?>
		<?=$this->_render('element', 'footer', array(), array('library' => 'li3b_core')); ?>
	</div><!--/.container-->
	<script type="text/javascript">
		$(function() {
			// $('.dropdown-toggle').dropdown()
			
			tinyMCE.init({
				// General options
				mode : "specific_textareas",
				editor_selector : "editor-html",
				
				theme : "advanced",
				plugins : "rainbow,style,table,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,media,searchreplace,contextmenu,paste,directionality,fullscreen,noneditable,xhtmlxtras",
				
				// Theme options
				theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,formatselect,fontselect,fontsizeselect,rainbow",
				theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,|,forecolor,backcolor",
				theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,|,code,fullscreen",
				theme_advanced_toolbar_location : "top",
				theme_advanced_toolbar_align : "left",
				theme_advanced_statusbar_location : "bottom",
				theme_advanced_resizing : true,
				theme_advanced_resize_horizontal : false,
				
				extended_valid_elements: 'code[*],pre[*]',
				
				// Example content CSS (should be your site CSS)
				content_css: "/li3b_core/css/editor-content.css"
			});
		});
	</script>
	<?=$this->html->flash(); ?>
</body>
</html>