(function($)
{
	$.module = [];
	
	var install = function(element, module)
	{
		if(!module.component || !module.component.length)
			throw "Bad component";
		
		return $.data(element, module.component, new module(element));
	};
	
	$.fn.inherit = function()
	{
		for(var i in $.module)
		{
			var selector = $.module[i][0];
			var module = $.module[i][1];
			
			if(this.is(selector))
				install(this.get(0), module);
			
			this.find(selector).each(function()
			{
				install(this, module);
			});
		}
		
		return this;
	};
})(window.jQuery);
