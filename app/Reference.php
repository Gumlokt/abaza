<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Type;

class Reference extends Model {
    protected $guarded = ['id', 'created_at', 'updated_at', ];
    public $scopes = [];
    public $sources = [];
    public $authors = [];

    public $current_ids = [];


    public function type() {
        return $this->belongsTo('App\Types');
    }

    public function words() {
        return $this->belongsToMany('App\Word');
    }

    public function samples() {
        return $this->belongsToMany('App\Sample');
    }





    // [] [] [] [] []
    // Retrieve scope IDs like [грам.], [дин], [шIырпш.] etc...
    public function retrieveScopeIds($scopes) {
        $this->current_ids = [];

        foreach ($scopes as $scope) {
            $this->current_ids[] = array_search($scope, $this->scopes);
        }

        return $this->current_ids;
    }

    // ! Note.: There are lines, where #-character appears twice (eg. #Ажв.#, #КытчапI.# &Some answer.&)
    // # # # # # # # # #
    // Retrieve sources like #Абаз. алокI.#, #Ажв.#, #КытчапI.#, #Наш.# etc...
    public function retrieveSourceIds($line) {
        $this->current_ids = [];

        if (strpos($line, ',')) {
            $entries = explode(", ", trim($line));

            foreach ($entries as $entry) {
                $this->current_ids[] = array_search($entry, $this->sources);
            }
        } else {
            $this->current_ids[] = array_search($line, $this->sources);
        }

        return $this->current_ids;
    }

    // $ $ $ $ $ $ $ $ $
    // Retrieve authors like $Джь. Къ.$, $Й. ГI.$, $Л. Джь.$ etc...
    public function retrieveAuthorId($line) {
        $this->current_ids = [];

        $this->current_ids[] = array_search($line, $this->authors);

        return $this->current_ids;
    }





    // Fill in (populate) field "scopes" in table "references" with scopes like [грам.], [дин], [шIырпш.] etc...
    protected function fillInScopes($type_id) {
        foreach ($this->scopesList as $scope) {
            $entry = self::create([ 'type_id' => $type_id, 'abbreviation' => $scope['abbreviation'], 'description' => $scope['description'] ]);

            $this->scopes[$entry->id] = $entry->abbreviation;
        }
    }

    public function getAllScopes($type_id) {
        $scopes = self::where('type_id', '=', $type_id)->get();

        if (!isset($scopes[0])) {
            $this->fillInScopes($type_id);
        } else {
            foreach ($scopes as $scope) {
                $this->scopes[$scope->id] = $scope->abbreviation;
            }
        }
    }


    // Fill in (populate) field "sources" in table "references" with sources like #Абаз. алокI.#, #Ажв.#, #КытчапI.#, #Наш.# etc...
    protected function fillInSources($type_id) {
        foreach ($this->sourcesList as $source) {
            $entry = self::create([ 'type_id' => $type_id, 'abbreviation' => $source['abbreviation'], 'description' => $source['description'] ]);

            $this->sources[$entry->id] = $entry->abbreviation;
        }
    }

    public function getAllSources($type_id) {
        $sources = self::where('type_id', '=', $type_id)->get();

        if (!isset($sources[0])) {
            $this->fillInSources($type_id);
        } else {
            foreach ($sources as $source) {
                $this->sources[$source->id] = $source->abbreviation;
            }
        }
    }


    // Fill in (populate) field "authors" in table "references" with authors like $Джь. Къ.$, $Й. ГI.$, $Л. Джь.$ etc...
    protected function fillInAuthors($type_id) {
        foreach ($this->authorsList as $author) {
            $entry = self::create([ 'type_id' => $type_id, 'abbreviation' => $author['abbreviation'], 'description' => $author['description'] ]);

            $this->authors[$entry->id] = $entry->abbreviation;
        }
    }

    public function getAllAuthors($type_id) {
        $authors = self::where('type_id', '=', $type_id)->get();

        if (!isset($authors[0])) {
            $this->fillInAuthors($type_id);
        } else {
            foreach ($authors as $author) {
                $this->authors[$author->id] = $author->abbreviation;
            }
        }
    }



