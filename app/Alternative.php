<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Alternative extends Model {
    protected $guarded = ['id', 'created_at', 'updated_at', ];

    public function sample() {
        return $this->belongsTo('App\Sample');
    }


    // Store alternatives to table 'alternatives'
    public function storeAlternatives($alternatives, $sample_id) {
        if (strpos($alternatives, ';')) {
            $parts = explode(";", $alternatives);

            foreach ($parts as $part) {
                $entry = self::create([ 'sample_id' => $sample_id, 'alternative' => trim($part) ]);
            }
        } else {
            $entry = self::create([ 'sample_id' => $sample_id, 'alternative' => trim($alternatives) ]);
        }
    }
}
