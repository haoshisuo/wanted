<?php
use Elasticsearch\ClientBuilder;

require 'vendor/autoload.php';

$hosts = [
    '127.0.0.1:9200',         // IP + 端口
];

$client = ClientBuilder::create()->setHosts($hosts)->setRetries(0)->build();

echo '<pre>';
// 索引一个文档
$params_index = [
    'index' => 'my_index',
    'type' => 'my_type',
    'id' => 'my_id',
    'body' => [
    	'testField' => 'abc',
    ]
];
/*$response_index = $client->index($params_index);
print_r($response_index);*/

// 获取一个文档
$params_get = [
    'index' => 'my_index',
    'type' => 'my_type',
    'id' => 'my_id',
    'client' => [
    	'ignore' => [404],			// 忽略异常
    	// 'verbose' => true,		// 返回详细输出
    	'timeout' => 10,			// curl超时设置
        'connect_timeout' => 10,
        // 'future' => 'lazy',		// 使用 Future 异步模式 
        // 'verify' => 'path/to/cacert.pem',	// 使用自签名证书
    ]
];
$response_get = $client->get($params_get);
print_r($response_get);

// 批量索引文档
$params_bulk = [
    'body' => [
    	[
    		'index' => [
	    		'_index' => 'my_index',
			    '_type' => 'my_type',
	    	],
    	],
    	[
    		'testField' => 'abc 777',
	    	'time' => 777,
    	],
    	[
    		'index' => [
	    		'_index' => 'my_index',
			    '_type' => 'my_type',
	    	],
    	],
    	[
    		'testField' => 'abc 666',
	    	'time' => 666,
    	],
    ],
];
/*$response_bulk = $client->bulk($params_bulk);
print_r($response_bulk);*/

$size = 3;
// 搜索一个文档
$params_search = [
    'index' => 'my_index',
    'type' => 'my_type',
    'body' => [
        'query' => [
            'match' => [
                'testField' => 'abc'
            ]
        ],
        // 空对象
        'highlight' => array(
	        'fields' => array(
	            'content' => new \stdClass(),
	        )
	    ),
	    // 对象数组
        /*'sort' => [
        	['id' => ['order' => 'desc']],
        ],*/
    ],
    // 游标查询
    'size' => $size,
    'scroll' => '30s',
];
$response_search = $client->search($params_search);
print_r($response_search);

while (isset($response_search['hits']['hits']) && count($response_search['hits']['hits']) == $size) {

    // 完成后，获取新的 scroll_id
    // 刷新下你的 _scroll_id 
    $scroll_id = $response_search['_scroll_id'];

    // 执行下一个游标请求
    $response_search = $client->scroll([
            "scroll_id" => $scroll_id,  // 使用上个请求获取到的  _scroll_id
            "scroll" => "30s"           // 时间窗口保持一致
        ]
    );
    print_r($response_search);
}

// 删除一个文档
$params_delete = [
    'index' => 'my_index',
    'type' => 'my_type',
    'id' => 'my_id',
];
/*$response_delete = $client->delete($params_delete);
print_r($response_delete);*/

// 删除索引
$deleteParams = [
    'index' => 'my_index'
];
/*$response_d = $client->indices()->delete($deleteParams);
print_r($response_d);*/

// 创建索引
$createParams = [
    'index' => 'my_index'
];
/*$response_c = $client->indices()->create($createParams);
print_r($response_c);*/

// putSettings getSettings putMapping getMapping