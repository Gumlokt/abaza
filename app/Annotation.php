<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Annotation extends Model {
    protected $guarded = ['id', 'created_at', 'updated_at', ];

    public function sample() {
        return $this->belongsTo('App\Sample');
    }


    // Store annotations to table 'annotations'
    public function storeAnnotation($annotation, $sample_id) {
        $entry = self::create([ 'sample_id' => $sample_id, 'annotation' => $annotation ]);
    }
}
