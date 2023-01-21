<?php

namespace App\Http\Controllers;

use Notification;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Notifications\EmailNotification;
use Illuminate\Support\Facades\Http;

use GuzzleHttp\Client;
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
        try {
            $result = uniqid();
            $events = Event::create([
                'id' => Str::uuid(),
                'name' => $request->name,
                'slug' => Str::slug($request->name).$result,
                'start_at' => $request->start_at,
                'end_at' => $request->end_at,
            ]);

            Redis::set('event_' . $events->id, $events);

            $user = User::first();

            $project = [
                'greeting' => 'Hi '.$user->name.',',
                'body' => 'Event created successfully',
                'details' => 'Even Name :' .$events->name.', Even Start Date :' .$events->start_at.', Event End Date :' .$events->end_at.',',
                'thanks' => 'Thank you.',
                'actionText' => 'View Project',
                'actionURL' => url('/'),
                'id' => 57
            ];

            Notification::send($user, new EmailNotification($project));

        } catch (\Throwable $th) {
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
        try {
            $cachedEvent = Redis::get('event_' . $id);

            if(isset($cachedEvent)) {
                $events = json_decode($cachedEvent, FALSE);
                return view('events.show', compact('events'));
            }else {
                $events = Event::find($id);
                Redis::set('event_' . $id, $events);
                return view('events.show', compact('events'));
            }

        } catch (\Throwable $th) {
            return response()->json([
                'code' => 500,
                'success' => false,
                'message' => 'Data failed to find and cached!'
            ]);
        }
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

            if($events) {
                Redis::del('event_' . $id);
                Redis::set('event_' . $id, $events);
            }

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
            Redis::del('event_' . $id);
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

    public function getApi()
    {
        $url = 'https://restcountries.com/v3.1';
        $collection_name = 'all';
        $request_url = $url . '/' . $collection_name;
        $client = new Client();
        try {

            $res = $client->get($request_url, [

                'headers' => [
                    'Content-type' => 'application/json',
                ]
            ]);

            $result = json_decode($res->getBody()->getContents());

            return view('events.third-party', compact('result'));


        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }



    }

    public function chartApi($market)
    {
        $baseURL = 'https://api.bitcoinupbit.com';
        $request = '/api/v1/public/kline';

        $url = $baseURL . $request;

        $client = new Client();

        $get_timezone = date_default_timezone_get();

        $tz_object = new DateTimeZone($get_timezone);

        $end_datetime = new DateTime();
        $end_datetime->setTimezone($tz_object);

        $start_datetime = new DateTime();
        $start_datetime->setTimezone($tz_object);
        $start_datetime = $start_datetime->modify('-2 years');

        try {

            $res = $client->get($url, [

                'headers' => [
                    'Content-type' => 'application/json',
                ],
                'query' => [
                    'market' => $market,
                    'start' => strtotime( $start_datetime->format('Y-m-d H:i:sP')),
                    'end' => strtotime( $end_datetime->format('Y-m-d H:i:sP')),
                    'interval' => 3600
                ]
            ]);

            $result = json_decode($res->getBody()->getContents());

            if ( $result->code === 200 ) {
                return view('chart.chart-test', compact('result'));
            }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

}