    protected $scopesList = [
        [
            'abbreviation' => 'абджьарпI',
            'description' => 'абджьарпI'
        ],
        [
            'abbreviation' => 'ажвы',
            'description' => 'ажвыра зхьыз ажваква'
        ],
        [
            'abbreviation' => 'асхъапI',
            'description' => 'асхъапI'
        ],
        [
            'abbreviation' => 'ахчапI',
            'description' => 'ахчапI'
        ],
        [
            'abbreviation' => 'ащтанчIвыпI',
            'description' => 'ащтанчIвыпI'
        ],
        [
            'abbreviation' => 'гIайырапI',
            'description' => 'гIайырапI'
        ],
        [
            'abbreviation' => 'гIахIвыщапI',
            'description' => 'гIахIвыщапI'
        ],
        [
            'abbreviation' => 'геогр.',
            'description' => 'агеография йапщылу ажваква'
        ],
        [
            'abbreviation' => 'грам.',
            'description' => 'аграмматика йапщылу ажваква'
        ],
        [
            'abbreviation' => 'дин',
            'description' => 'адин йапщылу ажваква'
        ],
        [
            'abbreviation' => 'динпI',
            'description' => 'динпI'
        ],
        [
            'abbreviation' => 'згIвапI',
            'description' => 'згIвапI'
        ],
        [
            'abbreviation' => 'ист.',
            'description' => 'история термин, история йазынарху'
        ],
        [
            'abbreviation' => 'къвалыпI',
            'description' => 'къвалыпI'
        ],
        [
            'abbreviation' => 'кыбпI',
            'description' => 'кыбпI'
        ],
        [
            'abbreviation' => 'кыбцIлапI',
            'description' => 'кыбцIлапI'
        ],
        [
            'abbreviation' => 'ларгылажвапI',
            'description' => 'ларгылажвапI'
        ],
        [
            'abbreviation' => 'лингв.',
            'description' => 'алингвистика йапщылу ажваква'
        ],
        [
            'abbreviation' => 'мажвадзалыцIпI',
            'description' => 'мажвадзалыцIпI'
        ],
        [
            'abbreviation' => 'мат.',
            'description' => 'амататематика йапщылу ажваква'
        ],
        [
            'abbreviation' => 'миф.',
            'description' => 'амифология йапщылу ажваква'
        ],
        [
            'abbreviation' => 'пслачвапI',
            'description' => 'пслачвапI'
        ],
        [
            'abbreviation' => 'пслачвахьызпI',
            'description' => 'пслачвахьызпI'
        ],
        [
            'abbreviation' => 'пссгIачIвыпI',
            'description' => 'пссгIачIвыпI'
        ],
        [
            'abbreviation' => 'спорт.',
            'description' => 'аспорт йапщылу ажваква'
        ],
        [
            'abbreviation' => 'титул',
            'description' => 'титул'
        ],
        [
            'abbreviation' => 'тштлапIкъпI',
            'description' => 'тштлапIкъпI'
        ],
        [
            'abbreviation' => 'филос.',
            'description' => 'афилос йапщылу ажваква'
        ],
        [
            'abbreviation' => 'хIапшхвыпшпI',
            'description' => 'хIапшхвыпшпI'
        ],
        [
            'abbreviation' => 'хIахъвтлапIапI',
            'description' => 'хIахъвтлапIапI'
        ],
        [
            'abbreviation' => 'хIисап',
            'description' => 'хIисап'
        ],
        [
            'abbreviation' => 'хвапI',
            'description' => 'хвапI'
        ],
        [
            'abbreviation' => 'хим.',
            'description' => 'ахимим йапщылу ажваква'
        ],
        [
            'abbreviation' => 'хъвмаргапI',
            'description' => 'хъвмаргапI'
        ],
        [
            'abbreviation' => 'хъвмарщапI',
            'description' => 'хъвмарщапI'
        ],
        [
            'abbreviation' => 'хъвхвыцпI',
            'description' => 'хъвхвыцпI'
        ],
        [
            'abbreviation' => 'цIлапI',
            'description' => 'цIлапI'
        ],
        [
            'abbreviation' => 'цIлахьызпI',
            'description' => 'цIлахьызпI'
        ],
        [
            'abbreviation' => 'шIтражвапI',
            'description' => 'шIтражвапI'
        ],
        [
            'abbreviation' => 'шIтыгажвапI',
            'description' => 'шIтыгажвапI'
        ],
        [
            'abbreviation' => 'шIырпш.',
            'description' => 'шIырпшыгажва'
        ],
        [
            'abbreviation' => 'шахм.',
            'description' => 'ашахмат йапщылу ажваква'
        ],
        [
            'abbreviation' => 'швырпI',
            'description' => 'швырпI'
        ],
    ];

    protected $sourcesList = [
        [
            'abbreviation' => '«Ком. ал.»',
            'description' => 'Газет «Коммунизм алашара»'
        ],
        [
            'abbreviation' => 'АУА',
            'description' => 'Абаза-урышв ажвар. Тыгв Владимир даредакторхъадапI. - М.: Изд. «Совет энциклопедия», 1967'
        ],
        [
            'abbreviation' => 'Абаз. алокI.',
            'description' => 'Абазашта алокIква. Тыгв Владимир йалайцIатI, Черкесск, 1965'
        ],
        [
            'abbreviation' => 'Абаза т.',
            'description' => 'Абаза турыхква, Ставрополь, 1947; Абаза турыхква, Черкесск 1955'
        ],
        [
            'abbreviation' => 'Абазашта.',
            'description' => 'газет Абазашта'
        ],
        [
            'abbreviation' => 'Ажв.',
            'description' => 'Ажважв'
        ],
        [
            'abbreviation' => 'Ашахв.',
            'description' => 'Тобыль Толистан. Ашахв акIкIара, Черкесск, 1982'
        ],
        [
            'abbreviation' => 'Дадыра.',
            'description' => 'Дадыра'
        ],
        [
            'abbreviation' => 'Кына йпхIа Минат.',
            'description' => 'Кына йпхIа Минат.'
        ],
        [
            'abbreviation' => 'КытчапI.',
            'description' => 'КытчапI'
        ],
        [
            'abbreviation' => 'ЛокI.',
            'description' => 'ЛокI, локI, лакIвыца. Тыгв Владимир йазикIкIын, йанйыргIалхтI, Черкесск, 1968'
        ],
        [
            'abbreviation' => 'НСС',
            'description' => 'НСС'
        ],
        [
            'abbreviation' => 'Нарт.',
            'description' => 'Нарт.'
        ],
        [
            'abbreviation' => 'Наш.',
            'description' => 'АуагIа рнашанаква'
        ],
        [
            'abbreviation' => 'Шахв.',
            'description' => 'Шахв, Черкесск, 1965'
        ],
    ];

