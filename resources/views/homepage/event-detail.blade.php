@extends('layouts.layout-homepage.app')

@section('homepage-section')
    @livewire('homepage.event-detail', ['slug' => request()->route('slug'), 'uid' => request()->route('uid')])
@endsection
