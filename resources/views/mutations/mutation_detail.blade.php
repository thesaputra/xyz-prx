@extends('layouts.app')

@section('content')
<div class="row">
  <h3 class="page-header">Mutasi Barang <small>Data Mutasi - Mutasi Item</small></h3>
  <div class="col-md-12">
    <table class="table table-bordered">
      <tr>
        <th>No Mutasi:</th>
        <td>{{$mutation->no_mutasi}}</td>
        <th>Tanggal Mutasi:</th>
        <td>{{$mutation->date_mutation}}</td>
      </tr>
      <tr>
        <th>Kantor Asal:</th>
        <td>{{$mutation->office_sender}}</td>
        <th>Kantor Tujuan:</th>
        <td>{{$mutation->office_destination}}</td>
      </tr>
    </table>
  </div>

  <ul class="nav nav-tabs" role="tablist">
    <li>
      <a href="#add-data" role="tab" data-toggle="tab">
        <icon class="fa fa-home"></icon> Tambah Mutasi Barang
      </a>
    </li>
    <li  class="active"><a href="#lookup-data" role="tab" data-toggle="tab">
      <i class="fa fa-user"></i> List Mutasi Barang
    </a>
  </li>
</ul>

<!-- Tab panes -->
<div class="tab-content">
  <div class="tab-pane" id="add-data">
    <div class="col-md-12">
      <h4 class="page-header">Tambah Mutasi Barang</h4>
    </div>
    {!! Form::open(['route' => 'mutation.store_detail_mutation']) !!}

    <div class="col-md-4">
      <div class="form-group">
        {!! Form::label('transaction_item_id', 'Nama Barang:') !!}
        {!! Form::text('item_name',null,['class'=>'form-control','id'=>'item']) !!}

        {!! Form::hidden('transaction_item_id',null,['class'=>'form-control','id'=>'item_id']) !!}

        {!! Form::hidden('mutation_id',$mutation->id,['class'=>'form-control']) !!}
      </div>
      <div class="form-group">
        {!! Form::label('qty', 'Jumlah Barang:') !!}
        {!! Form::text('qty',null,['class'=>'form-control']) !!}
      </div>
      <div class="form-group">
        {!! Form::submit('Simpan', ['class' => 'btn btn-primary form-control']) !!}
      </div>
    </div>
    {!! Form::close() !!}

  </div>
  <div class="tab-pane fade active in" id="lookup-data">
    <div class="col-md-12">
      <h4 class="page-header">List Data Mutasi Barang</h4>

      <table class="table table-striped table-bordered table-hover" id="mutation-detail-table">
        <thead>
          <tr class="bg-info">
            <th>No</th>
            <th>Nama Barang</th>
            <th>Jumlah Barang</th>
            <th>Aksi</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>

</div>

<script>
$(document).ready(function() {
  $('#mutation-detail-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{!! route('mutation.mutation_detail_data') !!}',
    columns: [
      {data: 'rownum', name: 'rownum',searchable: false},
      { data: 'item_name', name: 'item_name' },
      { data: 'qty', name: 'qty' },
      { data: 'action', name: 'action', orderable: false, searchable: false}
    ]
  });
});
</script>
<script>
$(document).ready(function() {
  $('#mutation-detail-table').on('click', '#btn-delete[data-remote]', function (e) {
    e.preventDefault();
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': '{!! csrf_token() !!}'
        }
      });
      var url = $(this).data('remote');
      if (confirm('Anda yakin?')) {
        $.ajax({
          url: url,
          type: 'DELETE',
          dataType: 'json',
          data: {method: '_DELETE', submit: true}
        }).always(function (data) {
          $('#mutation-detail-table').DataTable().draw(false);
        });
      }
    });

  autocomplete_item();

});

function autocomplete_item(){
  var substringMatcher = function(strs) {
    return function findMatches(q, cb) {
      var matches, substringRegex;
      matches = [];
      substrRegex = new RegExp(q, 'i');
      $.each(strs, function(i, str) {
        if (substrRegex.test(str)) {
          matches.push(str);
        }
      });
      cb(matches);
    };
  };

  var arr1 = [];
  $("#item").typeahead({
    hint: false,
    highlight: true,
    minLength: 2

  },
  {
    limit: 50,
    async: true,
    templates: {notFound:"Data not found"},
    source: function (query, processSync, processAsync) {
      return $.ajax({
        url: '{!! route("mutation.mutation_autocomplete") !!}',
        type: 'GET',
        data: {"term": query},
        dataType: 'json',
        success: function (json) {
          var _tmp_arr = [];
          json.map(function(item){
            _tmp_arr.push(item.name)
            arr1.push({id: item.id, st: item.name})
          })
          return processAsync(_tmp_arr);
        }
      });
    }
  })
  $("#item").on('typeahead:selected', function (e, code) {
    arr1.map(function(i){
      if (i.st == code){
        $("#item_id").val(i.id);
      }
    })

    if(e.keyCode==13){
      arr1.map(function(i){
        if (i.st == code){
          $("#item_id").val(i.id);
        }
      })
    }
  })
}
</script>

@endsection