    protected $authorsList = [
        [
            'abbreviation' => 'Б. Кв.',
            'description' => 'Брат Квчыкв'
        ],
        [
            'abbreviation' => 'Дж. ХI.',
            'description' => 'Джыр ХIамид'
        ],
        [
            'abbreviation' => 'Джь. Къ.',
            'description' => 'Джьгватан Къали'
        ],
        [
            'abbreviation' => 'И. К.',
            'description' => 'И. Крылов'
        ],
        [
            'abbreviation' => 'Й. ГI.',
            'description' => 'Йуан ГIалиса'
        ],
        [
            'abbreviation' => 'К. Л.',
            'description' => 'Кетеван Ломтатидзе'
        ],
        [
            'abbreviation' => 'Л. Джь.',
            'description' => 'ЛагIвычв Джьамльадин'
        ],
        [
            'abbreviation' => 'М. К.',
            'description' => 'Мыхц Кьарим'
        ],
        [
            'abbreviation' => 'М. Н.',
            'description' => 'Муратыкъва Николай'
        ],
        [
            'abbreviation' => 'Н. А.',
            'description' => 'Найман ХIаджьдауыт'
        ],
        [
            'abbreviation' => 'Т. Б.',
            'description' => 'ТхIайцIыхв Бемырза'
        ],
        [
            'abbreviation' => 'Т. И.',
            'description' => 'Тобыль ИсмагIиль'
        ],
        [
            'abbreviation' => 'Т. Т.',
            'description' => 'Тобыль Тольыстан'
        ],
        [
            'abbreviation' => 'Тл. М.',
            'description' => 'Тлабыча Мира'
        ],
        [
            'abbreviation' => 'ХI. З.',
            'description' => 'ХIачвыкъва ЗамахIщари'
        ],
        [
            'abbreviation' => 'ХI. Ш.',
            'description' => 'ХIвран ШахIвали'
        ],
        [
            'abbreviation' => 'Ц. П.',
            'description' => 'Цекъва Пасарби'
        ],
        [
            'abbreviation' => 'Ч. М.',
            'description' => 'ЧквтIу Микаэль'
        ],
        [
            'abbreviation' => 'Чв. Ш.',
            'description' => 'ЧвзыкIьа ШахIымби'
        ],
        [
            'abbreviation' => 'Ш. К.',
            'description' => 'Шхай Катя'
        ],
    ];
}


// // How to get all occurrences of scopes from first line of dictionary cell
// // Total words with scopes: 1734
// // Total unique scopes: 43
// if (strpos($lines[0], '[')) {
//     preg_match_all('/[\[]+([^]]+)[\]]+/', trim($lines[0]), $matches);

//     $count = count($matches[1]);

//     for ($i = 0; $i < $count; $i++) {
//         if (strpos($matches[1][$i], ',')) {
//             $entries = explode(",", trim($matches[1][$i]));

//             foreach ($entries as $entry) {
//                 if (!in_array(trim($entry), $result)) {
//                     $result[] = trim($entry);
//                 }
//             }
//         } else {
//             if (!in_array($matches[1][$i], $result)) {
//                 $result[] = trim($matches[1][$i]);
//             }
//         }
//     }
// }


// // How to get all occurrences of sources from first line of dictionary cell
// // Total words with sources: 1434
// // Total unique sources: 15
// if (strpos($lines[0], '#')) {
//     preg_match('/[#]+([^#]+)[#]+/', trim($lines[0]), $matches);

//     if (strpos($matches[1], ',')) {
//         $entries = explode(", ", trim($matches[1]));

//         foreach ($entries as $entry) {
//             if (!in_array(trim($entry), $result)) {
//                 $result[] = trim($entry);
//             }
//         }
//     } else {
//         if (!in_array($matches[1], $result)) {
//             $result[] = trim($matches[1]);
//         }
//     }
// }


// // How to get all occurrences of authors from first line of dictionary cell
// // Total words with authors: 384
// // Total unique authors: 20
// if (strpos($lines[0], '$')) {
//     preg_match_all('/[$]+([^$]+)[$]+/', trim($lines[0]), $matches);

//     $count = count($matches[1]);

//     for ($i = 0; $i < $count; $i++) {
//         if (!in_array($matches[1][$i], $result)) {
//             $result[] = trim($matches[1][$i]);
//         }
//     }
// }
