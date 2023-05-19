var interval = null;
var page;


$.fn.hasAttr = function(name) {  
   return this.attr(name) !== undefined;
};

$(document).ready(function(){	
     $("#loading").hide();
     $('a').click(function(e){
		 clearInterval(interval);
         e.preventDefault();    
		 page = $(this).attr('href');
		 $("#mainpage-load").empty();
		 $("#content-title").empty();
		 $("#content-title").prepend("Content of " . page);
		 if($(this).hasAttr('name'))
		 {			 
			Load($(this).attr('href'));
			interval = setInterval(function(){Tail(page)}, 10000);
		 }
		 else
		 {
			Load($(this).attr('href'));
		 }
     });
});

function Load(url)
{
    $("#loading").show();
    $("#mainpage-load").hide();
	$(document).load( url, function(resp, status, xhr){
            if (status == "success" && xhr.status == 200)
			{
				$("#mainpage-load").prepend(resp);
			}
            else{
                console.log("something wrong happend!");
            }
            $("#loading").hide();
            $("#mainpage-load").show();
        });
}

function Tail(url)
{
	$(document).load( url, function(resp, status, xhr){
            if (status == "success" && xhr.status == 200)
			{
				$("#mainpage-load").empty();	
				$("#mainpage-load").prepend(resp);
				$("#mainpage-load").animate({ scrollTop: $("#mainpage-load").prop("scrollHeight")}, 10000);
			}
            else{
                console.log("something wrong happend!");
            }
        });		
}