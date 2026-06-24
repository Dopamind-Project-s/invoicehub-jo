@extends('layouts.guest')

@section('content')
    @include('landing.sections.hero', ['settings' => $settings, 'heroSlides' => $heroSlides ?? []])
    @include('landing.sections.statistics', ['statistics' => $statistics ?? []])
    @include('landing.sections.partners', ['partners' => $partners ?? []])
    @include('landing.sections.features')
    @include('landing.sections.integrations', ['integrations' => $integrations ?? []])
    @include('landing.sections.pricing', ['plans' => $plans])
    @include('landing.sections.testimonials', ['testimonials' => $testimonials ?? []])
    @include('landing.sections.faq', ['faqs' => $faqs])
    @include('landing.sections.cta', ['settings' => $settings])
@endsection
