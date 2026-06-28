@extends('layouts.layout-dashboard.app', ['title' => 'Detail Lomba ' . $event->name . ' | Khafid Swimming Club'])

@section('dashboard-section')
    <livewire:dashboard.management-lomba :event="$event" />
@endsection
