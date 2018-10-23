<?php
/**
 * Class SessionCache
 *
 * @filesource   SessionCache.php
 * @created      27.05.2017
 * @package      chillerlan\SimpleCache
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\SimpleCache;

use chillerlan\Settings\SettingsContainerInterface;

class SessionCache extends CacheDriverAbstract{

	/**
	 * @var string
	 */
	protected $key;

	/**
	 * SessionCache constructor.
	 *
	 * @param \chillerlan\Settings\SettingsContainerInterface|null $options
	 *
	 * @throws \chillerlan\SimpleCache\CacheException
	 */
	public function __construct(SettingsContainerInterface $options = null){
		parent::__construct($options);

		$this->key = $this->options->cacheSessionkey;

		if(!is_string($this->key) || empty($this->key)){
			$msg = 'invalid session cache key';

			$this->logger->error($msg);
			throw new CacheException($msg);
		}


		$_SESSION[$this->key] = [];
	}

	/** @inheritdoc */
	public function get($key, $default = null){
		$this->checkKey($key);

		if(isset($_SESSION[$this->key][$key])){

			if($_SESSION[$this->key][$key]['ttl'] === null || $_SESSION[$this->key][$key]['ttl'] > time()){
				return $_SESSION[$this->key][$key]['content'];
			}

			unset($_SESSION[$this->key][$key]);
		}

		return $default;
	}

	/** @inheritdoc */
	public function set($key, $value, $ttl = null):bool{
		$this->checkKey($key);
		$ttl = $this->getTTL($ttl);

		$_SESSION[$this->key][$key] = [
			'ttl' => $ttl ? time() + $ttl : null,
			'content' => $value,
		];

		return true;
	}

	/** @inheritdoc */
	public function delete($key):bool{
		$this->checkKey($key);

		unset($_SESSION[$this->key][$key]);

		return true;
	}

	/** @inheritdoc */
	public function clear():bool{
		$_SESSION[$this->key] = [];

		return true;
	}

}