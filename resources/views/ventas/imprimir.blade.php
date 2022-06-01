<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{$file}}</title>
</head>
<body>
    <p>Imprimiendo...</p>
</body>
</html>
<script>
    let iframe = document.createElement('iframe');
    document.body.appendChild(iframe);
    iframe.style.display = 'none';
    iframe.onload = function() {
        setTimeout(function() {
            iframe.focus();
            iframe.contentWindow.print();
        }, 0);
    };
    iframe.src = "<?php echo '/pdf/PRINT_DOCUMENT.pdf'?>";

    //setTimeout(function () { window.close(); }, 3000);
</script>