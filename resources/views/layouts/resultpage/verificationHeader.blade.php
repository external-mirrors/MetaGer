<!DOCTYPE html>
<html lang="{!! trans('staticPages.meta.language') !!}">
<head>
    <meta charset="UTF-8">
    <noscript>
    <link rel="stylesheet" href="/index.css?id={{ $key }}">
    </noscript>
    <script>
        var link = document.createElement("link");
        link.rel = "stylesheet";
        link.href = "/index-js.css?id={{ $key }}";
        document.head.appendChild(link);
    </script>
