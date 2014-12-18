(function($)
{
	/* declare the module */
	var Module = function(form)
	{
		this.form = $(form).data(Module.component, this);
		this.init();
	};
	
	Module.component = "validate-form";
	
	/* and then the methods */
	Module.prototype =
	{
		init: function()
		{
			var self = this;
			
			this.form.find(":submit").on("click." + Module.component, function(event)
			{
				$.data(this.form, Module.component + "-source", this);
			});
			
			this.form.on("submit." + Module.component, function(event)
			{
				var source = $.data(this, Module.component + "-source") || undefined;
				
				$.removeData(this, Module.component + "-source");
				
				if($.data(this, Module.component + "-success"))
					return true;
				
				event.preventDefault();
				
				return self.submit(source);
			});
		},
		
		submit: function(source)
		{
			var self = this;
			var elements = [];
			
			this.form.find("div.control-group").each(function()
			{
				var element = $(this).removeClass("error");
				
				element.find("div.controls > .error-text").remove();
				element.find("div.controls > div.control-wrap > *").removeClass("invalid-content").tipsy("disable");
			});
			
			this.form.find(":input[name]").not(":disabled").each(function()
			{
				var clone = $(this).clone().data(Module.component + "-element", this);
				
				if(!clone.val())
					clone.val($(this).val());
				
				elements.push(clone[0]);
			});
			
			var params =
			{
				action: this.form.attr("action") || window.location,
				
				submit: source,
				submitDataProperty: Module.component + "-element",
				
				fields:
				{
					"::validate": new Date().getTime()
				},
				
				returnType: "json"
			};
			
			$(elements).encapsulate(params, function(response)
			{
				if(typeof response == "object")
				{
					if("errors" in response && typeof response.errors == "object")
					{
						for(var name in response.errors)
						{
							if(response.errors[name].length)
								self.tooltip(self.form.find("[name = '" + name + "']"), response.errors[name]);
						}
					}
					else
					{
						self.form.data(Module.component + "-success", true);
						
						source ? self.form.find(":submit").filter(source).trigger("click") : self.form.trigger("submit");
					}
				}
			});
		},
		
		tooltip: function(element, errors)
		{
			var message = errors.join("<br />");
			
			element.addClass("error invalid-content");
			
			if(element.data("tipsy"))
			{
				var tipsy = element.data("tipsy");
				
				tipsy.options.fallback = message;
				tipsy.enable();
				tipsy.show();
			}
			else
			{
				var tooltip =
				{
					fallback: message,
					html: true,
					fade: true,
					focus: true
				};
				
				element.tipsy(tooltip).tipsy("enable").one("keydown", function()
				{
					$(this).removeClass("error invalid-content").tipsy("disable");
				});
			}
			
			return element.data("tipsy");
		}
	};
	
	
	/* now, init the module */
	$(function()
	{
		$("form.validate-form").each(function()
		{
			return new Module(this);
		});
	});
})(window.jQuery);