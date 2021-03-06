<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Session;
use App\Http\Controllers\Controller;
use App\Models\Mutation;
use App\Models\MutationItem;

use App\Models\Office;
use App\Models\OfficeDivision;

use Yajra\Datatables\Datatables;
use Carbon\Carbon;
use Auth;

class MutationController extends Controller
{
  public function index()
  {
    return view('mutations.index');
  }

  public function mutation_data()
  {
    \DB::statement(\DB::raw('set @rownum=0'));
    $datas = Mutation::select([
      \DB::raw('@rownum  := @rownum  + 1 AS rownum'),
      'id',
      'no_mutasi',
      'date_mutation',
      'status'
    ]);
    return Datatables::of($datas)
    ->addColumn('action', function ($data) {
      return '
      <a href="./edit/'.$data->id.'" class="btn btn-xs btn-warning"><i class="glyphicon glyphicon-edit"></i> Edit</a>
      <a href="./mutation_detail/'.$data->id.'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Detail Barang Mutasi</a>
      ';
    })
    ->editColumn('date_mutation', function ($data) {
                  return $data->date_mutation ? with(new Carbon($data->date_mutation))->format('d/m/Y') : '';
            })
    ->make(true);
  }


  public function index_sent()
  {
    return view('mutations.index_sent');
  }

  public function sent_mutation_data()
  {
    \DB::statement(\DB::raw('set @rownum=0'));
    $datas = Mutation::select([
      \DB::raw('@rownum  := @rownum  + 1 AS rownum'),
      'id',
      'no_mutasi',
      'date_mutation',
      'status'
    ]);
    return Datatables::of($datas)
    ->addColumn('action', function ($data) {
      return '
      <button id="btn-delete" class="btn btn-xs btn-danger" data-remote="./update_sent_mutation/' . $data->id . '">Konfirmasi</button>
      ';
    })
    ->editColumn('date_mutation', function ($data) {
                  return $data->date_mutation ? with(new Carbon($data->date_mutation))->format('d/m/Y') : '';
            })
    ->where('status','=','dikirim')
    ->make(true);
  }

  public function create()
  {
    $current_office_id = Auth::user()->office_id;
    $current_division_id = Auth::user()->division;

    $valid_office = Office::where('id', $current_office_id)->first()->name;
    $valid_division = OfficeDivision::where('id', $current_division_id)->first()->name;

    return view('mutations.create',compact('valid_office','valid_division'));
  }

  public function store(Request $request)
  {
    $this->store_validation_rules($request);
    $date_mutation = $this->saved_date_format($request->input('date_mutation'));

    $no_urut = Mutation::all()->count();
    $unit_kerja = $request->input('division_sender');
    $per_kerja = 'PER9';
    $tahun = Carbon::now()->toDateTimeString();
    $format = Carbon::parse($tahun)->format('y');

    $before_urut = '00';
    if ($no_urut > 9) {
      $before_urut = '00';
    } elseif ($no_urut > 99) {
      $before_urut = '0';
    } elseif ($no_urut > 999) {
      $before_urut = '';
    }
    $no_urut = $no_urut+1;
    $no_mutasi = $before_urut.$no_urut.'/'.$unit_kerja.'/'.$per_kerja.'/'.$format;

    $request->merge(array('date_mutation'=>$date_mutation,'no_mutasi'=>$no_mutasi));

    $data=$request->input();
    Mutation::create($data);

    Session::flash('flash_message', 'Data berhasil ditambahkan!');

    return redirect('mutation/index');
  }


  public function edit($id)
  {
    $current_office_id = Auth::user()->office_id;
    $current_division_id = Auth::user()->division;

    $valid_office = Office::where('id', $current_office_id)->first()->name;
    $valid_division = OfficeDivision::where('id', $current_division_id)->first()->name;


    $mutation=Mutation::find($id);
    return view('mutations.edit',compact('mutation','valid_office','valid_division'));
  }


  public function update(Request $request, $id)
  {
    $this->update_validation_rules($request);

    $date_mutation = $this->saved_date_format($request->input('date_mutation'));
    $request->merge(array('date_mutation'=>$date_mutation));

    $data_update=$request->input();
    $data=Mutation::find($id);
    $data->update($data_update);

    Session::flash('flash_message', 'Data berhasil diupdate!');

    return redirect('mutation/index');
  }

  public function update_sent_mutation(Request $request, $id)
  {
    $request->merge(array('status'=>'diterima'));

    $data_update=$request->input();
    $data=Mutation::find($id);
    $data->update($data_update);

    Session::flash('flash_message', 'Data berhasil diupdate!');

    return redirect('mutation/index');
  }


  public function destroy($id)
  {
    //
  }

  public function mutation_detail($id)
  {
    $mutation = Mutation::where('mutations.id', '=', $id)
    ->select('mutations.id','mutations.no_mutasi','mutations.office_sender','mutations.division_sender','mutations.office_destination','mutations.division_destination','mutations.date_mutation','mutations.status')
    ->firstOrFail();

    return view('mutations.mutation_detail',compact('mutation'));
  }

  public function mutation_detail_data($id)
  {
    \DB::statement(\DB::raw('set @rownum=0'));
    $datas = MutationItem::select([
      \DB::raw('@rownum  := @rownum  + 1 AS rownum'),
      'mutation_items.id',
      'transaction_items.name as item_name',
      'mutation_items.qty'
    ]);
    return Datatables::of($datas)
    ->addColumn('action', function ($data) {
      return '
      <a href="./edit_mutation_detail/'.$data->id.'" class="btn btn-xs btn-warning"><i class="glyphicon glyphicon-edit"></i> Edit</a>
      ';
    })
    ->join('transaction_items','mutation_items.transaction_item_id','=','transaction_items.id')
    ->make(true);
  }

  public function mutation_autocomplete(Request $request)
  {
    $term = $request->term;

    $results = array();

    $queries = \DB::table('transaction_items')
    ->where('name', 'LIKE', '%'.$term.'%')
    ->take(10)->get();

    foreach ($queries as $query)
    {
      $results[] = [ 'id' => $query->id, 'name' => $query->name ];
    }

    return response()->json($results);
  }

  public function store_detail_mutation(Request $request)
  {

    $data=$request->input();
    MutationItem::create($data);

    Session::flash('flash_message', 'Data berhasil ditambahkan!');

    return redirect()->back();
  }

  private function store_validation_rules($request)
  {
    $this->validate($request, [
      'date_mutation' => 'required'
    ]);
  }

  private function update_validation_rules($request)
  {
    $this->validate($request, [
      'date_mutation' => 'required'
    ]);
  }

  private function saved_date_format($date)
  {
    $date_split = explode('/',$date);

    $year = $date_split[2];
    $month = $date_split[1];
    $day = $date_split[0];

    $format = $year.'-'.$month.'-'.$day;

    return $format;
  }
}
