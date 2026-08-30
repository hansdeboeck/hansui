@extends('errors.layout')

@section('code', '419')
@section('title', __('De pagina is verlopen'))

{{-- 419 is bijna altijd een formulier dat te lang open stond. De gebruiker heeft
     niets fout gedaan en hoeft dat ook niet te lezen. --}}
@section('message', __('U bent te lang weg geweest. Meld u opnieuw aan en probeer het nog eens.'))
