@extends('layouts.doc-new')
@section('title', 'SPK')
@section('content')
<div class="container-fluid text-10">
@foreach ($template as $t)
    {!!$t->content!!}
    @if (!$loop->last)
    <div class="page-break"></div>
    <br><br><br><br>
    @endif
@endforeach
</div>
@endsection
