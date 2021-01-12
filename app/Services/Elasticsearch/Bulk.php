<?php

namespace App\Services\Elasticsearch;

use App\Exceptions\CommonApiException;

use App\Formatters\Response\Common\BulkBulkResponse;
use App\Formatters\Response\Common\BulkStartResponse;
use App\Formatters\Response\Common\BulkEndResponse;

Class Bulk extends Indices{


    public function bulk($req_params){

        $latest_alias = "{$req_params['index']}-latest";
        // check {$index}-latest is setted
        if (empty($this->catAlias($latest_alias))){
            throw new CommonApiException("Alias with name {$latest_alias} doesn't exist.");
        }

        $formatter = app("Formatters/Document/{$req_params['index']}");
        $data = [];

        $valid_actions = [];
        foreach($req_params['actions'] as $action){

            $action = $formatter->preprocess($action);
            $valid_action = $formatter->process($action, $req_params['validate_range']);
            if (empty($valid_action)){
                $data["invalid"][] = $action['_id'];
            }
            else{
                $valid_actions[] = $valid_action;
            }
        }
        
        $params = [];
        
        foreach ($valid_actions as $action){
            
            $query = [
                $req_params['action_type'] => [
                    '_index' => $latest_alias,
                    '_type' => '_doc'
                ]
            ];

            if (isset($action['_id'])){
                $query[$req_params['action_type']]['_id'] = $action['_id'];
                unset($action['_id']);
            }

            $params['body'][] = $query;
            if ($req_params['action_type'] === "index"){
                $params['body'][] = $action;
            }
            else if($req_params['action_type'] === "update"){
                $params['body'][] = [
                    "doc" => $action
                ];
            }
        }

        $es_response = [];
        if (!empty($valid_actions)){
            $es_response = $this->es_indices_repo->bulk($params);
        }
        
        $response_formatter = new BulkBulkResponse($es_response, $data);
        return $response_formatter;
    }

    public function start($req_params){
        
        $latest_alias = "{$req_params['index']}-latest";
        //check {$index}-latest doesn't exist
        if(!empty($this->catAlias($latest_alias))){
            throw new CommonApiException("Alias with name {$latest_alias} exist.");
        }

        //use main as target index
        if (empty($req_params['target_index'])){
            $req_params['target_index'] = $this->catAlias($req_params['index']);
            if (empty($req_params['target_index'])){
                throw new CommonApiException("Alias with name {$req_params['index']} doesn't exist.");
            }
        }
        $actions = [];
        $actions[] = $this->updateAliasFormatter("add", $req_params['target_index'], $latest_alias);
        $es_response = $this->updateAliases($actions);

        // update time interval
        $this->setInterval($req_params['target_index'], "-1");

        $response_formatter = new BulkEndResponse($es_response, ["index"=>$req_params['target_index']]);
        return $response_formatter;
    }

    public function end($req_params){

        $latest_alias = "{$req_params['index']}-latest";
        //check {$index}-latest exist
        $latest_index = $this->catAlias($latest_alias);
        if(empty($latest_index)){
            throw new CommonApiException("Index with name {$latest_alias} doesn't exist.");
        }

        // fresh
        $this->refresh($latest_index);

        // update time interval
        $this->setInterval($latest_index, "10s");

        // remove index latest
        $actions = [];
        $actions[] = $this->updateAliasFormatter("remove", $latest_index, $latest_alias);
        $es_response = $this->updateAliases($actions);

        $response_formatter = new BulkStartResponse($es_response, ["index"=>$latest_index]);
        return $response_formatter;
    }
    
}