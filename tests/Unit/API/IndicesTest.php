<?php

namespace Tests\Unit\API;

use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use App\Repositories\Elasticsearch\Indices as EsIndicesRepo;

class IndicesTest extends TestCase
{
    private static $index;
    private static $es_indices_repo;
    public static function setUpBeforeClass(): void{
        parent::setUpBeforeClass();
        self::$index = "transport";
        self::$es_indices_repo = new EsIndicesRepo();
    }

    /**
     * A basic test example.
     *
     * @return string
     */
    public function testIndicesCreateTest(): string{

        $index = self::$index;
        $response = $this->postJson("/v1/{$index}/indices/create", []);

        $response
            ->assertStatus(200)
            ->assertJson([
                "metadata" => [
                    "status" => "0000",
                    "desc" => "Success"
                ]
            ])
            ->assertJsonStructure([
                "data" => [
                    "index"
                ]
            ]);
        
        $content = $response->decodeResponseJson();

        return $content["data"]["index"];
    }

    /**
     * 
     *
     */
    public function testBulkBulkTest(): void{
        
        $index = self::$index;

        $action_type = "index";
        $body = [];
        foreach (config('elasticsearch.transport.location_type') as $location_type){
            $data = Storage::disk('local')->get("examples/{$index}/{$location_type}.json");
            $data = json_decode($data);

            $body[] = $data;
        }

        $response = $this->postJson("/v1/{$index}/bulk/bulk", 
            [
                "action_type" => $action_type,
                "body" => $body
            ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                "metadata" => [
                    "status" => "0000",
                    "desc" => "Success"
                ],
                "data" => [
                    "invalid" => [],
                    "fail" => []
                ]
            ]);
    }

    /**
     * A basic test example.
     *
     * @return void
     * @depends testIndicesCreateTest
     */
    public function testIndicesChangeTest(string $create_index): void{
        
        $index = self::$index;
        $response = $this->postJson("/v1/{$index}/indices/change", ["force"=>true]);

        $response
            ->assertStatus(200)
            ->assertJson([
                "metadata" => [
                    "status" => "0000",
                    "desc" => "Success"
                ],
                "data" => [
                    "index" => $create_index
                ]
            ]);
    }

    /**
     * A basic test example.
     *
     * @return void
     * @depends testIndicesCreateTest
     */
    public function testIndicesDeleteTest(string $create_index): void{

        $es_indices_repo = self::$es_indices_repo;

        $params = [
            "index" => $create_index
        ];
        $response = $es_indices_repo->delete($params);
        $this->assertArrayHasKey("acknowledged", $response);
        $this->assertEquals($response["acknowledged"], true);

    }
}
