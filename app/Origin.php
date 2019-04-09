<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Origin extends Model {
    protected $guarded = ['id', 'created_at', 'updated_at', ];
    public $all = [];
    public $current_ids = [];


    public function words() {
        return $this->belongsToMany('App\Word');
    }

    public function variations() {
        return $this->belongsToMany('App\Variation');
    }


    // If current word is borrowed, than it should contains one of these symbols: FWtgR
    public function retrieveOrigins($line) {
        $this->current_ids = [];

        // F (capital) means, that language is undefined from which current word is borrowed
        // W (capital) means, that current word is borrowed from Adyghe
        // t (lowercase) means, that current word is borrowed from Turkish
        // g (lowercase) means, that current word is borrowed from Arabic
        // R (capital) means, that current word is borrowed from Russian
        preg_match('/([FWtgR]+)/', trim($line), $matches);

        if (isset($matches[1])) {
            $characters = str_split($matches[1]); // string to array of characters
            $count = count($characters);

            for ($i = 0; $i < $count; $i++) {
                $this->current_ids[] = array_search($this->originsMap[$characters[$i]], $this->all);
            }

            return true;
        } else {
            return false;
        }
    }


    // Fill in (populate) table origins with values like 'undefined', 'adyghe', 'arabic', 'russian', 'turkish'
    // These values are are predefined and could be used as "enum" data type in MySQL
    protected function fillInOrigins() {
        foreach ($this->originsList as $origin) {
            $entry = self::create($origin);

            $this->all[$entry->id] = $entry->origin;
        }
    }


    public function getAllOrigins() {
        $origins = self::all();

        if (!isset($origins[0])) {
            $this->fillInOrigins();
        } else {
            foreach ($origins as $origin) {
                $this->all[$origin->id] = $origin->origin;
            }
        }
    }



    protected $originsList = [
        [
            'origin' => 'Йгьрытаразым',
            'description' => 'Абызшва йгьрытаразым'
        ],
        [
            'origin' => 'Адыгьа',
            'description' => 'Адыгьа бызшва'
        ],
        [
            'origin' => 'Трыкв',
            'description' => 'Трыкв бызшва'
        ],
        [
            'origin' => 'ГIарып',
            'description' => 'ГIарып бызшва'
        ],
        [
            'origin' => 'Урышв',
            'description' => 'Урышв бызшва'
        ],
    ];

    protected $originsMap = [
        'F' => 'Йгьрытаразым',
        'W' => 'Адыгьа',
        't' => 'Трыкв',
        'g' => 'ГIарып',
        'R' => 'Урышв',
    ];
}
