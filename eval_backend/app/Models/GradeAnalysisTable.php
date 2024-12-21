<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeAnalysisTable extends Model
{
    //
    protected $dateFormat = "Y-m-d H:i:s";
    protected $guarded = [];
    protected $table = "grade_analysis_tables";

    public function content()
    {
        return $this->belongsTo(Content::class);
    }

}
