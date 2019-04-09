<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;

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


class ParseController extends Controller {
    public function parse(Request $request, $action) {
        // Clear (recreate) all tables before parsing and storing all dictionary cells
        if ('store' == $action) {
            Artisan::call('migrate:refresh');
        }

        $time_start = microtime(true);
        $file = 'public/aorta.txt'; // path to dictionary (typical text file)

        $meta['size'] = Storage::size($file);
        $meta['exists'] = Storage::disk('local')->exists($file);
        $meta['file'] = $file;


        $dictionary = Storage::get($file); // STEP 1. read whole content from dictionary - this is typical text file
        $slots = explode("\r\n\r\n", $dictionary); // STEP 2. split content into dictionary cells (or slots)


        // Before handling dictionary cells, fill in tables: 'endings', 'plurals', 'categories', 'origins', 'types' and 'references'
        $endings = new Ending();
        $endings->getAllEndings();
        
        $plurals = new Plural();
        $plurals->getAllPlurals();

        $categories = new Category();
        $categories->getAllCategories();

        // These ['undefined', 'adyghe', 'arabic', 'russian', 'turkish'] are predefined values, that could be used as "enum" data type in MySQL
        $origins = new Origin();
        $origins->getAllOrigins();

        // These ['scope', 'source', 'author'] are predefined values, that could be used as "enum" data type in MySQL
        $types = new Type();
        $types->getAllTypes(); // Populate table "types" with values: 'scope', 'source', 'author'

        // predefined values, that could be used as "enum" data type in MySQL: $types = ['scope', 'source', 'author'];
        $references = new Reference();
        $references->getAllScopes(array_search('scope', $types->all)); // Fill in scopes
        $references->getAllSources(array_search('source', $types->all));
        $references->getAllAuthors(array_search('author', $types->all));


        $word = new Word();

        $variations = new Variation();
        $comments = new Comment();

        $samples = new Sample();
        $answer = new Answer();
        $annotation = new Annotation();
        $alternatives = new Alternative();


        // variables for test and stats purposes (they can be deleted in production version)
        $result = [];
        $total = 0;

        //! Parse & Store to database action
        if ('store' == $action) {
            // Starting the root loop: traversing all dictionary cells (or slots in other words) 
            foreach ($slots as $slot) {
                $samples = new Sample(); // every dictionary cell can contain samples


                $lines = explode("\r\n", $slot); // STEP 3. split every dictionary cell into lines

                // _I_, _II_, _III_, _IV_, _V_, _VI_ Retrieve and collect categories like _I_, _II_, _III_ etc...
                $categories->retrieveCategory($lines[0]);

                // *** Symbols that acts as delimiters and their precedence
                // ":="  - (colon and equal sign) delimits nonautonomous words from their examples
                // " + " - (space, "plus" character and space) delimits composite words from other parts of dictionary cell
                // " "   - (regular space) delimits words from other parts of dictionary cell
                // "\n"  - (new line character) delimits words from other dictionary cell
                // ***********************************************************************************
                // Insert data to main table named 'words'
                // If line contains ':=' characters it means that dictionary cell is non-autonomous (it can be used only in conjunction with other words)
                if (strpos($lines[0], ':=')) {
                    $parts = explode(":= ", $lines[0]);
                    $word->storeNonAutonomousWord($parts[0], $categories->current_id);

                    // $parts[1] contains primary samples
                    // $samples->storePrimarySamples($parts[1], $word->entry->id);
                    if (!$samples->storePrimarySamples($parts[1], $word->entry->id)) {
                        dd($lines[0]);
                    }

                // If line contains '+' character it means that dictionary cell consists of two or more words
                } elseif (strpos($lines[0], '+')) {
                    $parts = explode(" + ", $lines[0]);
                    $word->storeMultiWord($parts[0], $categories->current_id);

                    // [] [] [] [] []
                    // Retrieve scopes like [грам.], [дин], [шIырпш.] etc... They will be stored to table 'refrence_word' further
                    // '[' can appear at 0-th index, that's why expression "if(strpos($parts[1], "["))" does not work and we should use more complex and strict testing
                    if (!(false === strpos($parts[1], "["))) {
                        $word->extractScopes($parts[1]);
                    }

                    // $parts[1] contains primary samples
                    if (!$samples->storePrimarySamples($parts[1], $word->entry->id)) {
                        dd($lines[0]);
                    }

                // Absence of white space characters means that dictionary cell consists of only one word and there is no samples, endings or other stuff like that
                // In other words, lines like this contain only one word which is sole member of dictionary cell
                } elseif (!strpos($lines[0], ' ')) {
                    $word->storeSoleWord($lines[0], $categories->current_id);

                    // F, W, t, g, R - Retrieve and collect origins like F(undifind), W(adyghe), t(turkish), g(arabic), R(russian) for sole words
                    if ($origins->retrieveOrigins($lines[0])) {
                        // insert appropriate ids to table origin_word
                        $word->entry->origins()->syncWithoutDetaching($origins->current_ids);
                    }

                // All other lines contain different stuff like samples, endings, category numbers etc...
                } else {
                    if (strpos($lines[0], '/')) {
                        $parts = explode("/", $lines[0]);
                        $word->storeStuffedWord($parts[0], $categories->current_id);

                        // $parts[1] contains primary samples
                        if (!$samples->storePrimarySamples('/' . $parts[1] . '/', $word->entry->id)) {
                            dd($lines[0]);
                        }
                    } else {
                        $word->storeStuffedWord($lines[0], $categories->current_id);
                    }



                    // () () () () () Retrieve and collect endings like (-даъа), (-дъа), (-ра) etc...
                    if ($endings->retrieveEndings($lines[0])) {
                        // insert appropriate ids to corresponding table
                        $word->entry->endings()->syncWithoutDetaching($endings->current_ids);
                    }




                    // 'Z' character in first line means, that dictionary cell contains variation(s)
                    if (strpos($lines[0], 'Z')) {
                        $line_parts = explode('Z', $lines[0]);

                        // {} {} {} {} {} Retrieve and collect plurals like {-ква}, {-чва}, {-чваква} for word
                        if ($plurals->retrievePlurals($line_parts[0])) {
                            // insert appropriate ids to corresponding table
                            $word->entry->plurals()->syncWithoutDetaching($plurals->current_ids);
                        }

                        // F, W, t, g, R - Retrieve and collect origins like F(undifind), W(adyghe), t(turkish), g(arabic), R(russian) for word
                        if ($origins->retrieveOrigins($line_parts[0])) {
                            // insert appropriate ids to table origin_word
                            $word->entry->origins()->syncWithoutDetaching($origins->current_ids);
                        }


                        // Fill in (populate) table "variations"
                        $variations->fillInVariations($lines[0], $word->entry->id);

                        foreach ($variations->entries as $key => $variation) {
                            // {} {} {} {} {} Retrieve and collect plurals like {-ква}, {-чва}, {-чваква} for variation(s)
                            if ($plurals->retrievePlurals($line_parts[2])) {
                                $variation->plurals()->syncWithoutDetaching($plurals->current_ids);
                            }

                            // F, W, t, g, R - Retrieve and collect origins like F(undifind), W(adyghe), t(turkish), g(arabic), R(russian) for variation(s)
                            if ($origins->retrieveOrigins($key)) {
                                $variation->origins()->syncWithoutDetaching($origins->current_ids);
                            }
                        }

                    } else {
                        // {} {} {} {} {} Retrieve and collect plurals like {-ква}, {-чва}, {-чваква} for word
                        if ($plurals->retrievePlurals($lines[0])) {
                            // insert appropriate ids to corresponding table
                            $word->entry->plurals()->syncWithoutDetaching($plurals->current_ids);
                        }

                        // F, W, t, g, R - Retrieve and collect origins like F(undifind), W(adyghe), t(turkish), g(arabic), R(russian) for variation(s)
                        if ($origins->retrieveOrigins($lines[0])) {
                            // insert appropriate ids to table origin_word
                            $word->entry->origins()->syncWithoutDetaching($origins->current_ids);
                        }
                    }
                } // end of store word with different stuff


                // finishing handle primary samples
                // if exists store data to tables: 'annotations', 'answers' and 'reference_sample'
                if (!empty($samples->primary_entries)) {
                    foreach ($samples->primary_entries as $sample) {
                        // store sources ## ## ##
                        if (isset($sample['sources'])) {
                            $source_ids = $references->retrieveSourceIds($sample['sources']);
                            $sample['entry']->references()->syncWithoutDetaching($source_ids);
                        }

                        // store scopes [] [] []
                        if (isset($sample['scopes'])) {
                            $scope_ids = $references->retrieveScopeIds($sample['scopes']);
                            $sample['entry']->references()->syncWithoutDetaching($scope_ids);
                        }

                        // store author $$ $$ $$
                        if (isset($sample['author']) and !empty($sample['author'])) {
                            $author_id = $references->retrieveAuthorId($sample['author']);
                            $sample['entry']->references()->syncWithoutDetaching($author_id);
                        }

                        // store 'annotations' if exists
                        if (isset($sample['annotation']) and !empty($sample['annotation'])) {
                            $annotation->storeAnnotation($sample['annotation'], $sample['entry']->id);
                        }

                        // store 'answers' if exists
                        if (isset($sample['answer']) and !empty($sample['answer'])) {
                            $answer->storeAnswer($sample['answer'], $sample['entry']->id);
                        }
                    }
                }



                // Fill in (populate) table "comments"
                // '%' character in first line means, that line contains comment
                if (strpos($lines[0], '%')) {
                    $comments->fillInComments($lines[0], $word->entry->id);
                }


                // [] [] [] [] [] Scopes
                // Check if there scopes for word and if they are exist insert appropriate ids to corresponding table
                if (!empty($word->current_scopes)) {
                    $scope_ids = $references->retrieveScopeIds($word->current_scopes);
                    $word->entry->references()->syncWithoutDetaching($scope_ids);
                }



                // Handle secondary samples
                // other elements if exist (lines beginning from 2nd and higher) contain secondary samples
                if (count($lines) > 1) {
                    array_shift($lines); // STEP 4. remove 0-th line to keep only secondary samples and then parse them
                    $samples->storeSecondarySamples($lines, $word->entry->id);

                    // if exists store data to tables: 'alternatives' and 'reference_sample'
                    if (!empty($samples->secondary_entries)) {
                        foreach ($samples->secondary_entries as $sample) {
                            // store scopes [] [] []
                            if (isset($sample['scopes'])) {
                                $scope_ids = $references->retrieveScopeIds($sample['scopes']);
                                $sample['entry']->references()->syncWithoutDetaching($scope_ids);
                            }

                            // store 'alternatives' if exists
                            if (isset($sample['alternatives']) and !empty($sample['alternatives'])) {
                                $alternatives->storeAlternatives($sample['alternatives'], $sample['entry']->id);
                            }
                        }
                    }
                }

            }
        } else {
            //! Only parse action (simulate storing to DB)
            // Starting the root loop: traversing all dictionary cells (or slots in other words) 
            foreach ($slots as $slot) {
                $lines = explode("\r\n", $slot);

                // if (count($lines) > 1) {
                //     array_shift($lines);
                // }

                // if (substr_count($lines[0], ']') > 1) {
                // if (preg_match('/́/', $line)) {
                // if (preg_match('/[^а-яА-ЯI]/u', $line) and !preg_match('/[́^]+/', $line)) {
                // $line = str_replace(['́', ], '', $line);
                // $line = preg_replace('/[^а-яI\/\[\]\(\)ё.,;«» -]+/iu', 'W', $line);
                // if (preg_match_all('/[^а-яА-ЯI]+/u', $line)) {
                // if (strpos($lines[0], '/')) {
                // if (preg_match('/[FWtgR]+/u', $lines[0])) {

                
                    // if (preg_match('/ль/iu', $lines[0])) {
                if (strpos($lines[0], '+') and strpos($lines[0], ':')) {
                    // $parts = explode("%", $lines[0]);

                    $result[] = $lines[0];
                    $total++;
                }



                // if (strpos($lines[0], ':=')) {
                //     $parts = explode(":= ", $lines[0]);

                //     if (preg_match('/ль/iu', $parts[0]) and !preg_match('/[FWtgR]+/u', $lines[0])) {
                //         $result[] = $parts[0];
                //         $total++;
                //     }

                // } elseif (strpos($lines[0], '+') and !preg_match('/[FWtgR]+/u', $lines[0])) {
                //     $parts = explode(" + ", $lines[0]);

                //     if (preg_match('/ль/iu', $parts[0])) {
                //         $result[] = $parts[0];
                //         $total++;
                //     }
                // } elseif (!strpos($lines[0], ' ')) {
                //     if (preg_match('/ль/iu', $lines[0]) and !preg_match('/[FWtgR]+/u', $lines[0])) {
                //         $result[] = $lines[0];
                //         $total++;
                //     }
                // } else {
                //     $parts = explode(" ", $lines[0]);

                //     if (preg_match('/ль/iu', $parts[0]) and !preg_match('/[FWtgR]+/u', $lines[0])) {
                //         $result[] = $parts[0];
                //         $total++;
                //     }
                // }

                // а́и́о́ы́
            }
        }

        // $result = array_unique($result);
        // sort($result, SORT_STRING);
        $time_end = microtime(true);
        $time_elapsed = $time_end - $time_start;
        return view('index', [ "meta" => $meta, "content" => $result, "total" => $total, "time_elapsed" => ceil($time_elapsed) ]);
    }
}
