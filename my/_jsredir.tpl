<!doctype html>
<html>
<head>
<script>
if(screen.width < 500 ||
 navigator.userAgent.match(/Android/i) ||
 navigator.userAgent.match(/webOS/i) ||
 navigator.userAgent.match(/iPhone/i) ||
 navigator.userAgent.match(/iPod/i) ){
    window.location.href = '{{ url }}';
 }else{
    window.opener.location.href = '{{ url }}';
    {% if winclose %}window.close();{% endif %}
 }
</script>
</head>
<body>
</body>
</html>
