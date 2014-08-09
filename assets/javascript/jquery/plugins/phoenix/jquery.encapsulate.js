/**
 *	A custom jQuery plugin I've made to encapsulate any AJAX form request.
 */


!function($)
{
	$.fn.encapsulate = function(params, response)
	{
		if(response == null)
		{
			response = params || function() {};
			params = {};
		}
		
		return performer.apply(this, [ params, response ]);
	};
	
	
	var performer = function(params, response)
	{
		var self = $(this);
		
		var container = $("<iframe></iframe>").css("display", "none").appendTo("body");
		var packet = $("<form></form>").attr("method", params.method || "POST").attr("enctype", "multipart/form-data").attr("action", params.action || window.location);
		
		if(params.fields)
		{
			for(var key in params.fields)
			{
				$("<input />").attr("type", "hidden").attr("name", key).val(params.fields[key]).appendTo(packet);
			}
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
		packet.submit();
		
		container.one("load", function()
		{
			if(response)
			{
				var output = container.contents().text().toString();
				
				switch(params.returnType)
				{
					case "map":
					{
						var method = new Function("return (" + output + ")");
						
						response.apply(self, [ method.apply(undefined) ]);
						break;
					}
					
					case "json":
					{
						response.apply(self, [ $.parseJSON($(this).contents().text()) ]);
						break;
					}
					
					default:
					{
						response.apply(self, [ output ]);
						break;
					}
				}
			}
			
			container.remove();
		});
		
		return this;
	};
}(jQuery);