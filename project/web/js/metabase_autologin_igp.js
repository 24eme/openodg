var url = new URL(document.location.href);


window.onpopstate = function(event) {
  console.log("location: " + document.location + ", state: " + JSON.stringify(event.state));
};


var xhttp = new XMLHttpRequest();
xhttp.open("GET", "/metabase/api/user/current", true);
xhttp.send();
xhttp.onload = function() {
	if(xhttp.status != 401) {
		return;
	}
		var url = new URL(document.location.href);
		if(!url.search.match(/noautologin/)) {
        		var igp = url.host.replace(/^([^\.]+)\..+$/, "$1");
        		url.searchParams.append('noautologin', '1');
				
        		document.location.href='/exports_igp' + igp + '/metabase.php?redirect=' + encodeURIComponent(url);
		}
}
