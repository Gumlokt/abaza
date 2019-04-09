<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model {
    protected $guarded = [ 'id', 'created_at', 'updated_at', ];


    public function word() {
        return $this->belongsTo('App\Word');
    }


    // Fill in (populate) table "comments"
    // '%' character in first line means, that line contains comment
    public function fillInComments($line, $word_id) {
        preg_match('/\%([^\%]+)\%/', $line, $matches);

        $letter = '';
        $comment = $matches[1];
        
        if (strpos($matches[1], '=')) {
            $parts = explode(" = ", $matches[1]);

            $letter = $parts[0];
            $comment = $parts[1];
        }

        self::create(['word_id' => $word_id, 'letter' => $letter, 'comment' => rtrim($comment, ';') ]);
    }
}
