(function($)
{
	/* declare the module */
	var Module = function(table)
	{
		this.table = $(table).data("grid-table", this);
		this.init();
	};
	
	Module.prototype =
	{
		init: function()
		{
			this.table.on("click", "ul.actions > li.clone", this.onclone);
			this.table.on("click", "ul.actions > li.remove", this.onremove);
			
			this.reindex();
		},
		
		reindex: function()
		{
			this.table.find("tbody > tr").each(function(index)
			{
				$(this).find("[name]").each(function()
				{
					$(this).attr("name", $(this).attr("name").replace(/^(.*?)\[.*?\](.*?)$/, "$1[" + index + "]$2"));
				});
			});
		},
		
		onclone: function(event)
		{
			event.preventDefault();
			
			var table = $(this).parents("tbody").first();
			var duplicate = $(this).parents("tr").first().clone().hide();
			
			duplicate.appendTo(table).fadeIn("fast");
			
			$(this).parents("table").data("grid-table").reindex();
		},
		
		onremove: function(event)
		{
			event.preventDefault();
			
			if(!window.confirm("Do you want to remove this record?"))
				return true;
			
			$(this).parents("tr").first().fadeOut("fast", function()
			{
				var container = $(this).parents("table");
				
				$(this).remove();
				$(container).data("grid-table").reindex();
			});
		}
	};
	
	
	/* now, init the module! */
	$(function()
	{
		$(".grid-table").each(function()
		{
			return new Module(this);
		});
	});
})(window.jQuery);