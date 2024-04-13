@extends('mailcarrier::livewire.layout')
@section('content')
    <livewire:mailcarrier::preview-template :token="$token" />
@endsection
