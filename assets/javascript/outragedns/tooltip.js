(function($)
{
	/* declare the module */
	var Module = function(element)
	{
		var element = $(element);
		
		if(element.data("tipsy"))
		{
			element.tipsy("disable");
			element.removeData("tipsy");
		}
		
		var tooltip =
		{
			fade: true,
			focus: true,
			gravity: "s"
		};
		
		element.tipsy(tooltip);
	};
	
	Module.component = "tooltip";
	
	
	/* now, init the module */
	$.module.push([ ".tooltip", Module ]);
})(window.jQuery);