<?php

namespace App\Services\Elasticsearch;

use App\Exceptions\CommonApiException;

Class Bulk extends Indices{

    /**
     * Undocumented function
     *
     * @param string $index
     * @param string $actionType
     * @param array $actions
     * @return void
     */
    public function bulk(string $index, string $actionType, array $actions, string $validateRange){

        $indexLatest = "{$index}-latest";
        // check {$index}-latest is setted
        if (count($this->catAliases($indexLatest)) == 0){
            throw new CommonApiException("Index with name {$index}-latest doesn't exist.");
        }

        $formatter = app("Formatter/{$index}");

        $validatedActions = [];
        $failureActions = [];
        foreach($actions as $action){

            $validatedAction = $formatter->validate($action, $validateRange);
            if (empty($validatedAction)){
                $failureActions[] = $action;
            }
            else{
                $validatedActions[] = $validatedAction;
            }
        }

        
        if (empty($validatedActions)){
            $response = [];
            $response['failure'] = $failureActions;

            return $response;
        }
        $response['failure'] = $failureActions;
        
        $params = ['body' => []];
        
        foreach ($validatedActions as $action){
            $params['body'][] = [
                $actionType => [
                    '_index' => $indexLatest,
                    '_id' => $action['_id'],
                    '_type' => '_doc'
                ]
            ];

            unset($action['_id']);
            if ($actionType === "index"){
                $params['body'][] = $action;
            }
            else if($actionType === "update"){
                $params['body'][] = [
                    "doc" => $action
                ];
            }
            
        }

        return $this->EsIndicesRepo->bulk($params);
    }

    public function start(string $index, float $docsThreshold=0.7){
        
        $indexAlias = "{$index}-latest";
        //check {$index}-latest doesn't exist
        if(count($this->catAliases($indexAlias)) > 0){
            throw new CommonApiException("Index with name {$index}-latest exist.");
        }
        // set index latest
        $indices = $this->countIndex($index, $docsThreshold);
        $latestIndex = $indices[0];
        $actions = [];
        $actions[] = $this->updateAliasformatter("add", $latestIndex, $indexAlias);
        $this->updateAliases($actions);

        // update time interval
        $this->setInterval($indexAlias, "-1");

        return ["index"=>$latestIndex, "alias"=>$indexAlias];
    }

    public function end(string $index){

        $indexAlias = "{$index}-latest";
        //check {$index}-latest exist
        $names = $this->catAliases($indexAlias);
        if(count($names) == 0){
            throw new CommonApiException("Index with name {$index}-latest doesn't exist.");
        }

        // fresh
        $this->refresh($indexAlias);

        // update time interval
        $this->setInterval($indexAlias, "10s");

        // remove index latest
        $latestIndex = $names[0];
        $actions = [];
        $actions[] = $this->updateAliasformatter("remove", $latestIndex, $indexAlias);
        $this->updateAliases($actions);

        return ["remove"=>["index"=>$latestIndex, "alias"=>$indexAlias]];
    }

    public function setHotSpot(array $location_codes, string $location_type, string $locale){


        return ["scuesss" => [], "failure" => []];

    }
    
}