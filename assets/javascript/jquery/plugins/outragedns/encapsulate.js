(function($)
{
	// processes the fields, submits the iframe and deals with the initial response
	var createRequest = function(iframe, params, callback)
	{
		var dom = iframe.contents().get(0);
		
		iframe.one("load", function()
		{
			var dom = $(this).contents().get(0);
			
			if(dom)
				callback($(dom.body).text().toString());
		});
		
		var packet = $("<form></form>").attr("method", params.method || "POST").attr("action", params.action || window.location);
		
		if(this.find("input[type = 'file']").length > 0)
			packet.attr("enctype", "multipart/form-data");
		
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
		
		$(dom.body).append(packet);
		
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
	};
	
	// create our initial iframe
	var handler = function(params, response)
	{
		var self = this;
		var iframe = $("<iframe></iframe>");
		
		iframe.css("visibility", "hidden");
		iframe.css("height", "0px");
		iframe.appendTo("body");
		
		iframe.one("load", function()
		{
			var callback = function(output)
			{
				if(output)
				{
					switch(params.returnType)
					{
						case "map":
							var method = new Function("return (" + output + ")");
							
							response.apply(self, [ method.apply(undefined) ]);
						break;
						
						case "json":
							response.apply(self, [ $.parseJSON(output) ]);
						break;
						
						default:
							response.apply(self, [ output ]);
						break;
					}
				}
				
				iframe.remove();
			};
			
			createRequest.apply(self, [ $(this), params, callback ]);
		});
		
		iframe.attr("src", "about:blank");
		
		return this;
	};
	
	// wraps calls from jQuery
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