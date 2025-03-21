<?xml version="1.0" encoding="UTF-8" ?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/" xmlns:moz="http://www.mozilla.org/2006/browser/search/">
        <ShortName>{{  $plugin_short_name }}</ShortName>
        <Description>{{ trans('plugin.description') }}</Description>
        <Image width="16" height="16" type="image/x-icon">{{ url('/favicon.ico') }}</Image>
        <Url type="text/html" template="{{ $link }}" method="GET" />
        <Url type="application/x-suggestions+json" template="{{ $suggestLink }}"/>
        <InputEncoding>UTF-8</InputEncoding>
</OpenSearchDescription>
