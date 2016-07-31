<html>
<head>
    <link href="css/dropzone.css" type="text/css" rel="stylesheet" />
    <link href="css/style.css" type="text/css" rel="stylesheet" />
    <script src="dropzone.js"></script>
</head>
<body>
<?php include_once("analyticstracking.php")
?>
<script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-15400226-7', 'auto');
    ga('send', 'pageview');
</script>
<div id="dropzone">
    <form action="worker.php" class="dropzone" id="upload">
        <div class="dz-message">
            Drop .txt log files here or click to upload.<br />
        </div>
    </form>
</div>
</body>
</html>
