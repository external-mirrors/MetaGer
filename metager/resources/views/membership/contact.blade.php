@extends('layouts.subPages')

@section('title', $title )

@section('navbarFocus.donate', 'class="dropdown active"')

@section('content')
<h1 class="page-title">@lang('membership.title')</h1>
<div class="page-description">@lang('membership.description')</div>
<div id="contact-data">
    <h3>@lang('membership.contact.title')</h3>
   <div class="input-group">
    <label for="name">@lang('membership.contact.name.label')</label>
    <input type="text" name="name" id="name" placeholder="@lang('membership.contact.name.placeholder')" required />
   </div>
   <div class="input-group">
    <label for="email">@lang('membership.contact.email.label')</label>
    <input type="email" name="email" id="email" placeholder="@lang('membership.contact.email.placeholder')" required />
   </div>
   <h3>@lang('membership.fee.title')</h3>
   <div class="input-group">
    <label for="amount">@lang('membership.fee.amount.label')</label>
    <input type="number" name="amount" id="amount" step="0.01" min="5" value="5,00" placeholder="@lang('membership.fee.amount.placeholder')" required />
   </div>
</div>
@endsection