<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Plural extends Model {
    protected $guarded = ['id', 'created_at', 'updated_at', ];
    public $all = [];
    public $current_ids = [];


    public function words() {
        return $this->belongsToMany('App\Word');
    }

    public function variations() {
        return $this->belongsToMany('App\Variation');
    }



    // Retrieve plurals like {-ква}, {-чва}, {-чваква} etc...
    public function retrievePlurals($line) {
        $this->current_ids = [];

        preg_match('/[\{]+([^}]+)[\}]+/', $line, $matches);

        if (isset($matches[1])) {
            if (strpos($matches[1], ',')) {
                $plurals = explode(",", $matches[1]);
                
                foreach ($plurals as $plural) {
                    $this->fillInPlurals($plural);
                }
            } else {
                $this->fillInPlurals($matches[1]);
            }

            return true;
        } else {
            return false;
        }
    }


    // Fill in (populate) table plurals with plurals like {-ква}, {-чва}, {-чваква} etc...
    protected function fillInPlurals($plural) {
        $item = trim($plural);

        if (in_array($item, $this->all)) {
            $this->current_ids[] = array_search($item, $this->all);
        } else {
            $entry = self::create([ 'plural' => $item ]);

            $this->all[$entry->id] = $entry->plural;
            $this->current_ids[] = $entry->id;
        }
    }


    public function getAllPlurals() {
        $plurals = self::all();

        if (isset($plurals[0])) {
            foreach ($plurals as $plural) {
                $this->all[$plural->id] = $plural->plural;
            }
        }
    }
}
