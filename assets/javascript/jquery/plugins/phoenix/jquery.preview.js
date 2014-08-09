/**
 *	A custom jQuery plugin I've made to allow any file upload
 *	to be turned into some sort of a blob.
 *
 *	I see this being very useful!
 */


!function($)
{
	$.fn.preview = function(target, onload)
	{
		var self = this;
		
		target = $(target).first();
		
		self.data("preview-target", target).on("click.preview", function(event)
		{
			event.preventDefault();
			
			var pointer = $(event.srcElement || event.target);
			
			if(!pointer.parents(".overlay").length)
			{
				if(target.length)
				{
					target.click();
				}
			}
		});
		
		target.on("change.preview", function(event)
		{
			self.find("img").remove();
				
			var href = $(this).is(":file") ? toURI(this) : $(this).val();
			var handler = onload;
			
			if(href)
			{
				var image = $("<img />").attr("src", href);
				
				image.css("position", "relative");
				image.css("max-width", "100%");
				image.css("max-height", "100%");
				
				image.appendTo(self);
				
				image.bind("load", function()
				{
					if(this.naturalWidth < this.naturalHeight)
					{
						image.css("padding-left", (self.width() - $(this).width()) / 2);
					}
					else
					{
						image.css("padding-top", (self.height() - $(this).height()) / 2);
					}
					
					window.URL.revokeObjectURL(this.src);
					
					if(handler)
					{
						handler.apply(self.get(0), [ this ]);
					}
				});
			}
		});
	};
	
	
	/**
	 *	Converts a file upload item to a blob.
	 */
	var toURI = function(target)
	{
		if(target && target.files)
		{
			try
			{
				return window.URL.createObjectURL(target.files[0]);
			}
			catch(exception)
			{
				return undefined;
			}
		}
		
		return undefined;
	};
}(jQuery);