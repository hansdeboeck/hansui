@extends('errors.layout')

@section('code', '404')
@section('title', __('Niet gevonden'))

{{-- "Bestaat niet OF u hebt er geen toegang toe" is geen omfloerste
     formulering maar de kern van het ontwerp: buiten het eigen organisatie- of
     afdelingsbereik antwoordt de applicatie bewust met 404 en niet met 403.
     Een pagina die hier "deze pagina bestaat niet" beweert, spreekt dat tegen en
     verklapt bovendien het omgekeerde zodra ze het een keer niet doet. --}}
@section('message', __('Deze pagina bestaat niet, of ze hoort niet bij uw organisatie.'))
