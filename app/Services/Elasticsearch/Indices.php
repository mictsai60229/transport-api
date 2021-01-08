<?php

namespace App\Services\Elasticsearch;

use Illuminate\Support\Facades\Storage;
use App\Repositories\Elasticsearch as EsRepo;
use App\Exceptions\CommonApiException;

Class Indices{

    protected $es_indices_repo;
    protected $es_cat_repo;

    public function __construct(){
        $this->es_indices_repo = new EsRepo\Indices;
        $this->es_cat_repo  = new EsRepo\Cat; 
    }

    /*
    * Create index, set the newest index alias to "{$index}-latest"
    * @param string $index, string $configPath
    * @return 
    */
    public function create(string $index, int $backup_count=0, float $docs_threshold=0.7){

        // check $index-latest is setted
        $index_alias = "{$index}-latest";
        if(!empty($this->catAlias($index_alias))){
            throw new CommonApiException("Index with name {$index}-latest exist.");
        }

        $this->deleteIndices($index, $backup_count, $docs_threshold);

        //add timestamp
        $date = date("YmdHis");
        $index_timestamp = "{$index}-{$date}";

        $params = [
            'index' => $index_timestamp,
            'body' => $this->getCreateBody("elasticsearch/{$index}")
        ];

        $response = $this->es_indices_repo->create($params);
        
        //set alias to "{$index}-newest"
        $create_index = $response["index"];
        $actions = [];
        $actions[] = $this->updateAliasFormatter("add", $create_index, $index_alias);
        $this->updateAliases($actions);
        $response['alias'] = $index_alias;

        return $response;
        
    }


    public function deleteIndices(string $index, int $backup_count=0, float $docs_threshold=0.7){

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


        return ["remove" => $delete_indices];
    }


    /*
    * Set Alias to the newest created not empty index
    * @param string $index
    * @return Array[string]
    */
    public function setAliases(string $index, string $target_index = null, float $docs_threshold=0.7){
        
        $aliases_actions = [];
        $response = [];

        if (!isset($target_index)){
            $indices = $this->countIndex($index, $docs_threshold);
            $target_index = $indices[0];
        }

        $index_alias = "{$index}-latest";
        $latest_index = $this->catAlias($index_alias);
        if ($latest_index === $target_index){
            $this->refresh($target_index);
            $this->setInterval($index_alias, "10s");
            $aliases_actions[] = $this->updateAliasFormatter("remove", $target_index, $index_alias);
        }
        
        $remove_index = $this->catAlias($index);

        if (!empty($remove_index)){
            $aliases_actions[] = $this->updateAliasFormatter("remove", $remove_index, $index);
            $response['remove'] = $remove_index;
        }
        
        $aliases_actions[] = $this->updateAliasFormatter("add", $target_index, $index);
        $response['add'] = $target_index;
        
        $this->updateAliases($aliases_actions);

        return $response;
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

    protected function updateAliasFormatter($action, $index, $alias){

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
}