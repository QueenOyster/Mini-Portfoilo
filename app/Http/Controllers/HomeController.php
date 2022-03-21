<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Memo;
use App\Models\MemoTag;
use App\Models\Tag;
use App\Models\Log;
use App\Models\MemoLog;

use DB;
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $tags = Tag::where('user_id', '=', \Auth::id())->whereNull('deleted_at')->orderBy('id', 'DESC')->get();

        $logs = Log::select('logs.*', 'memos.content AS content')
            ->whereDate('logs.created_at', Carbon::today())
            ->leftJoin('memo_logs', 'logs.id', '=', 'memo_logs.log_id',)
            ->leftJoin('memos', 'memo_logs.memo_id', '=', 'memos.id')
            ->where('memos.user_id', '=', \Auth::id())
            ->whereNull('memos.deleted_at')
            ->get(); // find($id);
        // dd($logs);

        $chartData = DB::table('logs')
            ->where('user_id', '=', \Auth::id())
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as tasks'))
            ->whereMonth('logs.created_at', Carbon::today())
            ->groupBy('date')
            ->get();

//        dd($chartData);
//        dd(json_encode($chartData));
//        $chartData = json_encode($chartData);
//        dd($chartData[0]->date); // string
//        dd(date_format($chartData[0]->date, 'Y-m-d'));
//        dd(Carbon::parse($chartData[0]->date));
//        dd(\Carbon\Carbon::parse($chartData[0]->date)->format('m-d'));

        return view('create', compact('tags', 'logs', 'chartData'));
    }

    public function chart()
    {
        return view('chart');
    }



    public function store(Request $request)
    {
        $posts = $request->all();
        $request->validate([ 'content' => 'required']);

        DB::transaction(function () use ($posts) {
            $memo_id = Memo::insertGetId(['content' => $posts['content'], 'user_id' => \Auth::id()]);

            $tag_exists = Tag::where('user_id', '=', \Auth::id())->where('name', '=', $posts['new_tag'])->exists();
            if (!empty($posts['new_tag']) || $posts['new_tag'] === "0" && !$tag_exists) {
                $tag_id = Tag::insertGetId(['user_id' => \Auth::id(), 'name' => $posts['new_tag']]);
                MemoTag::insert(['memo_id' => $memo_id, 'tag_id' => $tag_id]);
            }

            if (!empty($posts['tags'][0])) {
                foreach ($posts['tags'] as $tag) {
                    MemoTag::insert(['memo_id' => $memo_id, 'tag_id' => $tag]);
                }
            }
        });
        return redirect(route('home'));
    }

    public function edit($id)
    {
        $edit_memo = Memo::select('memos.*', 'tags.id AS tag_id')
            ->leftJoin('memo_tags', 'memo_tags.memo_id', '=', 'memos.id')
            ->leftJoin('tags', 'memo_tags.tag_id', '=', 'tags.id')
            ->where('memos.user_id', '=', \Auth::id())
            ->where('memos.id', '=', $id)
            ->whereNull('memos.deleted_at')
            ->get(); // find($id);
//        dd($edit_memo);

        $include_tags = [];
        foreach($edit_memo as $memo) {
            array_push($include_tags, $memo['tag_id']);
        }
        $tags = Tag::where('user_id', '=', \Auth::id())->whereNull('deleted_at')->orderBy('id', 'DESC')->get();

        $logs = Log::select('logs.*', 'memos.content AS content')
            ->whereDate('logs.created_at', Carbon::today())
            ->leftJoin('memo_logs', 'logs.id', '=', 'memo_logs.log_id',)
            ->leftJoin('memos', 'memo_logs.memo_id', '=', 'memos.id')
            ->where('memos.user_id', '=', \Auth::id())
            ->whereNull('memos.deleted_at')
            ->get();

        $chartData = DB::table('logs')
            ->where('user_id', '=', \Auth::id())
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as tasks'))
            ->whereMonth('logs.created_at', Carbon::today())
            ->groupBy('date')
            ->get();


        return view('edit', compact('edit_memo', 'include_tags', 'tags', 'memo', 'logs', 'chartData'));

    }

    public function submit($id) {

        $submit_memo = Memo::select('memos.*')
            ->where('memos.user_id', '=', \Auth::id())
            ->where('memos.id', '=', $id)
            ->whereNull('memos.deleted_at')
            ->get(); // find($id);

        $logs = Log::select('logs.*', 'memos.content AS content')
            ->whereDate('logs.created_at', Carbon::today())
            ->leftJoin('memo_logs', 'logs.id', '=', 'memo_logs.log_id',)
            ->leftJoin('memos', 'memo_logs.memo_id', '=', 'memos.id')
            ->where('memos.user_id', '=', \Auth::id())
            ->whereNull('memos.deleted_at')
            ->get(); // find($id);
//        dd($logs);

        $chartData = DB::table('logs')
            ->where('user_id', '=', \Auth::id())
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as tasks'))
            ->whereMonth('logs.created_at', Carbon::today())
            ->groupBy('date')
            ->get();

        return view('submit', compact('submit_memo', 'logs', 'chartData'));

    }


    public function update(Request $request)
    {
        $posts = $request->all();
        $request->validate([ 'content' => 'required']);

//        dd($posts);

        DB::transaction(function () use($posts){
            Memo::where('id', $posts['memo_id'])->update(['content' => $posts['content']]);
            MemoTag::where('memo_id', '=', $posts['memo_id'])->delete();

            if ( ( isset($posts['tags']) )) {
              foreach($posts['tags'] as $tag) {
                  MemoTag::insert(['memo_id' => $posts['memo_id'], 'tag_id' => $tag]);
              }
            }
        });

        $tag_exists = Tag::where('user_id', '=', \Auth::id())->where('name', '=', $posts['new_tag'])->exists();
        if ( ( !empty($posts['new_tag']) || $posts['new_tag'] === "0" ) && !$tag_exists) {
            $tag_id = Tag::insertGetId(['user_id' => \Auth::id(), 'name' => $posts['new_tag']]);
            MemoTag::insert(['memo_id' => $posts['memo_id'], 'tag_id' => $tag_id]);
        }
        return redirect(route('home'));
    }

    public function enroll(Request $request)
    {
        $posts = $request->all();

        DB::transaction(function () use($posts){
            $log_id = Log::insertGetId(['detail' => $posts['detail'], 'user_id' => \Auth::id()]);
            MemoLog::insert(['memo_id' => $posts['memo_id'], 'log_id' => $log_id]);
        });

        return redirect(route('home'));
    }




    public function destroy(Request $request)
    {
        $posts = $request->all();
        Memo::where('id', $posts['memo_id'])->update(['deleted_at' => date("Y-m-d H:i:s", time())]);
        return redirect(route('home'));
    }

    public function tag_destroy($id)
    {
//        $posts = $request->all();
//        $query_tag = \Request::query('tag');

//        dd($id);
        Tag::where('id', $id)->update(['deleted_at' => date("Y-m-d H:i:s", time())]);
        return redirect(route('home'));
    }



}
