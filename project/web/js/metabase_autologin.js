function metabase_redirect_to_external_auth(metabase_webpath, metabase_external_auth_page) {
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
            document.location.href = metabase_external_auth_page + '?redirect=' + encodeURIComponent(url);
		}
	}
}
