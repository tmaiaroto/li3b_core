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
		echo $this->html->style(array('/li3b_core/css/bootstrap.min.css', '/li3b_core/css/bootstrap-responsive.min.css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/smoothness/jquery-ui.css', '/li3b_core/css/jquery/tipsy.css', '/li3b_core/bootstrap-wysihtml5/src/bootstrap-wysihtml5.css', '/li3b_core/css/admin'), array('inline' => true));
	?>
	<!--[if lt IE 9]>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
	<script>!window.jQuery && document.write('<script src="/li3b_core/js/jquery/jquery-1.7.2.min.js"><\/script>')</script>
	<?php
		echo $this->html->script(array(
			'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.11/jquery-ui.min.js',
			'/li3b_core/js/jquery/jquery.tipsy.js',
			'/li3b_core/js/tiny_mce/tiny_mce.js',
			'/li3b_core/js/bootstrap.min.js',
			'/li3b_core/wysihtml5/dist/wysihtml5-0.3.0.js',
			'/li3b_core/bootstrap-wysihtml5/src/bootstrap-wysihtml5.js',
			'/li3b_core/js/tiny_mce/jquery.tinymce.js'
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

	<script type="text/javascript" src="/li3b_core/js/full-rainbow.min.js"></script>
</head>
<body>
	<?=$this->_render('element', 'admin_navbar', array('user' => $this->request()->user), array('library' => 'li3b_core')); ?>
	<div class="container">
		<?php echo $this->content(); ?>
		<?=$this->_render('element', 'footer', array(), array('library' => 'li3b_core')); ?>
	</div><!--/.container-->
	<script type="text/javascript">
		$(function() {

			// TO INSERT ANY SPECIFIC PREDEFINED TEXT: data-wysihtml5-command="insertHTML" data-wysihtml5-command-value="your text here"

			$('.wysihtml5').wysihtml5('deepExtend', {
				stylesheets: ['/li3b_core/css/bootstrap.min.css', '/li3b_core/css/rainbow-themes/blackboard.css'],
				toolbar: {
					speech: '<li>' +
							'<a class="btn" data-wysihtml5-command="insertSpeech" title="Voice input" href="javascript:;" unselectable="on"><i class="icon-volume-up"></i></a>' +
						'</li>',
					code:  function(locale, options) {
						return '<li><a class="btn" data-wysihtml5-command="formatInline" data-wysihtml5-command-value="code" href="javascript:;" unselectable="on"><i class="icon-th-large"></i></li>'
					},
					insertThis: function(locale, options) {
						return '<li><a class="btn" data-wysihtml5-command="fomratInline" data-wysihtml5-command-value="span" href="javascript:;"><i class="icon-ok"></i></a></li>';
					},
					insertAnything:  function(locale, options) {
							return '<li>' +
								'<a class="btn" data-wysihtml5-command="insertHTML" href="javascript:;" data-toggle="modal" data-target="#insertAnythingModal" unselectable="on"><i class="icon-asterisk"></i></a>' +
								'<div id="insertAnythingModal" data-wysihtml5-dialog="insertHTML" class="modal hide fade">' +
									'<div class="modal-header">' +
										'<a class="close" data-dismiss="modal">&times;</a>' +
										'<h3>Insert Some Stuff</h3>' +
									'</div>' +
									'<div class="modal-body">' +
										'<textarea id="myJazz"></textarea>' +
									'</div>' +
									'<div class="modal-footer">' +
										'<a class="btn" href="javascript:;" data-dismiss="modal">Cancel</a>' +
										'<a class="btn btn-primary" data-dismiss="modal" data-wysihtml5-command="insertHTML" onClick="$(this).attr(\'data-wysihtml5-command-value\', $(\'#myJazz\').val()); $(\'#myJazz\').val(\'\')" data-wysihtml5-command-value="jazz" href="javascript:;" unselectable="on">Insert</a>' +
									'</div>' +
								'</div>' +
							'</li>';
					},
				},
				html: true,
				parserRules: {
					classes: {
					  "middle": 1,
					  "icon-beer": 1,
					  "prettyprint": 1
					},
					tags: {
						// <iframe width="560" height="315" src="http://www.youtube.com/embed/eE_IUPInEuc" frameborder="0" allowfullscreen></iframe>
						iframe: {
							allow_attributes: ['height', 'width', 'src', 'frameborder', 'allowfullscreen']
						},
						code: {
							allow_attributes: ['data-language', 'style']
						},
						pre: {
							allow_attributes: ['style']
						},
						strong: {},
						em: {},
						i: {}
					}
				}
			});

			var wysihtml5Editor = $('.wysihtml5').data("wysihtml5").editor;
			//console.dir(wysihtml5Editor)
			wysihtml5Editor.on('blur', function(a) {
				$('#PostBody').val(wysihtml5Editor.getValue());
			})


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