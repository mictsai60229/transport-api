<?php

namespace App\Services\Elasticsearch;

use Illuminate\Support\Facades\Storage;
use App\Repositories\Elasticsearch\Cat as EsCatRepo;
use App\Repositories\Elasticsearch\Indices as EsIndicesRepo;
use App\Exceptions\CommonApiException;

use App\Formatters\Response\Common\IndicesCreateResponse;
use App\Formatters\Response\Common\IndicesChangeResponse;

Class Indices{

    protected $es_indices_repo;
    protected $es_cat_repo;

    public function __construct(){
        $this->es_indices_repo = new EsIndicesRepo;
        $this->es_cat_repo  = new EsCatRepo; 
    }

    /**
     * Undocumented function
     *
     * @param array $req_params
     * @return void
     */
    public function create(array $req_params){

        // check $index-latest is setted
        $latest_alias = "{$req_params['index']}-latest";
        if(!empty($this->catAlias($latest_alias))){
            throw new CommonApiException("Alias with name {$latest_alias} exist.");
        }

        $delete_indices = $this->deleteIndices($req_params['index'], $req_params['backup_count'], $req_params['docs_threshold']);

        //add timestamp
        $date = date("YmdHis");
        $index_timestamp = "{$req_params['index']}-{$date}";

        $params = [
            'index' => $index_timestamp,
            'body' => $this->getCreateBody("elasticsearch/{$req_params['index']}")
        ];

        $es_response = $this->es_indices_repo->create($params);
        
        //set create_index alias to "{$index}-latest"
        $create_index = $es_response["index"];
        $actions = [];
        $actions[] = $this->updateAliasFormatter("add", $create_index, $latest_alias);
        $this->updateAliases($actions);

        $response_formatter = new IndicesCreateResponse($es_response, ["delete"=>$delete_indices]);
        return $response_formatter;
        
    }


    protected function deleteIndices(string $index, int $backup_count=0, float $docs_threshold=0.7){

        $indices = $this->countIndex($index, $docs_threshold);
        $current_index = $this->catAlias($index);
        $delete_indices = [];


        $indices = array_filter($indices, function($v) use(&$current_index){
            return $v !== $current_index;
        });
        $indices = array_values($indices);

        // delete index not in backupCount
        $indices_count = count($indices);

        for ($i=$backup_count;$i<$indices_count;$i++){

            $this->delete($indices[$i]);
            $delete_indices[] = $indices[$i];
        }


        return $delete_indices;
    }


    /*
    * Set Alias to the newest created not empty index
    * @param string $index
    * @return Array[string]
    */
    public function change(array $req_params){
        
        $aliases_actions = [];
        $data = [];

        $latest_alias = "{$req_params['index']}-latest";
        $latest_index = $this->catAlias($latest_alias);

        if (!isset($req_params['target_index'])){
            $req_params['target_index'] = $latest_index;
        }
        
        if(!isset($req_params['target_index'])){
            throw new CommonApiException("Alias with name {$latest_alias} or parameter target_index should be set.");
        }

        $this->refresh($req_params['target_index']);

        $main_index = $this->catAlias($req_params['index']);
        if (!empty($main_index)){
            $aliases_actions[] = $this->updateAliasFormatter("remove", $main_index, $req_params['index']);
            $data['old_index'] = $main_index;

            //check $target_index.docs_count > $remove_index.docs_count * $docs_threshold
            if (! $this->compareIndex($req_params['target_index'], $main_index, $req_params['docs_threshold'])){
                throw new CommonApiException("Index with name {$req_params['target_index']} doen't have enough data");
            };
        }


        if ($latest_index === $req_params['target_index']){
            $this->setInterval($req_params['target_index'], "10s");
            $aliases_actions[] = $this->updateAliasFormatter("remove", $req_params['target_index'], $latest_alias);
        }
        
        $aliases_actions[] = $this->updateAliasFormatter("add", $req_params['target_index'], $req_params['index']);
        $data['index'] = $req_params['target_index'];
        
        $es_response = $this->updateAliases($aliases_actions);

        $response_formatter = new IndicesChangeResponse($es_response, $data);
        return $response_formatter;
    }

    /*
    * Count index starts with $index, sort according to content
    * @param string $index
    * @return Array[string]
    */
    protected function countIndex(string $index, float $docs_threshold=0.7){
        
        $indices_info = $this->es_cat_repo->indices([]);

        //filter index with "{$index}-{$timestamp}"
        $pattern = "/{$index}\-\d{14}/";
        $indices_info = array_filter($indices_info, function($v) use(&$pattern){
            return preg_match($pattern, $v['index']);
        });
        $indices_info = array_values($indices_info);

        $current_docs_count = 0;
        $docs_count =[];
        $index_names = [];

        $current_index = $this->catAlias($index);

        foreach($indices_info as $index_info){
            $docs_count[] =  $index_info['docs.count'];
            $index_names[] = $index_info['index'];

            if ($index_info['index'] === $current_index){
                $current_docs_count = $index_info['docs.count'];
            }
        }
        
        $validate_docs_count = $docs_threshold*$current_docs_count;
        $docs_count = array_map(function($v) use(&$validate_docs_count){
            if ($v >= $validate_docs_count){
                return 1;
            }
            else{
                return 0;
            }
        }, $docs_count);

        array_multisort($docs_count, SORT_NUMERIC, SORT_DESC,
            $index_names, SORT_STRING, SORT_DESC,
            $indices_info);

        $indices = [];
        foreach ($indices_info as $index_info){
            $indices[] = $index_info['index'];
        }

        return $indices;
    }

    /*
    * get elasticsearch mappings and settings
    * @param string $configPath
    * @return string (raw Json)
    */
    protected function getCreateBody(string $config_path){
    
        $mappings_json = Storage::disk('local')->get("{$config_path}/mappings.json");
        $settings_json = Storage::disk('local')->get("{$config_path}/settings.json");

        return "{
            \"mappings\" : {$mappings_json},
            \"settings\" : {$settings_json}
        }";
    }

    /*
    * return the index point to alias
    * @param string $name
    * @return array(string)
    */
    protected function catAlias(string $name){
        
        $params = [
            'name' => $name
        ];

        $aliases_result = $this->es_cat_repo->aliases($params);

        $index = null;
        if (count($aliases_result)>0){
            $index = $aliases_result[0]["index"];
        }

        return $index;
    }

    /*
    * Update Alias settings
    * @param string $action, string $index, string $alias
    * @return Json
    */
    protected function updateAliases(array $actions){

        $params = [
            'body' => [
                'actions' => $actions
            ]
        ];

        return $this->es_indices_repo->updateAliases($params);
    }

    /*
    * Delete index
    * @param string $index
    * @return Json
    */
    protected function delete(string $index){
        
        $params = [
            "index" => $index
        ];

        return $this->es_indices_repo->delete($params);
    }

    /*
    * Refresh Index
    * @param string $index
    * @return Json
    */
    protected function refresh(string $index){
        
        $params = [
            'index' => $index
        ];

        return $this->es_indices_repo->refresh($params);
    }

    /*
    * Set refresh_interval of Index
    * @param string $index, string interval
    * @return Json
    */
    protected function setInterval(string $index, string $interval){
        
        $params = [
            'index' => $index,
            'body' => ['refresh_interval' => $interval ]
        ];

        return $this->es_indices_repo->putSettings($params);
    }

    protected function updateAliasFormatter(string $action, string $index, string $alias){

        $alias_format = [
            $action => [
                'index' => $index,
                'alias' => $alias
            ]
        ];

        if ($action === 'add'){
            $alias_format[$action]['is_write_index'] = True;
        }
        return $alias_format;
    }

    protected function compareIndex(string $indexA, string $indexB, float $docs_threshold=0.7){
        
        $indexA_info = $this->es_cat_repo->indices(["index"=> [$indexA]]);
        $indexB_info = $this->es_cat_repo->indices(["index"=> [$indexB]]);

       
        if (empty($indexA_info) || empty($indexB_info)){
            throw new CommonApiException("One of index with name {$indexA}, {$indexB} doesn't exist .");
        }

        $indexA_info = $indexA_info[0];
        $indexB_info = $indexB_info[0];

        if ($indexA_info['docs.count'] >= $indexB_info['docs.count']*$docs_threshold){
            return true;
        }
        return false;
    }
}