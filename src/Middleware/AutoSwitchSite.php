<?php

namespace ThemisMin\AutoSwitchSite\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use itbdw\Ip\IpLocation;

class AutoSwitchSite
{

    /**
     * @var string[]
     */
    protected $block_ips = array('192.168.', '127.0.0.1');

    /**
     * 根据IP自动切换站点
     * @param \Illuminate\Http\Request $request
     * @param Closure                  $next
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        $config = config('auto_switch_site');
        $cookie_name = $config['cookie_name'];

        // lock_site: 1 记住锁, 不根据ip切换; 0 清除切换锁, 根据ip切换; null 根据ip切换;
        $lock_site = null;

        if (is_null($lock_site)) {
            $lock_site = $request->get($cookie_name, null);
        }
        if (is_null($lock_site)) {
            $lock_site = Cookie::get($cookie_name, null);
        }

        // 根据ip切换
        if (1 != $lock_site) {    // 根据ip切换
            $ip = $request->ip(); // 当前访问的IP地址

            if (!$this->_blockIps($ip)) {                 // 不是屏蔽的IP
                $location = IpLocation::getLocation($ip); // IP解析
                foreach ($config['site_domains'] as $key => $site_domain) {
                    if (in_array($location['country'], $site_domain['address'])
                        || in_array($location['province'], $site_domain['address'])
                        || in_array($location['city'], $site_domain['address'])
                        || empty($site_domain['address']) // 空地址
                    ) {
                        $site = $key;
                        break;
                    }
                }

                if (!in_array($request->getSchemeAndHttpHost(), $config['site_domains'][$site]['domains'])) {
                    return redirect()->away($config['site_domains'][$site]['domain']);
                }
            }
        }

        $response = $next($request);

        // Perform action
        if (1 == $lock_site) { // 记住锁
            $cookie = cookie($cookie_name, $lock_site, 24 * 60);
            return $response->withCookie($cookie);
        } else if (0 == $lock_site) { // 清除切换锁
            $cookie = Cookie::forget($cookie_name);
            return $response->withCookie($cookie);
        }
        return $response;
    }

    /**
     * 是否屏蔽的IP
     * @param $ip
     * @return bool
     */
    protected function _blockIps($ip)
    {
        $result = false;
        foreach ($this->block_ips as $block_ip) {
            if (false !== stripos($ip, $block_ip)) {
                $result = true;
            }
        }
        return $result;
    }
}
