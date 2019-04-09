<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Type extends Model {
    protected $guarded = ['id', 'created_at', 'updated_at', ];
    public $all = [];

    public function references() {
        return $this->hasMany('App\References ');
    }


    // Fill in (populate) table types with type names like 'scope', 'source' and 'author'
    // Scope values are enclosed with square bracets like: [шIырпш.], [дин, ажвы] etc... (actual for words and samples)
    // Source values are enclosed with # character like: #Ажв.#, #Абаза т.# etc... (actual only for samples)
    // Author values are enclosed with $ character like: $Т. Т.$, $Л. Джь.$ etc... (actual only for samples)
    protected function fillInTypes() {
        foreach ($this->typesList as $type) {
            $entry = self::create([ 'type' => $type ]);

            $this->all[$entry->id] = $entry->type;
        }
    }


    public function getAllTypes() {
        $types = self::all();

        if (!isset($types[0])) {
            $this->fillInTypes();
        } else {
            foreach ($types as $type) {
                $this->all[$type->id] = $type->type;
            }
        }
    }


    protected $typesList = [
        'scope',
        'source',
        'author'
    ];
}
