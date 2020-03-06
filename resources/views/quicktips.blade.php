<!DOCTYPE html>
<html lang="{{ LaravelLocalization::getCurrentLocale() }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link type="text/css" rel="stylesheet" href="{{ mix('css/quicktips.css') }}" />
  <link href="/fonts/liberationsans/stylesheet.css" rel="stylesheet">
	<link type="text/css" rel="stylesheet" href="{{ mix('css/fontawesome.css') }}" />
	<link type="text/css" rel="stylesheet" href="{{ mix('css/fontawesome-solid.css') }}" />
</head>
<body id="quicktips">
@foreach($quicktips as $quicktip)
  <div class="quicktip" type="{{ $quicktip->type }}">
    @include('parts.quicktip', ['quicktip' => $quicktip])
  </div>
@endforeach
</body>
</html>
