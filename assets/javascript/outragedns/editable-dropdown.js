(function($)
{
	/* declare the module */
	var Module = function(select)
	{
		this.select = $(select);
		
		this.input = undefined;
		this.trigger = undefined;
		
		this.init();
	};
	
	Module.component = "editable-dropdown";
	
	
	/* and then the methods */
	Module.prototype =
	{
		init: function()
		{
			var self = this;
			var selector = "data-" + Module.component + "-prompt";
			
			if(this.select.find("[" + selector + "]").length)
				this.trigger = this.select.find("[" + selector + "]").first();
			else
				this.trigger = $("<option></option>").text("Other").attr(selector, "1").appendTo(this.select);
			
			$([ "change", "focus" ]).each(function(index, item)
			{
				self.select.off(item + "." + Module.component + "-select").on(item + "." + Module.component + "-select", function(event)
				{
					return self.focus(event);
				});
			});
			
			self.select.parents("form").off("submit." + Module.component + "-form").on("submit." + Module.component + "-form", function()
			{
				$(this).find("[data-" + Module.component + "-inactive]").remove();
			});
			
			if(this.select.attr("data-" + Module.component + "-default"))
				this.val(this.select.attr("data-" + Module.component + "-default"));
		},
		
		focus: function()
		{
			if(this.select.find("> option:selected").index(this.trigger) != -1)
				this.spawninput();
		},
		
		blur: function()
		{
			if(this.input.val().length)
				return true;
			
			this.input.remove();
			this.input = undefined;
			
			this.select.show();
			this.select.removeAttr("data-" + Module.component + "-inactive");
			this.select.find("> option").removeAttr("selected").first().attr("selected", "selected");
		},
		
		val: function(value)
		{
			if(value === undefined)
			{
				if(this.input)
					return this.input.val();
				
				return this.select.val();
			}
			else
			{
				var current = this.select.find("option").filter(function()
				{
					return $(this).attr("value") == value;
				});
				
				if(current.length)
				{
					this.select.find("option").removeAttr("selected");
					current.attr("selected", "selected");
					
					return this;
				}
				
				return this.spawninput(value);
			}
		},
		
		spawninput: function(value)
		{
			var self = this;
			
			if(this.input)
				return this.input;
			
			this.input = $("<input></input>").attr("type", "text").attr("name", this.select.attr("name")).data(Module.component, this);
			
			if(this.select.attr("data-" + Module.component + "-class"))
				this.select.addClass(this.select.attr("data-" + Module.component + "-class"));
			
			if(this.select.attr("data-" + Module.component + "-placeholder"))
				this.input.attr("placeholder", this.select.attr("data-" + Module.component + "-placeholder"));
			
			if($.fn.uniform)
				$.uniform.restore(this.select[0]);
			
			this.select.hide();
			this.select.attr("data-" + Module.component + "-inactive", "inactive");
			this.select.after(this.input);
			
			this.input.on("blur", function() { return self.blur() });
			
			if(value !== undefined)
				this.input.val(value);
			
			this.select.trigger(Module.component + "-spawn-input", [ this.input ]);
			this.input.trigger("focus");
			
			return this.input;
		}
	};
	
	
	/* define as a module */
	$.module.push([ "select.editable-dropdown", Module ]);
})(window.jQuery);
