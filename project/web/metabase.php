<script type="text/javascript">
var xhttp = new XMLHttpRequest(); 
xhttp.open("POST", "/metabase/api/session", false);
xhttp.setRequestHeader("Content-type", "application/json");
xhttp.send('{"password":"<?php echo $_SERVER['PHP_AUTH_PW'] ?>","username":"<?php echo $_SERVER['PHP_AUTH_USER'] ?>","remember":true}');

var url = new URL(document.location.href);
var urlRedirect = url.searchParams.get('redirect');

console.log(urlRedirect);
if(urlRedirect) {
	var urlRedirect = new URL(urlRedirect);
	document.location.href = urlRedirect.href;
} else {
	document.location.href = '/metabase/';
}
</script>
