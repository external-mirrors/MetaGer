<title>{{ Request::input('eingabe', '') }} - MetaGer</title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
</head>
<body>
    <iframe id="mg-framed" src="{{ $url }}" autofocus="true" onload="this.contentWindow.focus();"></iframe>
    <script nonce="{{ $mgv }}">
        document.querySelector("iframe").src += "&js=true";
    </script>
</body>
