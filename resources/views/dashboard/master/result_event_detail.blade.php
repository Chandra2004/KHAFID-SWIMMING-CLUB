@extends('layouts.layout-dashboard.app', ['title' => 'Hasil ' . $event->name . ' | Khafid Swimming Club'])
@section('dashboard-section')
    <livewire:dashboard.management-result-event-detail :event_uid="$event_uid" />
@endsection
