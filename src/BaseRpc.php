<?php
/**
 * Created by PhpStorm.
 * User: maczheng
 * Date: 2021-04-16
 * Time: 14:26
 */

namespace LaravelHyperfClientRpcService;


class BaseRpc
{
	/**
	 * @param $message
	 * @param $data
	 * @return array
	 */
	protected function success($message, $data = [])
	{
		return ['status' => 1, 'message' => $message, 'data' => $data];
	}

	/**
	 * @param $message
	 * @param $data
	 * @return array
	 */
	protected function error($message, $data = [])
	{
		return ['status' => 0, 'message' => $message, 'data' => $data];
	}
}