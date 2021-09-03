var xhttp = new XMLHttpRequest();
xhttp.open("GET", metabase_webpath + "/api/user/current", true);
xhttp.send();
xhttp.onload = function() {
	if(xhttp.status != 401) {
		return;
	}
		var url = new URL(document.location.href);
		if(!url.search.match(/noautologin/)) {
        		url.searchParams.append('noautologin', '1');
        		document.location.href = metabase_authscript + '?redirect=' + encodeURIComponent(url);
		}
}
