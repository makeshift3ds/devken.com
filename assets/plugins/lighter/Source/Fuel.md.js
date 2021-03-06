/*
---
description: Markdown language fuel.

license: MIT-style

authors:
- Jose Prado

requires:
  core/1.2.4: '*'

provides: [Fuel.md]

...
*/
Fuel.md = new Class ({
	
	Extends: Fuel,
	language: 'md',
	
	initialize: function(code, options) {
		
		/** Set of RegEx patterns to match */
		this.patterns = new Hash({
			'header1': {pattern: /^(.+)\n=+\n/gim,   alias: 'st1'},
			'header2': {pattern: /^(.+)\n-+\n/gim,   alias: 'st2'},
			'header3': {pattern: /[#]{1,6}.*/gim,    alias: 'st0'},
			'ul':      {pattern: /^\*\s*.*/gim,      alias: 'kw1'},
			'ol':      {pattern: /^\d+\..*/gim,      alias: 'kw1'},
			'italics': {pattern: /\*.*?\*/g,         alias: 'kw3'},
			'bold':    {pattern: /\*\*.*?\*\*/g,     alias: 'kw3'},
			'url':     {pattern: /\[[^\]]*\]\([^\)]*\)/g, alias: 'kw4'}
		});
		
		this.parent(code, options);
	}
	
});