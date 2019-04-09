<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


use App\Ending;
use App\Plural;
use App\Origin;

use App\Variation;
use App\Comment;

use App\Category;

use App\Sample;
use App\Refrence;



class Word extends Model {
    protected $guarded = ['id', 'created_at', 'updated_at', ];
    public $current_word = '';
    public $autonomous = true;
    public $current_scopes = [];

    public function endings() {
        return $this->belongsToMany('App\Ending');
    }

    public function plurals() {
        return $this->belongsToMany('App\Plural');
    }

    public function origins() {
        return $this->belongsToMany('App\Origin');
    }


    public function variations() {
        return $this->hasMany('App\Variation');
    }

    public function comments() {
        return $this->hasMany('App\Comment');
    }

    public function category() {
        return $this->belongsTo('App\Category');
    }


    public function samples() {
        return $this->hasMany('App\Sample');
    }

    public function references() {
        return $this->belongsToMany('App\Reference');
    }


    // *** Symbols that acts as delimiters and their precedence
    // ":="  - (colon and equal sign) delimits nonautonomous words from their examples
    // " + " - (space, "plus" character and space) delimits composite words from other parts of dictionary cell
    // " "   - (regular space) delimits words from other parts of dictionary cell
    // "\n"  - (new line character) delimits words from other dictionary cell
    // ***********************************************************************************

    // store non-autonomous word
    public function storeNonAutonomousWord($line, $category_id) {
        $this->current_scopes = [];
        $this->autonomous = false;
        $this->current_word = $line;

        // Firstly extract scopes if exist and than extract line till to the square bracket character '['
        // if (strpos($this->current_word, '[')) {
        //     $this->extractScopes($this->current_word);
        //     $this->current_word = strstr($this->current_word, "[", true);
        // }
        if (strpos($this->current_word, "[")) {
            $this->extractScopes($this->current_word);
            $this->current_word = preg_replace('/[\[]+([^\]]+)[\]]+/', '', $this->current_word);
        }

        // remove '+' (plus) character
        if (strpos($this->current_word, '+')) {
            $this->current_word = strstr($this->current_word, "+", true);
        }

        // remove '_' (underscore) character
        if (strpos($this->current_word, '_')) {
            $this->current_word = strstr($this->current_word, "_", true);
        }


        // store word in DB table
        $this->storeWord($category_id);


        return true;
    }


    // store non-autonomous word
    public function storeMultiWord($line, $category_id) {
        $this->current_scopes = [];
        $this->autonomous = true;
        $this->current_word = $line;

        // store word in DB table
        $this->storeWord($category_id);

        return true;
    }


    // store single word that can also contains other stuff like scopes, samples etc...
    public function storeStuffedWord($line, $category_id) {
        $this->current_scopes = [];
        $this->autonomous = true;
        $this->current_word = strstr($line, " ", true);
        
        if (strpos($line, '[')) {
            $this->extractScopes($line);
        }

        // store word in DB table
        $this->storeWord($category_id);

        return true;
    }


    // store single word that does not contain any other stuff
    public function storeSoleWord($line, $category_id) {
        $this->current_scopes = [];
        $this->autonomous = true;
        $this->current_word = $line;

        // store word in DB table
        $this->storeWord($category_id);

        return true;
    }



    // Extract scopes of dictionary cell from string fragment and store them to $this->current_scopes
    public function extractScopes($fragment) {
        preg_match('/[\[]+([^\]]+)[\]]+/', $fragment, $matches);

        if (strpos($matches[1], ', ')) {
            $parts = explode(", ", $matches[1]);

            foreach ($parts as $part) {
                $this->current_scopes[] = $part;
            }
        } else {
            $this->current_scopes[] = $matches[1];
        }
    }


    // Store word and other data in database to table "words"
    protected function storeWord($category_id) {
        $cleared = $this->clearWord();
        // $cleared = $this->clearWord(trim($this->current_word));

        $this->entry = self::create([
            'category_id' => $category_id,
            'word' => $this->clearAcute($cleared),
            'stress' => $cleared,
            'independent' => $this->autonomous,
        ]);
    }


    // Remove unnecessary characters: [FWtgR,]
    public function clearWord() {
        $this->current_word = str_replace(['F', 'W', 't', 'g', 'R'], '', $this->current_word);
        $this->current_word = trim($this->current_word);
        $this->current_word = rtrim($this->current_word, ",");

        return $this->current_word;
        return str_replace(['F', 'W', 't', 'g', 'R'], '', $this->current_word);
        // return str_replace(['F', 'W', 't', 'g', 'R', ','], '', $word);
    }

    // Remove acute (accent): []́
    public function clearAcute($word) {
        return str_replace(['́', ], '', $word);
    }
}
