(function() {
var el = document.createElement('div'); el.id = "supp-feedback";
var b = document.getElementsByTagName('body')[0];
otherlib = false;
msg = '';
var fileref = document.createElement("link");
fileref.setAttribute("rel", "stylesheet");
fileref.setAttribute("type", "text/css");
fileref.setAttribute("href", "http://pository.scott.ee/styles/bookmarklet.css");
document.getElementsByTagName("head")[0].appendChild(fileref);
if(typeof jQuery!='undefined') { return showMsg(); } else if (typeof $=='function') { otherlib=true; }
getScript('http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js',function() {
	if (typeof jQuery=='undefined') { alert('jQuery could not be loaded. Try again shortly'); return false;}
	return showMsg();
});
function showMsg() {
	if(jQuery("#supp-feedback").is(":visible")) { return false; }
	el.innerHTML = '<div id="supp-inner"><span id="supp-loading">Loading...</span></div>';
	b.appendChild(el);
	jQuery(el).fadeIn("slow", function() {
		jQuery.getJSON("http://pository.scott.ee/?q="+encodeURIComponent(location.href)+"&callback=?", 
		function(data) {
		   	el.innerHTML= '<div id="supp-inner"><a href="http://pository.scott.ee" title="Pository &raquo;"><img src="http://pository.scott.ee/img/' + data.icon + '" alt="' + data.icon + '" id="supp-icon"></a><span id="supp-score">' + data.score + '</span><span id="supp-text">' + data.feedback + '</span></div>';
		});
		jQuery(el).bind("click", function() {
			jQuery(el).fadeOut('slow', function() {
					b.removeChild(el);
			});
		});
	});	
	if (otherlib) {
		$jq=jQuery.noConflict();
	}
}
function getScript(url,success){
	var script=document.createElement('script');
	script.src=url;
	var head=document.getElementsByTagName('head')[0],
	done=false;
	script.onload=script.onreadystatechange = function(){
		if ( !done && (!this.readyState || this.readyState == 'loaded' || this.readyState == 'complete') ) {
			done=true;
			success();
		}
	};
	head.appendChild(script);
}
})();				