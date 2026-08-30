@extends('errors.layout')

@section('code', '500')
@section('title', __('Er ging iets mis'))

{{-- Geen technische details en geen verontschuldiging in drie zinnen: wat de
     gebruiker nodig heeft is de bevestiging dat het gemeld is en een weg terug. --}}
@section('message', __('De fout is gemeld. Probeer het opnieuw, of neem contact op als het blijft gebeuren.'))
