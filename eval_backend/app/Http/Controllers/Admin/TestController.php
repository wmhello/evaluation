<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    //

    public function generateCountBaseInfo()
    {
        $ids = DB::table('groups')->pluck('id')->toArray();
        $grades = DB::table("grades")->get();
        $content = DB::table("contents")->get();
        $arrGrade = [];
        foreach ($grades as $grade) {
            $tmp = (array)$grade;
            unset($tmp['id']);
            unset($tmp['grade_desc']);
            unset($tmp['grade_short']);
            unset($tmp['created_at']);
            unset($tmp['updated_at']);
            $arrGrade [] = $tmp;
        }
        foreach ($ids as $group_id) {
            foreach ($arrGrade as &$grade) {
                $grade['group_id'] = $group_id;
                $item = DB::table("grade_analysis_tables")->where($grade)->first();
                if (is_null($item)){
                    $grade["created_at"] = now();
                    DB::table("grade_analysis_tables")->insert($grade);
                }
            }
            $tmp = [];
            foreach ($content as $con) {
                $tmp = [
                  "topic_id" => 1,
                  "content_id" => $con->id,
                  "grade_name" => $con->content_title.'(平均分)',
                  "group_id" => $group_id,
                  "grade_value" => $con->content_value
                ];
                $item = DB::table("grade_analysis_tables")->where($tmp)->first();
                if (is_null($item)){
                    $tmp["created_at"] = now();
                    DB::table("grade_analysis_tables")->insert($tmp);
                }
            }
        }
    }

    public function computerTeacherScore($i, $group_id)
    {
        $count_field = "name". $i;
        $sql = <<<SQL
        select  topic_id,group_id, content_id,##person## as grade_name, count(##person##) as ##person##  from grade_base_tables 
        where topic_id = 1 and group_id = ##group_id##
        group by topic_id, group_id, content_id, ##person##
SQL;
       $sql = str_replace("##person##", $count_field, $sql);
       $sql = str_replace("##group_id##", $group_id, $sql);
       $result = DB::select($sql);
       foreach ($result as $res) {
           $tmp =(array)$res;
           $value = $tmp[$count_field];
           unset($tmp[$count_field]);
           $item = DB::table("grade_analysis_tables")->where($tmp)->first();
           // 更新数字
           if ($item) {
               DB::table("grade_analysis_tables")->where($tmp)->update([
                  "updated_at" => now(),
                  "$count_field" => $value
               ]);
           }
       }
       // 平均分分值
       $content = DB::table("contents")->where("topic_id", 1)->select(["id","content_title"])->get();
       foreach($content as $con) {
            $content_id = $con->id;
            $grade_name = $con->content_title.'(平均分)';
            $sql = <<<SQL
           select topic_id,group_id, content_id, '##grade_name##' as grade_name, sum(value)/sum(##person##) as ##person## from (
           select topic_id,group_id, content_id, grade_name, grade_value, ##person##, grade_value * ##person## as value from grade_analysis_tables 
           where topic_id = 1 and group_id = ##group_id## and content_id = ##content_id## and grade_name in ('优', '良', '中', '差') ) as tmp  
           group by topic_id,group_id, content_id
SQL;
          $sql = str_replace("##person##", $count_field, $sql);
          $sql = str_replace("##group_id##", $group_id, $sql);
          $sql = str_replace("##grade_name##", $grade_name, $sql);
          $sql = str_replace("##content_id##", $content_id, $sql);

          $result = DB::select($sql);
           foreach ($result as $res) {
               $tmp =(array)$res;
               $value = $tmp[$count_field];
               unset($tmp[$count_field]);
               $item = DB::table("grade_analysis_tables")->where($tmp)->first();
               // 更新数字
               if ($item) {
                   DB::table("grade_analysis_tables")->where($tmp)->update([
                      "updated_at" => now(),
                      "$count_field" => $value
                   ]);
               }
           }
       }




    }

    public function countData()
    {
        $ids = DB::table('groups')->pluck('id')->toArray();
        foreach($ids as $group_id) {
            $count = DB::table("persons")->where("group_id", $group_id)->count();
            // 依次统计出每个被评分者被打的次数
            for($i =1; $i <= $count; $i ++ ) {
                $this->computerTeacherScore($i, $group_id);
            }
            // 依次得到每一阶段中某个老师的平均数
        }
    }

    public function test()
    {

//        $this->generateCountBaseInfo();
        $this->countData();
        dd("生成统计表基本信息");
    }
//        $group_id = request("group_id", 1);
//        $persons= DB::table("persons")->where("group_id",$group_id)->get();
//        $persons->each(function($person, $key){
//            // 按成绩分组，获取每个人在每个内容级别的人数  比如第一个内容中的优级别有6人
//            $sql = <<< SQL
//select topic_id, content_id, group_id, ##field## as grade_name, count(*) as ##field##
//from grade_base_tables where group_id =1 and topic_id
//group by topic_id, content_id, group_id, ##field##
//SQL;
//            $sql = str_replace("##field##", "name".$person->order, $sql);
//            $result = DB::select($sql);
//            foreach ($result as &$item){
//                $item = (array)$item;
//            }
//            foreach ($result as $item) {
//                $sql = <<< SQL
//update grade_analysis_tables
//set ##filed## = ##filed_value##
//where topic_id = ##topic_id##
//and content_id = ##content_id##
//and group_id = ##group_id##
//and grade_name = '##grade_name##'
//SQL;
//                $sql = str_replace("##filed##", "name".$person->order, $sql);
//                $sql = str_replace("##filed_value##", $item["name".$person->order], $sql);
//                $sql = str_replace("##topic_id##", $item['topic_id'], $sql);
//                $sql = str_replace("##content_id##", $item['content_id'], $sql);
//                $sql = str_replace("##group_id##", $item['group_id'], $sql);
//                $sql = str_replace("##grade_name##", $item['grade_name'], $sql);
//                DB::select($sql);
//            }
//            dd("name1结束");
//        });
//
//
//    }
}
