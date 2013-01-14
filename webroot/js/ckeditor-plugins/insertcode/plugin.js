/*
 Copyright (c) 2003-2012, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.plugins.add( 'insertcode',
	{
		requires: 'dialog',
		lang : 'en,pl', // %REMOVE_LINE_CORE%
		icons: 'insertcode', // %REMOVE_LINE_CORE%
		onLoad : function()
		{
			if ( CKEDITOR.config.insertcode_class )
			{
				CKEDITOR.addCss(
					'code.' + CKEDITOR.config.insertcode_class + ' {' +
						CKEDITOR.config.insertcode_style +
						'}'
				);
			}
		},
		init : function( editor )
		{
			editor.addCommand( 'insertcode', new CKEDITOR.dialogCommand( 'insertcode' ) );
			editor.ui.addButton && editor.ui.addButton( 'InsertCode',
				{
					label : editor.lang.insertcode.title,
					icon : this.path + 'icons/insertcode.png',
					command : 'insertcode',
					toolbar: 'insert,99'
				} );

			if ( editor.contextMenu )
			{
				editor.addMenuGroup( 'code' );
				editor.addMenuItem( 'insertcode',
					{
						label : editor.lang.insertcode.edit,
						icon : this.path + 'icons/insertcode.png',
						command : 'insertcode',
						group : 'code'
					});
				// TODO: Fix this. I think the <code> must be added as a CKEDITOR node in order to get the data.
				// For now, just have to click the code button in the toolbar to edit.
				/*
				editor.contextMenu.addListener( function( element )
				{
					if ( element )
						//console.dir(element)
						element = element.getAscendant( 'pre', true );
					if ( element && !element.isReadOnly() && element.hasClass( editor.config.insertcode_class ) )
						return { insertcode : CKEDITOR.TRISTATE_OFF };
					return null;
				});
				*/
			}

			CKEDITOR.dialog.add( 'insertcode', function( editor )
			{
				return {
					title : editor.lang.insertcode.title,
					minWidth : 540,
					minHeight : 380,
					contents : [
						{
							id : 'general',
							label : editor.lang.insertcode.code,
							elements : [
								{
									type : 'textarea',
									id : 'contents',
									label : editor.lang.insertcode.code,
									cols: 140,
									rows: 22,
									validate : CKEDITOR.dialog.validate.notEmpty( editor.lang.insertcode.notEmpty ),
									required : true,
									setup : function( element )
									{
										/* // isn't working...
										var textareaElement = CKEDITOR.document.getById( this._.inputId );
										textareaElement.on( 'keydown', function( ev ) {
											e = ev.data.$;
											var o = e.target;
											var kC = e.keyCode ? e.keyCode : e.charCode ? e.charCode : e.which;
											if (kC == 9 && !e.shiftKey && !e.ctrlKey && !e.altKey) {
												var oS = o.scrollTop;
												if (o.setSelectionRange)
												{
													var sS = o.selectionStart;
													var sE = o.selectionEnd;
													o.value = o.value.substring(0, sS) + "\t" + o.value.substr(sE);
													o.setSelectionRange(sS + 1, sS + 1);
													o.focus();
												}
												else if (o.createTextRange)
												{
													document.selection.createRange().text = "\t";
													e.returnValue = false;
												}
												o.scrollTop = oS;

										        // The DOM event object is passed by the "data" property.
										        var domEvent = ev.data;
										        // Prevent the click to chave any effect in the element.
										        domEvent.preventDefault();
										        this.focus();
										    }
										});
										*/

										var html = element.getHtml();
										if ( html )
										{
											var div = document.createElement( 'div' );
											div.innerHTML = html;
											this.setValue( div.firstChild.firstChild.nodeValue );
											//this.setValue( div.firstChild.nodeValue );
										}
									},
									commit : function( element )
									{
										//element.setHtml( CKEDITOR.tools.htmlEncode( this.getValue() ) );
										//element.setHtml( '<code contenteditable="false">' + CKEDITOR.tools.htmlEncode( this.getValue() ) + '</code>' );
										element.setHtml( '<code contenteditable="false">' + CKEDITOR.tools.htmlEncode( this.getValue() ) + '</code>' );
									}
								}
							]
						}
					],
					onShow : function()
					{
						var sel = editor.getSelection(),
							element = sel.getStartElement();
						if ( element )
							element = element.getAscendant( 'pre', true );

						if ( !element || element.getName() != 'pre' || !element.hasClass( editor.config.insertcode_class ) )
						{
							//element = editor.document.createElement( 'pre' );
							element = new CKEDITOR.dom.element( 'pre' );
							this.insertMode = true;
						}
						else
							this.insertMode = false;

						this.code = element;
						this.setupContent( this.code );
					},
					onOk : function()
					{
						if ( editor.config.insertcode_class )
							this.code.setAttribute( 'class', editor.config.insertcode_class );

						if ( this.insertMode )
							editor.insertElement( this.code );

						this.commitContent( this.code );
					}
				};
			} );
		}
	} );

if (typeof(CKEDITOR.config.insertcode_style) == 'undefined')
	CKEDITOR.config.insertcode_style = 'background-color:#F8F8F8;border:1px solid #DDD;padding:10px;';
if (typeof(CKEDITOR.config.insertcode_class)  == 'undefined')
	CKEDITOR.config.insertcode_class = 'prettyprint';