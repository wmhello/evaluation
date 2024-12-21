<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;

trait Evaluation
{

    public function content()
    {
        $result = DB::table("v_content_group")->select(["id", "topic_id", "content_id", "content_title", "grade_name", "grade_value"])->get();
        return $result;
    }

        // 批量更新数据
    public function updateBatch($tableName = "", $multipleData = array(), $referenceColumn = ''){

        if( $tableName && !empty($multipleData) ) {

            // column or fields to update
            $updateColumn = array_keys($multipleData[0]);
            $index = $this->findStringIndex($updateColumn, $referenceColumn);
            if ($index >= 0) {
                 unset($updateColumn[$index]);
            }

            $whereIn = "";

            $q = "UPDATE ".$tableName." SET ";
            foreach ( $updateColumn as $uColumn ) {
                $q .=  $uColumn." = CASE ";

                foreach( $multipleData as $data ) {
                    $q .= "WHEN ".$referenceColumn." = '".$data[$referenceColumn]."' THEN '".$data[$uColumn]."' ";
                }
                $q .= "ELSE ".$uColumn." END, ";
            }
            foreach( $multipleData as $data ) {
                $whereIn .= "'".$data[$referenceColumn]."', ";
            }
            $q = rtrim($q, ", ")." WHERE ".$referenceColumn." IN (".  rtrim($whereIn, ', ').")";
            return DB::update(DB::raw($q));
        } else {
            return false;
        }
    }

    public function findStringIndex($arr, $str)
    {
          foreach($arr as $key => $val) {
             if($val == $str) {
                  return $key;
                }
             }
          return -1;
    }

}
