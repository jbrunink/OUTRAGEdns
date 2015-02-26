(function($)
{
	/* declare the module */
	var Module = function(table)
	{
		this.table = $(table);
		this.init();
	};
	
	Module.component = "grid-table";
	
	
	/* and then the methods */
	Module.prototype =
	{
		init: function()
		{
			var self = this;
			
			this.table.on("click." + Module.component, ".actions ul > li.clone", this.onclone);
			this.table.on("click." + Module.component, ".actions ul > li.remove", this.onremove);
			
			this.reindex();
			
			if(this.table.hasClass("sortable"))
			{
				var sortable =
				{
					axis: "y",
					items: "> tbody > tr:not(.placeholder-row)",
					containment: "parent",
					helper: "clone",
					handle: ".move-bar",
					
					start: function(event, ui)
					{
						var rows = ui.helper.find("td");
						
						$(this).find("thead th").each(function(index)
						{
							var row = rows.eq(index).css("width", $(this).width() + "px");
						});
						
						ui.helper.addClass("sortable-item");
						self.table.addClass("is-sorting");
					},
					
					update: function(event, ui)
					{
						self.reindex();
						self.table.removeClass("is-sorting");
					},
				};
				
				this.table.sortable(sortable);
				this.table.addClass("has-sortable");
			}
		},
		
		reindex: function()
		{
			var offset = 0;
			
			if(this.table.hasClass("has-sortable"))
				this.table.sortable("refresh");
			
			if(this.table.attr("data-grid-table-group"))
			{
				$("table.grid-table[data-grid-table-group = '" + this.table.attr("data-grid-table-group") + "']").find("tbody > tr").each(function(index)
				{
					$(this).find("[name]").each(function()
					{
						$(this).attr("name", $(this).attr("name").replace(/^(.*?)\[.*?\](.*?)$/, "$1[" + (offset + index) + "]$2"));
					});
				});
			}
			else
			{
				this.table.find("tbody > tr").each(function(index)
				{
					$(this).find("[name]").each(function()
					{
						$(this).attr("name", $(this).attr("name").replace(/^(.*?)\[.*?\](.*?)$/, "$1[" + (offset + index) + "]$2"));
					});
				});
			}
		},
		
		onclone: function(event)
		{
			event.preventDefault();
			
			var table = $(this).parents("tbody").first();
			var duplicate = $(this).parents("tr").first().clone().hide();
			
			duplicate.appendTo(table).fadeIn("fast");
			duplicate.inherit();
			
			$(this).parents("table").data(Module.component).reindex();
		},
		
		onremove: function(event)
		{
			event.preventDefault();
			
			if(!window.confirm("Do you want to remove this record?"))
				return true;
			
			var link = $(this).find("a");
			
			if(link.attr("href") && link.attr("href") != "#")
				return window.document.location = link.attr("href");
			
			$(this).parents("tr").first().fadeOut("fast", function()
			{
				var container = $(this).parents("table");
				
				$(this).remove();
				$(container).data(Module.component).reindex();
			});
		}
	};
	
	
	/* now, init the module */
	$.module.push([ "table.grid-table", Module ]);
})(window.jQuery);
