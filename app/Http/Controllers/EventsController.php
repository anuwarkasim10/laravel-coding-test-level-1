<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use DataTables;

class EventsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $events = Event::orderby('created_at', 'asc')->get();

        // dd($request->ajax());
        // $new = Event::where('uuid', '=', '7504101')->get();
        // dd($new);
        if ($request->ajax()) {
            $data = Event::latest()->get();

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($data){
                    $actionBtn = '<a href="/events/'.$data->id.'/edit" class="edit btn btn-success btn-sm">Edit</a> <a data-id="'.$data->id.'" class="delete btn btn-danger btn-sm" id="btn-delete">Delete</a>';
                    return $actionBtn;
                })
                ->addColumn('custom_id', function($data){
                    $actionBtn = '<a href="/events/show/'.$data->id.'" id="btn-show">'.$data->id.'</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action', 'custom_id'])
                ->make(true);
        }


        return view('events.index');


        // return view('events.index', compact('events'));
    }

    public function indexActive()
    {
        $events = Event::whereDate('start_at','<=', now())
        ->whereDate('end_at','>=', now())
        ->get();;

        return response()->json([
            'code' => 200,
            'success' => true,
            'result' => $events
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('events.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());
        try {
            $result = uniqid();
            $events = Event::create([
                'id' => Str::uuid(),
                'name' => $request->name,
                'slug' => Str::slug($request->name).$result,
                'start_at' => $request->start_at,
                'end_at' => $request->end_at,
            ]);



        } catch (\Throwable $th) {
            // dd($th);
            //throw $th;
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'success' => false,
                'message' => 'Data failed to create!'
            ]);
        }
        return redirect()->route('events.events-index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $events = Event::find($id);

        return view('events.show', compact('events'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $events = Event::find($id);
        $start_at = date("d/m/Y", strtotime($events->start_at));
        $end_at = date("d/m/Y", strtotime($events->end_at));
        // dd(date("m-d-Y", strtotime($events->start_at)));
        // dd(date_format($start_at, "d/m/Y"));
        return view('events.edit', compact('events', 'start_at', 'end_at'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateCreate(Request $request, $id)
    {

        $events = Event::find($id);

        try{
            if(isset($events)){
                $events->name = $request->name ? $request->name : $events->name;
                $events->slug = $request->slug ? $request->slug : $events->slug;
                $events->start_at = $request->start_at ? $request->start_at : $events->start_at;
                $events->end_at = $request->end_at ? $request->end_at : $events->end_at;
                $events->save();
            }else{
                $events = Event::create([
                    'id' => $id,
                    'name' => $request->name,
                    'slug' => Str::slug($request->slug),
                    'start_at' => $request->start_at,
                    'end_at' => $request->end_at,
                ]);
            }

            return response()->json([
                'code' => 200,
                'success' => true,
                'data' => $events,
                'message' => 'Data has been updated/created successfully!'
            ]);

        }catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'success' => false,
                'data' => [],
                'message' => 'Data failed to update/create successfully.'
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        try{
            $result = uniqid();
            $events = Event::find($id);
            $events->name = $request->name ? $request->name : $events->name;
            $events->slug = $request->name ? Str::slug($request->name).$result : $events->slug;
            $events->start_at = $request->start_at ? $request->start_at : $events->start_at;
            $events->end_at = $request->end_at ? $request->end_at : $events->end_at;
            $events->save();

            return redirect()->route('events.events-index');

        }catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'success' => false,
                'data' => [],
                'message' => 'Data failed to update.'
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $events = Event::findOrFail($id);

        try{
            $events->delete();

        }catch (\Throwable $th) {
            return response()->json([
                'code' => 500,
                'success' => false,
                'data' => [],
                'message' => 'Ops!!! Something is wrong.'
            ]);
        }
        return redirect()->route('events.events-index');
    }
}

