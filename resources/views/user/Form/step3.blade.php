@extends('layouts.app')

@section('content')

<div class="container bg-white rounded shadow-sm">
    <h2 class="my-2 pb-2 border-bottom border-dark">Contact gegevens</h2>
    <div class="form-row border-bottom pt-2">
        <div class="form-group col-md-8">
            <label class="form-label">E-mail adres (van de school)</label>
            {{ Form::email('txtEmail', '', ['id'=>'txtEmail','oninput'=>'this.className', 'class' => 'mb-2 form-control '])}}
        </div>
        <div class="form-group col-md-4">
            <label class="form-label">GSM-nummer</label>
            {{ Form::text('txtGsm', '', ['id'=>'txtGsm','oninput'=>'this.className', 'class' => 'mb-2 form-control '])}}
        </div>
    </div>
    <div class="form-row border-bottom pt-2">
        <div class="form-group col-md-6">
            <label class="form-label">Noodnummer 1</label>
            {{ Form::text('txtNoodnummer1', '', ['id'=>'txtNoodnummer1','oninput'=>'this.className', 'class' => 'mb-2 form-control '])}}
        </div>

        <div class="form-group col-md-6">
            <label class="form-label">Noodnummer 2</label>
            {{ Form::text('txtNoodnummer2', '', ['id'=>'txtNoodnummer2','oninput'=>'this.className', 'class' => 'mb-2 form-control '])}}
        </div>
    </div>
    <h2 class="my-2 pb-2 border-bottom border-dark">Medische gegevens</h2>
    <div class="form-row border-bottom pt-2">
        <div class="form-group col-md-12">
        <label class="form-label">Heeft u een operatie gehad in het afgelopen jaar of andere medische aandoening? (Allergie, ziekte, ...)</label><br>
        <div>
            {{ Form::radio('check', '1', ['id'=>'radioMedisch','oninput'=>'this.className'])}}Ja
            {{ Form::radio('check', '0', ['id'=>'radioMedisch','oninput'=>'this.className'])}}Nee
        </div>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-12">
        <label class="formLabel">Wat houden deze in?</label>
            <br/>
        {{ Form::textarea('txtMedisch', '', ['id'=>'txtMedisch','oninput'=>'this.className','placeholder'=>'Niet verplicht'])}}</p>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-12 float-right">
            <a class = "btn btn-secondary form-control col-sm-2 mb-4 mt-2" href="{{url()->previous()}}">Vorige</a>
            {{ Form::submit('Registreer',['class' => 'btn btn-primary form-control col-sm-2 mb-4 mt-2 ']) }}
        </div>
    </div>
    {{ Form::close() }}
</div>

@endsection