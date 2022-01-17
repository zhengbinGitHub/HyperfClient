执行
php artisan vendor:publish --provider="LaravelHyperfClientRpcService\RpcServiceProvider"

调用
$method = 'calculator/add';
$params = ['a' => 12, 'b' => 10];
$result = app('hyperf-rpc-client')->callRpcMethod($method, $params);