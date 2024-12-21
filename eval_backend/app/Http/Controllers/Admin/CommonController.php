<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommonController extends Controller
{
    //
    use Tool;
    use Evaluation;

    public function getContent()
    {
       $result = $this->content();
       return $this->successWithData($result);
    }

    public function getPerson()
    {
        $group_id = request('group_id', 1);
        $result = DB::table("persons")->where('group_id', $group_id)->get();
        return $this->successWithData($result);
    }

    public function getMark()
    {
        $group_id = request('group_id', 1);
        $result = DB::table("grade_base_tables")
            ->where('group_id', $group_id)->select(['mark'])->orderBy("mark", "desc")
            ->groupBy(["mark"])
            ->get();
        return $this->successWithData($result);
    }

    public function test()
    {

    }

    public function getGroup()
    {
        $result = DB::table("groups")->where("status", 1)->get();
        return $this->successWithData($result);
    }
}
