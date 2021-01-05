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
    
}