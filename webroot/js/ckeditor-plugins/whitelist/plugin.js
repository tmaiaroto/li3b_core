/*
 * @file Whitelisting plugin for CKEditor
 * Copyright (C) 2012 Alfonso Martínez de Lizarrondo
 *
 */

CKEDITOR.plugins.add( 'whitelist',
{

	init : function( editor )
	{
	}, //Init

	afterInit : function( editor )
	{
		var dataProcessor = editor.dataProcessor,
			dataFilter = dataProcessor && dataProcessor.dataFilter,
			htmlFilter = dataProcessor && dataProcessor.htmlFilter;

		// Object with the rules that will be applied on each operation
		var sanitizerRules =
		{
			comment : function( contents )
			{
				// Strip out all comments (as well as protected source like scripts)
				return null;
			},
			elements:
			{
				$ : function( element )
				{
					var config=editor.config;
					if (!(element.name in config.whitelist_elements))
					{
						// console.log("Remove " + element.name);
						// The element (as well as any content or children) is removed.
						return false;
					}

					var whitelistAttributes = config.whitelist_elements[ element.name ].attributes || {};
					for( var att in element.attributes )
					{
						// Some attributes like href or src are handled by CKEditor in a different way to avoid problems with the browsers
						if ( att.substr(0,15) == "data-cke-saved" )
							att = substr( 15 );

						if (!( att in config.whitelist_globalAttributes) && !(att in whitelistAttributes))
						{
							// console.log("remove attribute: " + att);
							delete element.attributes[att];
						}
					}
				}
			}
		};


		// dataFilter : conversion from html input to internal data
		dataFilter.addRules( sanitizerRules, 20);

		// htmlFilter : conversion from internal data to html output.
		htmlFilter.addRules( sanitizerRules, 20);

	}
} );

/**
 * Whitelisted elements. Attributes specific to each element can be added in the definition
 */
CKEDITOR.config.whitelist_elements = {
	p: 1,
	span: 1,
	br: 1,
	strong: 1,
	em: 1,
	h1: 1,
	h2: 1,
	h3: 1,
	h4: 1,
	h5: 1,
	h6: 1,
	ol: 1,
	ul: 1,
	li: 1,
	img: { attributes: {src:1, alt:1} },
	a: { attributes: {href:1} }
};

/**
 * Whitelisted attributes globaly. Any element is allowed to have these attributes
 */
CKEDITOR.config.whitelist_globalAttributes = {
	id: 1,
	'class': 1
};