<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ending extends Model {
    protected $guarded = ['id', 'created_at', 'updated_at', ];
    public $all = [];
    public $current_ids = [];


    public function words() {
        return $this->belongsToMany('App\Word');
    }


    // Get endings like (-даъа), (-дъа), (-ра) etc...
    public function retrieveEndings($line) {
        $this->current_ids = [];

        // sequence '(-' is a sign by which we can make a conclusion, that there are one or more endings mentioned above, which we want to retrieve
        preg_match('/[\(]+(-[^)]+)[\)]+/', trim($line), $matches);

        if (isset($matches[1])) {
            if (strpos($matches[1], ',')) {
                $endings = explode(",", $matches[1]);
                
                foreach ($endings as $ending) {
                    $this->fillInEndings($ending);
                }
            } else {
                $this->fillInEndings($matches[1]);
            }

            return true;
        } else {
            return false;
        }
    }


    // Fill in (populate) table endings with endings like (-даъа), (-дъа), (-ра) etc...
    protected function fillInEndings($ending) {
        $item = trim($ending);

        if (in_array($item, $this->all)) {
            $this->current_ids[] = array_search($item, $this->all);
        } else {
            $entry = self::create([ 'ending' => $item ]);

            $this->all[$entry->id] = $entry->ending;
            $this->current_ids[] = $entry->id;
        }
    }


    public function getAllEndings() {
        $endings = self::all();

        if (isset($endings[0])) {
            foreach ($endings as $ending) {
                $this->all[$ending->id] = $ending->ending;
            }
        }
    }
}
