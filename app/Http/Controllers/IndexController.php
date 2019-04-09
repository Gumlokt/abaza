<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Ending;
use App\Plural;
use App\Origin;

use App\Category;

use App\Type;
use App\Reference;

use App\Word;
use App\Variation;
use App\Comment;

use App\Sample;
use App\Annotation;
use App\Answer;
use App\Alternative;



class IndexController extends Controller {
    public function index() {
        return view('index');
    }


    public function autocomplete(Request $request) {
        $term = $request->input('term');
        $term = preg_replace("/[^абвгдеёжзийклмнопрстуфхцчшщъыьэюяАБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ \-,]/", "I", $term);

        $selection = Word::where('word', 'like', $term . '%')
            ->select('word')
            ->distinct()
            ->orderBy('word', 'asc')
            ->get();

        $words = [];
        foreach ($selection as $value) {
            $words[] = $value->word;
        }

        return $words;
    }



    public function translation(Request $request) {
        $word = $request->input('word');
        $word = trim($word);
        $word = preg_replace("/[^абвгдеёжзийклмнопрстуфхцчшщъыьэюяАБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ \-,]/", "I", $word);

        if (empty($word)) {
            return 'Empty request. Type at least one letter and try again...';
        }

        $words = Word::where('word', '=', $word)
            ->orderBy('id', 'asc')
            ->get();

        if (!isset($words[0])) {
            $term = 'word';

            if (strpos($word, ' ')) {
                $term = 'phrase';
            }

            return 'There is no such ' . $term . '...';
        }


        $translations = '';
        foreach ($words as $word) {

            // Start of dictionary cell wrapper
            $translations .= "<div class='nttabulova'>";


            // Start of word block (first line)
            $translations .= "<div class='tnt_wb'>";


            // Grammar category number like: I., II., III...
            $translations .= "<span class='tnt_hn'>" . $word->category->category . ".</span>";


            // Word itself - in other words - the dictionary cell
            $translations .= " <span class='tnt_h'>" . $word->stress . "</span>";
            if (!$word->independent) {
                $translations .= ":";
            }
            $translations .= "</span>";


            // Endings like: (-ра, -дъа)
            $endings = $word->endings()->get();
            if (isset($endings[0])) {
                $translations .= " <span class='tnt_e'>(";

                $last_key = count($endings) - 1;
                foreach ($endings as $key => $ending) {
                    $translations .= $ending->ending;

                    if ($key < $last_key) {
                        $translations .= ", ";
                    }
                }

                $translations .= ")</span>";
            }


            // Plurals like: {-чва, -ква}
            $plurals = $word->plurals()->get();
            if (isset($plurals[0])) {
                $translations .= " <span class='tnt_p'>{";

                $last_key = count($plurals) - 1;
                foreach ($plurals as $key => $plural) {
                    $translations .= $plural->plural;

                    if ($key < $last_key) {
                        $translations .= ", ";
                    }
                }

                $translations .= "}</span>";
            }


            // References for words - shows scopes like [грам.], [миф.], [хим.] etc...
            $references = $word->references()->get();
            if (isset($references[0])) {
                $translations .= " <span class='tnt_r'>[";


                $last_key = count($references) - 1;
                foreach ($references as $key => $reference) {
                    $translations .= "<span title='" . $reference->description . "'>" . $reference->abbreviation . "</span>";

                    if ($key < $last_key) {
                        $translations .= ", ";
                    }
                }

                $translations .= "]</span>";
            }


            // Origins - shows from which language the word is borrowed
            $origins = $word->origins()->get();
            if (isset($origins[0])) {
                $translations .= " <span class='tnt_bl' title='Ажва йгIазлыху абызшва гIвыма(ква)'><span class='tnt_b'>*</span>(";

                $last_key = count($origins) - 1;
                foreach ($origins as $key => $origin) {
                    $translations .= $origin->origin;

                    if ($key < $last_key) {
                        $translations .= ", ";
                    }
                }

                $translations .= ")</span>";
            }


            // Comments for words
            $comments = $word->comments()->get();
            if (isset($comments[0])) {
                $translations .= " <span class='tnt_c'>(";

                $last_key = count($comments) - 1;
                foreach ($comments as $key => $comment) {
                    if (!empty($comment->letter)) {
                        $translations .= "«<span class='tnt_l'>" . $comment->letter . "</span>» ";
                    }

                        $translations .= $comment->comment;

                    if ($key < $last_key) {
                        $translations .= "; ";
                    }
                }

                $translations .= ")</span>";
            }


            // End of word block (first line)
            $translations .= "</div>\n";


            // Variations for words
            $variations = $word->variations()->get();
            if (isset($variations[0])) {
                $translations .= "<div class='tnt_vb'>Датша хIващаква:";
                $translations .= " <span class='tnt_v'>";

                $last_key = count($variations) - 1;
                foreach ($variations as $key => $variation) {
                    $translations .= $variation->stress;

                    // Origins - shows from which language the variation is borrowed
                    $origins = $variation->origins()->get();
                    if (isset($origins[0])) {
                        $translations .= " <span class='tnt_bl' title='Ажва йгIазлыху абызшва гIвыма(ква)'><span class='tnt_b'>*</span>(";

                        $last_origins_key = count($origins) - 1;
                        foreach ($origins as $key => $origin) {
                            $translations .= $origin->origin;

                            if ($key < $last_origins_key) {
                                $translations .= ", ";
                            }
                        }

                        $translations .= ")</span>";
                    }

                    if ($key < $last_key) {
                        $translations .= "; ";
                    }
                }

                $translations .= "</span>";


                // Plurals for variations like: {-чва, -ква}
                $plurals = $variations[0]->plurals()->get();
                if (isset($plurals[0])) {
                    $translations .= " <span class='tnt_p'>{";

                    $last_key = count($plurals) - 1;
                    foreach ($plurals as $key => $plural) {
                        $translations .= $plural->plural;

                        if ($key < $last_key) {
                            $translations .= ", ";
                        }
                    }

                    $translations .= "}</span>";
                }

                $translations .= "</div>";
            }


            // Start to handle samples
            // Start of primary samples
            $primary_samples = Sample::where('word_id', '=', $word->id)
                ->where('primary', '=', 1)
                ->get();

            if (isset($primary_samples[0])) {
                $translations .= "<table class='tnt_psb'>";
                $translations .= "<tbody>";

                $last_key = count($primary_samples) - 1;
                foreach ($primary_samples as $key => $sample) {
                    $translations .= "<tr class='tnt_tr'>";
                    $translations .= "<td class='tnt_ps'>";

                    $translations .= $sample->sample;

                        // Annotations for samples - shows scopes like #КытчапI.#
                        $annotations = $sample->annotations()->get();
                        if (isset($annotations[0])) {
                            $translations .= " <span class='tnt_ann'>(" . $annotations[0]->annotation . ")</span>";
                        }


                        // References for primary samples - shows scopes, sources and authors like [грам.], #Ажв.#, $Т. И.$ etc...
                        $references = $sample->references()->get();
                        if (isset($references[0])) {
                            $translations .= " <span class='tnt_r'>[";

                            $last_key = count($references) - 1;
                            foreach ($references as $key => $reference) {
                                $translations .= "<span title='" . $reference->description . "'>" . $reference->abbreviation . "</span>";

                                if ($key < $last_key) {
                                    $translations .= ", ";
                                }
                            }

                            $translations .= "]</span>";
                        }


                        // Answers for samples - shows scopes like #КытчапI.#
                        $answers = $sample->answers()->get();
                        if (isset($answers[0])) {
                            $translations .= " <span class='tnt_c'>(Джьауап: ";
                            $translations .= "<span class='tnt_ans'>" . $answers[0]->answer . "</span>";
                            $translations .= ")</span>";
                        }


                    if (!$sample->sentenced) {
                        $translations .= ";";
                    }

                    $translations .= "</td>";
                    $translations .= "</tr>";
                }

                $translations .= "</tbody>";
                $translations .= "</table>";
            }
            // End of Primary samples


            // Start of Secondary samples
            $secondary_samples = Sample::where('word_id', '=', $word->id)
                ->where('primary', '=', 0)
                ->get();

            if (isset($secondary_samples[0])) {
                $translations .= "<table class='tnt_ssb'>";
                $translations .= "<tbody>";

                $last_key = count($secondary_samples) - 1;
                foreach ($secondary_samples as $key => $sample) {
                    $translations .= "<tr class='tnt_tr'>";
                    $translations .= "<td>";
                    $translations .= "<span class='tnt_ss'>";

                    $translations .= $sample->sample;
                    $translations .= "</span>";


                        // References for secondary samples - shows scopes, sources and authors like [грам.], #Ажв.#, $Т. И.$ etc...
                        $references = $sample->references()->get();
                        if (isset($references[0])) {
                            $translations .= " <span class='tnt_r'>[";

                            $last_key = count($references) - 1;
                            foreach ($references as $key => $reference) {
                                $translations .= "<span title='" . $reference->description . "'>" . $reference->abbreviation . "</span>";

                                if ($key < $last_key) {
                                    $translations .= ", ";
                                }
                            }

                            $translations .= "]</span>";
                        }


                        // Alternatives for secondary samples
                        $alternatives = $sample->alternatives()->get();
                        if (isset($alternatives[0])) {
                            $translations .= " <span class='tnt_alt'>/";

                            $last_key = count($alternatives) - 1;
                            foreach ($alternatives as $key => $alternative) {
                                $translations .= $alternative->alternative;

                                if ($key < $last_key) {
                                    $translations .= "; ";
                                }
                            }

                            $translations .= "/</span>";
                        }



                    $translations .= "</span>";
                    $translations .= "</tr>";
                }


                $translations .= "</tbody>";
                $translations .= "</table>";
            }
            // End of Secondary samples



            // Author of Dictionary
            $translations .= "<div class='author'>Tabulova N. T.</div>";

            // End of dictionary cell wrapper
            $translations .= "</div>";
        }


        return $translations;
    }
}
