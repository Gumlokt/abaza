<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sample extends Model {
    protected $guarded = ['id', 'created_at', 'updated_at', ];
    public $primary_entries = [];
    public $secondary_entries = [];

    public $i = 0;
    public $bracketed = true;
    public $sentence = true; // samples may looks like sentences or phrases
    public $word_id = true;

    // ******************************************************************************************
    // * $primary_entries[] = [
    // *    'entry'        => Eloquent_Object,
    // *    'scopes'       => [],
    // *    'sources'      => '',
    // *    'author'       => '',
    // *    'answer'       => '',
    // *    'annotation'   => '',
    // * ];

    // * $secondary_entries[] = [
    // *    'entry'        => Eloquent_Object,
    // *    'scopes'       => [],
    // *    'alternatives' => '/..../',
    // * ];
    // ******************************************************************************************

    public function word() {
        return $this->belongsTo('App\Word');
    }

    public function references() {
        return $this->belongsToMany('App\Reference');
    }

    public function alternatives() {
        return $this->hasMany('App\Alternative');
    }

    public function annotations() {
        return $this->hasMany('App\Annotation');
    }

    public function answers() {
        return $this->hasMany('App\Answer');
    }



    // Store primary samples
    public function storePrimarySamples($line, $word_id) {
        // ******************************************************************************************
        // * $primary_entries[] = [
        // *    'entry'        => Eloquent_Object,
        // *    'scopes'       => [],
        // *    'sources'      => '',
        // *    'author'       => '',
        // *    'answer'       => '',
        // *    'annotation'   => '',
        // * ];
        // ******************************************************************************************

        $this->primary_entries = [];

        $this->i = 0;
        $this->bracketed = true;
        $this->sentence = true;
        $this->word_id = $word_id;



        if (strrpos($line, "/")) {
            $line_parts = explode("/", $line);

            // non-independent words may contain samples, that can be eighter outside of brackets or in brackets 
            if (!empty($line_parts[0])) {
                $this->bracketed = false;
                $this->sentence = false;

                if (strpos($line_parts[0], ";")) {
                    $parts = explode(";", $line_parts[0]);

                    foreach ($parts as $part) {
                        $this->handleCurrentSample($part);
                    }
                } else {
                    $this->handleCurrentSample($line_parts[0]);
                }
            }


            $this->bracketed = true;

            if (strpos($line_parts[1], "<>")) {

                $samples_parts = explode(" <> ", $line_parts[1]); // $samples_parts[0] contains samples delimited by ';' character while $samples_parts[1] contains samples delimited by '@' character

                // Do not forget to exclude samples that contain '%' character, because they are actually should be stored in table 'comments'
                if (!strrpos($samples_parts[0], "%")) {
                    $this->sentence = false;

                    $phrases = explode(";", $samples_parts[0]);
                    foreach ($phrases as $phrase) {
                        if (!empty($phrase)) {
                            $this->handleCurrentSample($phrase);
                        }
                    }
                }

                $this->sentence = true;

                $sentences = explode("@", $samples_parts[1]);
                foreach ($sentences as $sentence) {
                    if (!empty($sentence)) {
                        $this->handleCurrentSample($sentence);
                    }
                }
            } else {
                // Do not forget to exclude samples that contain '%' character, because they are actually should be stored in table 'comments'
                if (!strrpos($line_parts[1], "%")) {
                    // simply if(strpos($line_parts[1], "@")) {...} does not work, because '@' character appears at 0-th index and evaluates to false
                    // that's why we should use strict equation
                    if (!(false === strpos($line_parts[1], "@"))) {
                        $this->sentence = true;

                        $sentences = explode("@", $line_parts[1]);
                        foreach ($sentences as $sentence) {
                            if (!empty($sentence)) {
                                $this->handleCurrentSample($sentence);
                            }
                        }
                    } else {
                        $this->sentence = false;

                        if (strpos($line_parts[1], ";")) {
                            $phrases = explode(";", $line_parts[1]);

                            foreach ($phrases as $phrase) {
                                if (!empty($phrase)) {
                                    $this->handleCurrentSample($phrase);
                                }
                            }
                        } else {
                            $this->handleCurrentSample($line_parts[1]);
                        }
                    }
                }
            }
        } else {
            $this->bracketed = false;
            $this->sentence = false;

            if (strpos($line, ";")) {
                $phrases = explode(";", $line);

                foreach ($phrases as $phrase) {
                    if (!empty($phrase)) {
                        $this->handleCurrentSample($phrase);
                    }
                }
            } else {
                $this->handleCurrentSample($line);
            }
        }


        return true;
    }

    // Store secondary samples
    public function storeSecondarySamples($lines, $word_id) {
        // ******************************************************************************************
        // * $secondary_entries[] = [
        // *    'entry'        => Eloquent_Object,
        // *    'scopes'       => [],
        // *    'alternatives' => '/...; .../',
        // * ];
        // ******************************************************************************************
        $this->secondary_entries = [];
        $this->i = 0;

        foreach ($lines as $line) {
            if (strpos($line, '/')) {
                $parts = explode("/", $line);

                $current_sample = $parts[0];
                $this->secondary_entries[$this->i]['alternatives'] = $parts[1];
            } else {
                $current_sample = $line;
            }


            if (strpos($current_sample, ']')) {
                preg_match('/[\[]+([^]]+)[\]]+/', $line, $matches);
                $this->secondary_entries[$this->i]['scopes'][] = $matches[1];

                $current_sample = preg_replace('/[\[]+([^\]]+)[\]]+/', '', $current_sample);
            }


            $current_sample = trim($current_sample);


            $this->secondary_entries[$this->i]['entry'] = self::create([
                'word_id'   => $word_id,
                'sample'    => $current_sample,
                'primary'   => false,
                'bracketed' => false,
                'sentenced'  => false,
            ]);

            $this->i++;
        }
    }



    public function handleCurrentSample($current_sample) {
        if (strrpos($current_sample, "%")) {
            $current_sample = preg_replace('/\%([^\%]+)\%/', '', $current_sample);
        }
        if (strpos($current_sample, "}")) {
            $current_sample = preg_replace('/[\{]+([^}]+)[\}]+/', '', $current_sample);
        }

        if (strpos($current_sample, "]")) {
            $this->retrieveScopes($current_sample);
            $current_sample = preg_replace('/[\[]+([^\]]+)[\]]+/', '', $current_sample);
        }
        if (strpos($current_sample, "#")) {
            $this->retrieveSources($current_sample);
            $current_sample = preg_replace('/[#]+([^#]+)[#]+/', '', $current_sample);
        }
        if (strpos($current_sample, "$")) {
            $this->retrieveAuthor($current_sample);
            $current_sample = preg_replace('/[$]+([^$]+)[$]+/', '', $current_sample);
        }
        if (strpos($current_sample, "&")) {
            $this->retrieveAnswer($current_sample);
            $current_sample = preg_replace('/[&]+([^&]+)[&]+/', '', $current_sample);
        }
        if (strpos($current_sample, "((")) {
            $this->retrieveAnnotation($current_sample);
            $current_sample = preg_replace('/[\(]{2}(.+)[\)]{2}/', '', $current_sample);
        }

        $current_sample = trim($current_sample);

        if (!empty($current_sample)) {
            $this->primary_entries[$this->i]['entry'] = self::create([
                'word_id'   => $this->word_id,
                'sample'    => $current_sample,
                'primary'   => true,
                'bracketed' => $this->bracketed,
                'sentenced'  => $this->sentence,
            ]);

            $this->i++;

            return true;
        } else {
            if (isset($this->primary_entries[$this->i]) and !empty($this->primary_entries[$this->i])) {
                unset($this->primary_entries[$this->i]);
            }
            // array_pop($this->primary_entries);

            return true;
        }
        
        return false;
    }



    public function retrieveScopes($line) {
        preg_match('/[\[]+([^]]+)[\]]+/', $line, $matches);

        if (strpos($matches[1], ',')) {
            $entries = explode(",", $matches[1]);

            foreach ($entries as $entry) {
                $this->primary_entries[$this->i]['scopes'][] = trim($entry);
            }
        } else {
            $this->primary_entries[$this->i]['scopes'][] = trim($matches[1]);
        }

        return true;
    }    

    public function retrieveSources($line) {
        preg_match('/[#]+([^#]+)[#]+/', $line, $matches);
        $this->primary_entries[$this->i]['sources'] = trim($matches[1]);

        return true;
    }    

    public function retrieveAuthor($line) {
        preg_match('/[$]+([^$]+)[$]+/', $line, $matches);
        $this->primary_entries[$this->i]['author'] = trim($matches[1]);

        return true;
    }    

    public function retrieveAnswer($line) {
        preg_match('/[&]+([^&]+)[&]+/', $line, $matches);
        $this->primary_entries[$this->i]['answer'] = trim($matches[1]);

        return true;
    }    

    public function retrieveAnnotation($line) {
        preg_match('/[\(]{2}(.+)[\)]{2}/', $line, $matches);
        $this->primary_entries[$this->i]['annotation'] = trim($matches[1]);

        return true;

    }    
}
