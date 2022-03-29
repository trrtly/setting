<?php

/*
 * This file is part of ibrand/setting.
 *
 * (c) iBrand <https://www.ibrand.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace iBrand\Component\Setting\Repositories;

/**
 * Class CacheDecorator
 * @package iBrand\Component\Setting\Repositories
 */
class CacheDecorator implements SettingInterface
{
    /**
     * @var SettingInterface
     */
    private $repo;
    /**
     * @var mixed
     */
    private $cache;

    /**
     * @var string
     */
    private $key;

    /**
     * CacheDecorator constructor.
     * @param SettingInterface $repo
     */
    public function __construct(SettingInterface $repo)
    {
        $this->repo = $repo;
        $this->cache = cache();
        $this->key = md5('ibrand.setting');
    }

    /**
     * @param array $settings
     * @return mixed
     */
    public function setSetting(array $settings)
    {
        $this->cache->forget($this->key);

        $result = $this->repo->setSetting($settings);

        $this->cache->put($this->key, $this->repo->allToArray(), config('ibrand.setting.minute'));

        return $result;

    }


    /**
     * @param $key
     * @param null $default
     * @return mixed|string
     */
    public function getSetting($key, $default = null)
    {
        /** @var ParameterBag $request */
        $request = app('request')->request;
        if (!$data = $request->get('ibrand_system_setting')) {
            $data = $this->allToArray();
            $request->add(['ibrand_system_setting' => $data]);
        }

        $value = $default ?: '';

        return $data[$key] ?? $value;
    }

    /**
     * @return mixed
     */
    public function allToArray()
    {
        $cacheKey = $this->key;

        $data = $this->cache->remember($cacheKey, config('ibrand.setting.minute'), function () {
            return $this->repo->allToArray();
        });

        return $data;
    }
}
