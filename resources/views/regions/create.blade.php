@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-3">
    </div>
  <div class="col-md-6">
    <h2 class="page-header">Tambah Data Regional
    </h2>
        {!! Form::open(['route' => 'master.region.store']) !!}
        <div class="form-group">
            {!! Form::label('code', 'Kode Regional:') !!}
            {!! Form::text('code',null,['class'=>'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::label('name', 'Nama Regional:') !!}
            {!! Form::text('name',null,['class'=>'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::label('abbreviation', 'Singkatan:') !!}
            {!! Form::text('abbreviation',null,['class'=>'form-control']) !!}
        </div>
        <div class="form-group">
          {!! Form::label('date_open', 'Tanggal Buka:') !!}
          {!! Form::text('date_open',null,['id'=>'date-open','class'=>'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::submit('Simpan', ['class' => 'btn btn-primary form-control']) !!}
        </div>
        {!! Form::close() !!}
  </div>
</div>
<script>
$(document).ready(function() {
  $('#date-open').datepicker({
    format: "dd/mm/yyyy",
    language: "id"
  });
});
</script>

@endsection
