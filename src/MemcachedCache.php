<?php
/**
 * Class MemcachedCache
 *
 * @filesource   MemcachedCache.php
 * @created      25.05.2017
 * @package      chillerlan\SimpleCache
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\SimpleCache;

use chillerlan\Settings\SettingsContainerInterface;
use Memcached;

class MemcachedCache extends CacheDriverAbstract{

	/**
	 * @var \Memcached
	 */
	protected $memcached;

	/**
	 * MemcachedCache constructor.
	 *
	 * @param \Memcached                                           $memcached
	 * @param \chillerlan\Settings\SettingsContainerInterface|null $options
	 *
	 * @throws \chillerlan\SimpleCache\CacheException
	 */
	public function __construct(Memcached $memcached, SettingsContainerInterface $options = null){
		parent::__construct($options);

		$this->memcached = $memcached;

		if(empty($this->memcached->getServerList())){
			$msg = 'no memcache server available';

			$this->logger->error($msg);
			throw new CacheException($msg);
		}

	}

	/** @inheritdoc */
	public function get($key, $default = null){
		$this->checkKey($key);

		$value = $this->memcached->get($key);

		return $value ?: $default;
	}

	/** @inheritdoc */
	public function set($key, $value, $ttl = null):bool{
		$this->checkKey($key);

		return $this->memcached->set($key, $value, $this->getTTL($ttl));
	}

	/** @inheritdoc */
	public function delete($key):bool{
		$this->checkKey($key);

		return $this->memcached->delete($key);
	}

	/** @inheritdoc */
	public function clear():bool{
		return $this->memcached->flush();
	}

	/** @inheritdoc */
	public function getMultiple($keys, $default = null):array{
		$keys = $this->getData($keys);

		$this->checkKeyArray($keys);

		$values = $this->memcached->getMulti($keys);
		$return = [];

		foreach($keys as $key){
			$return[$key] = $values[$key] ?? $default;
		}

		return $return;
	}

	/** @inheritdoc */
	public function setMultiple($values, $ttl = null):bool{
		$values = $this->getData($values);

		$this->checkKeyArray(array_keys($values));

		return $this->memcached->setMulti($values, $this->getTTL($ttl));
	}

	/** @inheritdoc */
	public function deleteMultiple($keys):bool{
		$keys = $this->getData($keys);

		$this->checkKeyArray($keys);

		return $this->checkReturn((array)$this->memcached->deleteMulti($keys));
	}

}