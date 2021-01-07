<?php

namespace App\Services\Elasticsearch;

use Illuminate\Support\Facades\Storage;
use App\Repositories\Elasticsearch as EsRepo;
use App\Exceptions\CommonApiException;

Class Indices{

    protected $EsIndicesRepo;
    protected $EsCatRepo;

    public function __construct(){
        $this->EsIndicesRepo = new EsRepo\Indices;
        $this->EsCatRepo  = new EsRepo\Cat; 
    }

    /*
    * Create index, set the newest index alias to "{$index}-latest"
    * @param string $index, string $configPath
    * @return 
    */
    public function create(string $index, int $backupCount=1, float $docsThreshold=0.7){
        
        

        // check $index-latest is setted
        $indexAlias = "{$index}-latest";
        if(count($this->catAliases($indexAlias)) > 0){
            throw new CommonApiException("Index with name {$index}-latest exist.");
        }

        

        $this->deleteIndices($index, $backupCount, $docsThreshold);

        
        
        //add timestamp
        $date = date("YmdHis");
        $indexTimestamp = "{$index}-{$date}";

        $params = [
            'index' => $indexTimestamp,
            'body' => $this->getCreateBody("elasticsearch/{$index}")
        ];

        $response = $this->EsIndicesRepo->create($params);
        
        //set alias to "{$index}-newest"
        $createIndex = $response["index"];
        $actions = [];
        $actions[] = $this->updateAliasFormatter("add", $createIndex, $indexAlias);
        $this->setAliases($createIndex, $indexAlias);
        $response['alias'] = $indexAlias;

        return $response;
        
    }


    public function deleteIndices(string $index, int $backupCount=1, float $docsThreshold=0.7){

        $indices = $this->countIndex($index, $docsThreshold);
        $currentIndices = $this->catAliases($index);
        $deleteIndices = [];

        

        // delete index not in backupCount
        $indicesCount = count($indices);
        for ($i=$backupCount ; $i<$indicesCount ; $i++ ){

            //avoid removing current alias
            if (in_array($indices[$i], $currentIndices)){
                continue;
            }

            $this->delete($indices[$i]);
            $deleteIndices[] = $indices[$i];
        }


        return ["remove" => $deleteIndices];
    }

    /*
    * Set Aliases to index
    * @param string $targetIndex, string alias
    * @return Json
    */
    public function setAliases(string $targetIndex, string $alias){
        
        # indices with name $alias
        $removeIndices = $this->catAliases($alias);
        $response = [];
        $actions = [];
        
        $response['remove'] = $removeIndices;
        $response['add'] = $targetIndex;


        foreach ($removeIndices as $index){
            $actions[] = $this->updateAliasFormatter("remove", $index, $alias);
        }
        $actions[] = $this->updateAliasFormatter("add", $targetIndex, $alias);
        
        $this->updateAliases($actions);
        $response['acknowledge'] = true;

        return $response;
    }

    /*
    * Set Alias to the newest created not empty index
    * @param string $index
    * @return Array[string]
    */
    public function setAliasesLatest(string $index, float $docsThreshold=0.7){
        
        $indices = $this->countIndex($index, $docsThreshold);
        return $this->setAliases($indices[0], $index);
    }

    /*
    * Count index starts with $index, sort according to content
    * @param string $index
    * @return Array[string]
    */
    protected function countIndex(string $index, float $docsThreshold=0.7){
        
        $indicesInfo = $this->EsCatRepo->indices([]);

        //filter index with "{$index}-{$timestamp}"
        $newIndicesInfo = [];
        $pattern = "/{$index}\-\d{14}/";
        foreach ($indicesInfo as $indexInfo){
            if (preg_match($pattern, $indexInfo['index'],$matches)){
                $newIndicesInfo[] = $indexInfo;
            }
        }
        $indicesInfo = $newIndicesInfo;

        $docsCount =[];
        $indexNames = [];
        foreach($indicesInfo as $indexInfo){
            $docsCount[] =  $indexInfo['docs.count'];
            $indexNames[] = $indexInfo['index'];
        }

        $maxDocsCount = max($docsCount);
        if ($maxDocsCount > 0){
            foreach($docsCount as $key => $count){
                if ($count/$maxDocsCount >=  $docsThreshold){
                    $docsCount[$key] = 1;
                }
                else{
                    $docsCount[$key] = 0;
                }
            }
        }

        array_multisort($docsCount, SORT_NUMERIC, SORT_DESC,
            $indexNames, SORT_STRING, SORT_DESC,
            $indicesInfo);

        $indices = [];
        foreach ($indicesInfo as $indexInfo){
            $indices[] = $indexInfo['index'];
        }

        return $indices;
    }

    /*
    * get elasticsearch mappings and settings
    * @param string $configPath
    * @return string (raw Json)
    */
    protected function getCreateBody(string $configPath){
    
        $mappingsJson = Storage::disk('local')->get("{$configPath}/mappings.json");
        $settingsJson = Storage::disk('local')->get("{$configPath}/settings.json");

        return "{
            \"mappings\" : {$mappingsJson},
            \"settings\" : {$settingsJson}
        }";
    }

    /*
    * return the index point to alias
    * @param string $name
    * @return array(string)
    */
    protected function catAliases(string $name){
        
        $params = [
            'name' => $name
        ];

        $aliases_result = $this->EsCatRepo->aliases($params);
        $indices = [];

        foreach($aliases_result as $alias_result){
            $indices[] = $alias_result["index"];
        }

        return $indices;
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

        return $this->EsIndicesRepo->updateAliases($params);
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

        return $this->EsIndicesRepo->delete($params);
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

        return $this->EsIndicesRepo->refresh($params);
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

        return $this->EsIndicesRepo->putSettings($params);
    }

    protected function updateAliasFormatter($action, $index, $alias){

        $Aliasformat = [
            $action => [
                'index' => $index,
                'alias' => $alias
            ]
        ];

        if ($action === 'add'){
            $Aliasformat[$action]['is_write_index'] = True;
        }
        return $Aliasformat;
    }
}