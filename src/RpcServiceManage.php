<?php
/**
 * Created by PhpStorm.
 * User: maczheng
 * Date: 2021-04-16
 * Time: 11:28
 */

namespace RpcService;

use Ramsey\Uuid\Uuid;

class RpcServiceManage extends BaseRpc
{
	/**
	 * @var 请求地址、端口
	 */
	private $host, $port;

	/**
	 * 配置文件
	 * @var
	 */
	private $config;

	/**
	 * @var null
	 */
	private $client = null;

	/**
	 * HyperRpcManage constructor.
	 * @param $config
	 */
	public function __construct($config)
	{
		$this->config = $config;
	}

	/**
	 * 创建rpc客户端
	 */
	private function createClient()
	{
		//初始化端口和ip
		if(empty($this->host) || empty($this->port)){
			$this->host = $this->config['host'];
			$this->port = (int)$this->config['port'];
		}
		//创建新的client并且返回
		$client = new \Swoole\Client(SWOOLE_SOCK_TCP);  //默认创建的client就是同步阻塞的
		try{
			//连接超时时间设置为2秒
			if(!$client->connect($this->host, $this->port, $this->config['timeout'])){
				throw new \Error("connect failed. Error: {$client->errCode}\n");    //正常情况下应该写日志
			}
		}catch (\Exception $e){
			throw new \Error('rpc服务端连接失败:'.$e->getMessage());
		}
		$this->client = $client;
	}

	/**
	 * 为什么不每次连接tcp客户端后都关闭连接呢？
	 * 因为频繁的连接和关闭会消耗服务端的性能，非常多的tcp连接会处于close_wait的状态下面，
	 * 实际上swoole在收到关闭信号的时候会自动关闭掉客户端，
	 * 即使不关闭每条进程一个close_wai也不影响服务端的性能,返回数据为空字符串的情况，1重试次数超过3次，2 hyperf rpc服务端关闭
	 * 例如：服务名称：CalculatorService 方法：add ($method: /calculator/add)
	 * @param string $method
	 * @param array $params
	 * @param string $type
	 * @param string $version
	 * @return array|bool
	 * @throws \Exception
	 */
	public function callRpcMethod(string $method, array $params, string $type = 'jsonrpc', string $version = '2.0')
	{
		$data = [
			$type => $version,
			'method' => $method,
			'params' => [$params],
			'id' => Uuid::uuid4()->getHex(),
		];
		if(is_null($this->client)){
			$this->createClient();
		}
		$message = json_encode($data, JSON_UNESCAPED_UNICODE)."\r\n";
		$data = "";
		//当rpc-tcp服务端报错的时候send函数和recv函数都会报错，所以这里需要捕捉异常
		$i = 0;
		while(true){
			try{
				$this->client->send($message);
				$data = $this->client->recv();
			}catch (\Exception $e){
				//在hyperf重启时tcp连接会丢失产生错误，此时需要重新连接
				$this->createClient();
			}
			//数据不为空，或者重试超过3次时退出循环，一般来说就是三次，次数太多会出现问题
			if(!empty($data) || $i>3){
				break;
			}
			$i++;
		}
		//return $data;
		//处理一下结果
		if(!empty($data)){
			$dataResult = (array)json_decode($data,true);
			if(isset($dataResult['result'])){
				$result = (array)json_decode($dataResult['result'],true);
				if($result['code'] == '-1'){
					return $this->error($result['msg'], ['id' => $dataResult['id']]);
				}
				return $this->success('ok', $result['data']);
			}else{
				return $this->error('数据返回格式错误', $dataResult);
			}
		}else{
			return $this->error('数据异常');   //不存在的时候返回false
		}
	}
}