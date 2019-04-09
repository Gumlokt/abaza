<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Variation extends Model {
    protected $guarded = ['id', 'created_at', 'updated_at', ];
    public $entries = [];

    public function words() {
        return $this->belongsTo('App\Word');
    }

    public function plurals() {
        return $this->belongsToMany('App\Plural');
    }

    public function origins() {
        return $this->belongsToMany('App\Origin');
    }


    // Fill in (populate) table "comments"
    // '%' character in first line means, that line contains comment
    public function fillInVariations($line, $word_id) {
        $this->entries = [];
        preg_match('/Z([^Z]+)Z/', trim($line), $matches);

        if (strpos($matches[1], ' ')) {
            $parts = explode(' ', trim($matches[1]));

            foreach ($parts as $part) {
                $cleared = $this->clearWord(trim($part));
                $this->entries[$part] = self::create([ 'word_id' => $word_id, 'variation' => $this->clearAcute($cleared), 'stress' => $cleared ]);
            }
        } else {
            $cleared = $this->clearWord(trim($matches[1]));
            $this->entries[$matches[1]] = self::create([ 'word_id' => $word_id, 'variation' => $this->clearAcute($cleared), 'stress' => $cleared ]);
        }
    }


    // remove unnecessary characters: [FWtgR,]
    public function clearWord($word) {
        $word = str_replace(['F', 'W', 't', 'g', 'R', ','], '', $word);

        return $word;
    }

    // remove acute (accent): []́
    public function clearAcute($word) {
        $word = str_replace(['́', ], '', $word);

        return $word;
    }
}
