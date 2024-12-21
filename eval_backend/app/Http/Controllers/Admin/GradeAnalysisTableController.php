<?php


namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class GradeAnalysisTableController extends Controller
{
    use Tool;
    protected  $model = 'App\Models\GradeAnalysisTable';  // 当前模型
    protected  $fillable = [];  // 当前模型可以修改和新增的字段
    protected  $resource = 'App\Http\Resources\GradeAnalysisTable'; // 显示个体资源
    protected  $resourceCollection = 'App\Http\Resources\GradeAnalysisTableCollection'; // 显示资源集合
    protected  $map = [];   // 导入导出时候  数据表字段与说明的映射表


    public function index(Request $request)
    {
        // 显示订单列表
        $pageSize = $request->input('pageSize', 10);
        return  $this->getListData($pageSize);
    }



    protected  function  getListData($pageSize){
        // 当前列表数据  对应于原来的index
        $action = request("action", "count");
        if ($action === "count") {
           $group_id = request("group_id", 1);
           $data = $this->model::where("group_id", $group_id)
               ->where("topic_id", 1)
               ->whereIn("grade_name", ["优", "良", "中", "差"])
               ->paginate($pageSize);
        } else {
           $group_id = request("group_id", 1);
           $data = $this->model::where("group_id", $group_id)
               ->where("topic_id", 1)
               ->whereNotIn("grade_name", ["优", "良", "中", "差"])
               ->paginate($pageSize);
        }

        return new $this->resourceCollection($data);
    }


    public function show($id){
        $data = $this->model::find($id);
        return new $this->resource($data);
    }

    public function getContent()
    {
        $mark = request('mark', null);
        $group_id = request('group_id', 1);
        $result = DB::table("grade_base_tables")
            ->where("mark", $mark)
            ->where("group_id", $group_id)
            ->get();
        return $this->successWithData($result);
    }

    public function store(Request $request)
    {
        $this->generateCountBaseInfo();
        $this->countData();
        return $this->successWithInfo("测试信息统计完成");
    }

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
        }
    }


    protected function storeHandle($data)
    {
          return $data;   // TODO: Change the autogenerated stub
    }

    protected function getErrorInfo($validator)
    {
            $errors = $validator->errors();
            $errorTips = '';
            foreach($errors->all() as $message){
                $errorTips = $errorTips.$message.',';
            }
            $errorTips = substr($errorTips, 0, strlen($errorTips)-1);
            return $errorTips;
    }


    public function update(Request $request, $id)
    {
        $data = $request->only($this->fillable);
        if (method_exists($this, 'message')){
            $validator = Validator::make($data, $this->updateRule($id), $this->message());
        } else {
            $validator = Validator::make($data, $this->updateRule($id));
        }
        if ($validator->fails()){
            // 有错误，处理错误信息并且返回
            $errorTips = $this->getErrorInfo($validator);
            return $this->errorWithInfo($errorTips, 422);
        }
        // 进一步处理数据
        $data = $this->updateHandle($data);
        // 更新到数据表
        if ($this->model::where('id', $id)->update($data)){
            return $this->successWithInfo('数据更新成功');
        } else {
            return $this->errorWithInfo('数据更新失败');
        }
    }

    protected  function  updateHandle($data){
        return $data;
    }

    public function destroy($id)
    {
        if ($this->destroyHandle($id)){
            return  $this->successWithInfo('数据删除成功', 204);
        } else {
            return $this->errorWithInfo('数据删除失败，请查看指定的数据是否存在');
        }
    }

    protected function destroyHandle($id) {
        DB::transaction(function () use($id) {
            // 删除逻辑  注意多表关联的情况
            $this->model::where('id', $id)->delete();
        });
        return true;
    }
    public function deleteAll()
    {
        // 前端利用json格式传递数据
        $ids = json_decode(request()->input('ids'),true);
        foreach ($ids as $id) {
            $this->destoryHandle($id);
        }
        return $this->successWithInfo('批量删除数据成功', 204);
    }



    public function export()
    {
        $data = $this->model::all();
        $data = $data->toArray();
        $arr = $this->exportHandle($data);
        $data = collect($arr);
        $fileName = time().'.xlsx';
        $file = 'xls\\'.$fileName;
        (new FastExcel($data))->export($file);
        return $this->successWithInfo($file);
    }

    protected function exportHandle($arrData){
        // 默认会根据$map进行处理，
        $arr = [];
        foreach ($arrData as $item) {
            $tempArr = $this->handleItem($item, 'export');
            // 根据需要$tempArr可以进一步处理，特殊的内容，默认$tempArr是根据$this->map来处理
            $arr[] = $tempArr;
        }
        return $arr;
    }


    /**
     * 根据map表，处理数据
     * @param $data
     */
    protected function handleItem($data, $type = 'export'){
        $arr = [];
        if ($type === 'export'){
            foreach ($this->map as $key => $item){
                if (!isset($data[$item])){
                    continue;
                }
                $arr[$key] = $data[$item];
            }
        }
        if ($type === 'import'){
            foreach ($this->map as $key => $item){
                if (!isset($data[$key])){
                    continue;
                }
                $arr[$item] = $data[$key];
            }
        }
        return $arr;
    }


    public function import()
    {
//        1.接收文件，打开数据
//        2. 处理打开的数据，循环转换
//        3. 导入到数据库
        $data = (new FastExcel())->import(request()->file('file'));
        $arrData = $data->toArray();
        $arr = $this->importHandle($arrData);
        $this->model::insert($arr['successData']);
        $tips = '当前操作导入数据成功'.$arr['successCount'].'条';
        if ($arr['isError']) {
            // 有失败的数据，无法插入，要显示出来，让前端能下载
            $file = time().'.xlsx';
            $fileName = public_path('xls').'\\'.$file;
            $file = 'xls\\'.$file;
            $data = collect($arr['errorData']);
            (new FastExcel($data))->export($fileName);
            $tips .= ',失败'.$arr['errorCount'].'条';
            return response()->json([
                'info' => $tips,
                'fileName' => $file,
                'status' => 'error',
                'status_code' => 422
            ], 422);
        } else {
            return $this->successWithInfo($tips, 201);
        }
    }

    protected function importHandle($arrData){
//        1. 要对每一条记录进行校验

//        2. 根据校验的结果，计算出可以导入的条数，以及错误的内容

        $error = []; // 错误的具体信息
        $isError = false;  // 是否存在信息错误
        $successCount = 0; // 统计数据导入成功的条数
        $errorCount = 0;  // 出错的条数
        $arr = [];  // 正确的内容存储之后，返回数据
        foreach ($arrData as $key => $item) {
            $data = $this->handleItem($item, 'import');
            $data['created_at'] = Carbon::now();
            // 可以根据需要，进一步处理数据
            $this->validatorData($item,$data,$error, $isError ,$successCount, $errorCount,$arr);
        }
        return [
            'successData' => $arr,
            'errorData' => $error,
            'isError' => $isError,
            'errorCount' => $errorCount,
            'successCount' => $successCount,
        ];
    }


    protected function validatorData($item, $data, &$error, &$isError ,&$successCount, &$errorCount,&$arr){
        if (method_exists($this, 'message')){
            $validator = Validator::make($data,$this->storeRule(),$this->message());
        } else {
            $validator = Validator::make($data,$this->storeRule());
        }
        if ($validator->fails()){
            // 获取相关的错误信息，并且把错误信息单独存放
            $errors = $validator->errors($validator);
            $tips = '';
            foreach ($errors->all() as $message){
                $tips .= $message.',';
            }
            $tips = substr($tips,0,strlen($tips)-1);
            // 状态信息
            $item['错误原因'] = $tips;
            $error[] = $item;
            $isError = true;
            $errorCount ++;
        } else {
            // 没有出错的，我们先存在正确的数组
            $arr[] = $data;
            $successCount ++;
        }
    }


    protected function storeRule(){
      return [];
    }

    protected  function UpdateRule($id){
        return [];
    }


   protected function  message(){
       return [];
   }


}
