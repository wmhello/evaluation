<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    //
    protected $dateFormat = "Y-m-d H:i:s";
    protected $guarded = [];
    protected $table = "contents";

    public function grade_analysis_tables()
    {
        return $this->hasMany(GradeAnalysisTable::class);
    }


}
