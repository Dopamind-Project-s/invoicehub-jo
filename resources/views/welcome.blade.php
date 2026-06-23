@extends('layouts.guest')

@section('content')
    @include('landing.sections.hero', ['settings' => $settings])
    @include('landing.sections.statistics')
    @include('landing.sections.partners')
    @include('landing.sections.features')
    @include('landing.sections.integrations')
    @include('landing.sections.pricing', ['plans' => $plans])
    @include('landing.sections.testimonials')
    @include('landing.sections.faq', ['faqs' => $faqs])
    @include('landing.sections.cta', ['settings' => $settings])
@endsection
