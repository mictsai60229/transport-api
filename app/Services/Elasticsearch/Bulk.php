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
    public function bulk(string $index, string $action_type, array $actions, string $validate_range){

        $index_latest = "{$index}-latest";
        // check {$index}-latest is setted
        if (empty($this->catAlias($index_latest))){
            throw new CommonApiException("Index with name {$index}-latest doesn't exist.");
        }

        $formatter = app("Formatter/{$index}");

        $validated_actions = [];
        $failure_actions = [];
        foreach($actions as $action){

            $validated_action = $formatter->validate($action, $validate_range);
            if (empty($validated_action)){
                $failure_actions[] = $action;
            }
            else{
                $validated_actions[] = $validated_action;
            }
        }

        
        if (empty($validated_actions)){
            $response = [];
            $response['failure'] = $failureActions;

            return $response;
        }
        $response['failure'] = $failure_actions;
        
        $params = [];
        
        foreach ($validated_actions as $action){
            
            $query = [
                $action_type => [
                    '_index' => $index_latest,
                    '_type' => '_doc'
                ]
            ];

            if (isset($action['_id'])){
                $query[$action_type]['_id'] = $action['_id'];
                unset($action['_id']);
            }

            $params['body'][] = $query;

            
            if ($action_type === "index"){
                $params['body'][] = $action;
            }
            else if($action_type === "update"){
                $params['body'][] = [
                    "doc" => $action
                ];
            }
            
        }

        return $this->es_indices_repo->bulk($params);
    }

    public function start(string $index, string $target_index=null, float $docs_threshold=0.7){
        
        $index_alias = "{$index}-latest";
        //check {$index}-latest doesn't exist
        if(!empty($this->catAlias($index_alias))){
            throw new CommonApiException("Index with name {$index}-latest exist.");
        }
        // set index latest
        $latest_index = $target_index;
        if (empty($target_index)){
            $indices = $this->count_index($index, $docs_threshold);
            $latest_index = $indices[0];
        }
        $actions = [];
        $actions[] = $this->updateAliasFormatter("add", $latest_index, $index_alias);
        $this->updateAliases($actions);

        // update time interval
        $this->setInterval($index_alias, "-1");

        return ["index"=>$latest_index, "alias"=>$index_alias];
    }

    public function end(string $index){

        $index_alias = "{$index}-latest";
        //check {$index}-latest exist
        $latest_index = $this->catAlias($index_alias);
        if(empty($latest_index)){
            throw new CommonApiException("Index with name {$index}-latest doesn't exist.");
        }

        // fresh
        $this->refresh($index_alias);

        // update time interval
        $this->setInterval($index_alias, "10s");

        // remove index latest
        $actions = [];
        $actions[] = $this->updateAliasFormatter("remove", $latest_index, $index_alias);
        $this->updateAliases($actions);

        return ["remove"=>["index"=>$latest_index, "alias"=>$index_alias]];
    }

    public function setHotSpot(array $location_codes, string $location_type, string $locale){


        return ["scuesss" => [], "failure" => []];

    }
    
}