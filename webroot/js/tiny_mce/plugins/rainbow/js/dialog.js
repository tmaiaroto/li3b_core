tinyMCEPopup.requireLangPack();

var RainbowDialog = {
	
	wrapNeeded: true,
	
	init : function() {
		var f = document.forms[0];

		// Get the selected contents as text and place it in the textarea
		f.codeSource.value = tinyMCEPopup.editor.selection.getContent({format : 'text'});
		// Default value
		f.line.value = '1';
		
		// However, if the rainbow button was clicked while inside a code element, use that instead
		if(tinyMCEPopup.editor.selection.getNode().nodeName == 'CODE') {
			// First, set the options for language and line number if available
			if(tinyMCEPopup.editor.selection.getNode().getAttribute('data-language') != null) {
				f.language.value = tinyMCEPopup.editor.selection.getNode().getAttribute('data-language');
			}
			if(tinyMCEPopup.editor.selection.getNode().getAttribute('data-line') != null) {
				f.line.value = tinyMCEPopup.editor.selection.getNode().getAttribute('data-line');
			}
			
			// Set the textarea with existing code
			f.codeSource.value = unescape(tinyMCEPopup.editor.selection.getNode().innerHTML);
			RainbowDialog.wrapNeeded = false;
			// Remove the pre element so it doesn't get duplicated
			tinyMCEPopup.editor.dom.remove(tinyMCEPopup.editor.selection.getNode());
		}
		
	},

	insert : function() {
		// If nothing was entered, just close.
		if(document.forms[0].codeSource.value == '') {
			tinyMCEPopup.close();
		}
		
		// Insert the contents from the input into the document
		var dataLanguage = '';
		if(document.forms[0].language.value != '') {
			dataLanguage = ' data-language="' + document.forms[0].language.value + '"';
		}
		
		var dataLine = '';
		if(document.forms[0].line.value != '') {
			dataLine = ' data-line="' + document.forms[0].line.value + '"';
		}
		
		var rainbowCode;
		if(RainbowDialog.wrapNeeded == true) {
			rainbowCode = '<pre><code' + dataLine + dataLanguage + '>' + escape(document.forms[0].codeSource.value) + '</code></pre>';
		} else {
			rainbowCode = '<code' + dataLine + dataLanguage + '>' + escape(document.forms[0].codeSource.value) + '</code>';
		}
		
		//tinyMCEPopup.editor.execCommand('mceReplaceContent', false, rainbowCode);
		tinyMCEPopup.editor.execCommand('mceInsertContent', false, rainbowCode);
		
		tinyMCEPopup.close();
	},
	
	// TODO: Clicking the "X" will wipe out content....So concel/close really needs to put everything back as it was...
	cancel : function() {
		
		tinyMCEPopup.close();
	}
};

tinyMCEPopup.onInit.add(RainbowDialog.init, RainbowDialog);
