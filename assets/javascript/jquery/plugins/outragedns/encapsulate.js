(function($)
{
	var handler = function(params, response)
	{
		var self = $(this);
		
		var container = $("<iframe></iframe>").css("display", "none").appendTo("body");
		var packet = $("<form></form>").attr("method", params.method || "POST").attr("enctype", "multipart/form-data").attr("action", params.action || window.location);
		
		if(params.fields)
		{
			for(var key in params.fields)
				$("<input />").attr("type", "hidden").attr("name", key).val(params.fields[key]).appendTo(packet);
		}
		
		if(this.length)
		{
			this.filter(":input").each(function()
			{
				var clone = $(this).clone();
				var parent = $(this).parent();
				
				packet.append(this);
				parent.append(clone);
			});
		}
		
		container.contents().find("body").append(packet);
		
		if(params.submit)
		{
			params.submit = $(params.submit);
			
			if(params.submitDataProperty)
			{
				packet.find(":submit").filter(function()
				{
					return params.submit.is($.data(this, params.submitDataProperty));
				}).first().trigger("click");
			}
			else
			{
				packet.find(":submit").filter(params.submit).first().trigger("click");
			}
		}
		else
		{
			packet.trigger("submit");
		}
		
		container.one("load", function()
		{
			if(response)
			{
				var output = container.contents().text().toString();
				
				switch(params.returnType)
				{
					case "map":
						var method = new Function("return (" + output + ")");
						
						response.apply(self, [ method.apply(undefined) ]);
					break;
					
					case "json":
						response.apply(self, [ $.parseJSON($(this).contents().text()) ]);
					break;
					
					default:
						response.apply(self, [ output ]);
					break;
				}
			}
			
			container.remove();
		});
		
		return this;
	};
	
	$.fn.encapsulate = function(params, response)
	{
		if(response == null)
		{
			response = params || function() {};
			params = {};
		}
		
		return handler.apply(this, [ params, response ]);
	};
})(window.jQuery);