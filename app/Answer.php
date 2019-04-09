<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model {
    protected $guarded = ['id', 'created_at', 'updated_at', ];

    public function sample() {
        return $this->belongsTo('App\Sample');
    }


    // Store answers to table 'answers'
    public function storeAnswer($answer, $sample_id) {
        $entry = self::create([ 'sample_id' => $sample_id, 'answer' => $answer ]);
    }
}
