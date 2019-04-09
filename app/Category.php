<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model {
    protected $guarded = ['id', 'created_at', 'updated_at', ];
    public $all = [];
    public $current_id = 0;


    public function words() {
        return $this->hasMany('App\Word');
    }


    // Retrieve categories like _I_, _II_, _III_ etc...
    public function retrieveCategory($line) {
        $this->current_id = 0;

        preg_match('/[_]+([^_]+)[_]+/', trim($line), $matches);

        if (isset($matches[1])) {
            $this->fillInCategories($matches[1]);
        } else {
            $this->fillInCategories('I');
        }

        return true;
    }


    // Fill in (populate) table categories with categories like _I_, _II_, _III_ etc...
    protected function fillInCategories($category) {
        $item = trim($category);

        if (in_array($item, $this->all)) {
            $this->current_id = array_search($item, $this->all);
        } else {
            $entry = self::firstOrCreate([ 'category' => $item ]);
            $this->all[$entry->id] = $entry->category;
            $this->current_id = $entry->id;
        }
    }


    public function getAllCategories() {
        $categories = self::all();

        if (isset($categories[0])) {
            foreach ($categories as $category) {
                $this->all[$category->id] = $category->category;
            }
        }
    }
}
