@extends('layouts.layout-homepage.app')

<style>
    /* Custom Scrollbar */
    ::-webkit-scrollbar {
        width: 6px;
    }
    ::-webkit-scrollbar-track {
        background: #f1f5f9;
    }
    ::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    .event-card {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .event-card:hover {
        transform: translateY(-12px);
    }
    .glass-effect {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
    .text-gradient {
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .bg-grid {
        background-image: radial-gradient(#e2e8f0 1px, transparent 1px);
        background-size: 30px 30px;
    }
</style>

@section('homepage-section')
    <livewire:homepage.event-list />
@endsection
