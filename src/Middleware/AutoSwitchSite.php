<?php

namespace ThemisMin\AutoSwitchSite\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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

        // lock_site:站点锁; 1 记住锁, 不根据ip切换; 0 清除切换锁, 根据ip切换; null 根据ip切换;
        $lock_site = null;

        if (is_null($lock_site)) {
            $lock_site = $request->get($cookie_name, null);
        }
        if (is_null($lock_site)) {
            $lock_site = Cookie::get($cookie_name, null);
        }

        // 网站全路径
        $fullUrl = $request->fullUrl();
        //
        $parseUrl = parse_url($fullUrl);
        // 路径
        $path = Arr::get($parseUrl, 'path', '/');

        // 根据ip切换
        if (
            '/' == $path // 是首页
            && 1 != $lock_site // 站点锁未打开
            && !$this->_checkrobot() // 不是机器人
            && !$this->_isCrawler() // 不是爬虫
        ) {
            $ip = $request->ip();          // 当前访问的IP地址

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

    /**
     * 是否机器人爬虫
     * @param string $useragent
     * @return bool
     */
    protected function _checkrobot($useragent = '')
    {
        static $kw_spiders = array('bot', 'crawl', 'spider', 'slurp', 'sohu-search', 'lycos', 'robozilla');
        static $kw_browsers = array('msie', 'netscape', 'opera', 'konqueror', 'mozilla');
        $useragent = strtolower(empty($useragent) ? $_SERVER['HTTP_USER_AGENT'] : $useragent);
        if (strpos($useragent, 'http://') === false && $this->_dstrpos($useragent, $kw_browsers))
            return false;
        if ($this->_dstrpos($useragent, $kw_spiders))
            return true;
        return false;
    }

    protected function _dstrpos($string, $arr, $returnvalue = false)
    {
        if (empty($string))
            return false;
        foreach ((array)$arr as $v) {
            if (strpos($string, $v) !== false) {
                $return = $returnvalue ? $v : true;
                return $return;
            }
        }
        return false;
    }

    /**
     * 是否爬虫
     * @return bool|void
     */
    protected function _isCrawler()
    {
        if (!empty($agent)) {
            $spiderSite = array(
                "TencentTraveler",
                "Baiduspider+",
                "BaiduGame",
                "Googlebot",
                "msnbot",
                "Sosospider+",
                "Sogou web spider",
                "ia_archiver",
                "Yahoo! Slurp",
                "YoudaoBot",
                "Yahoo Slurp",
                "MSNBot",
                "Java (Often spam bot)",
                "BaiDuSpider",
                "Voila",
                "Yandex bot",
                "BSpider",
                "twiceler",
                "Sogou Spider",
                "Speedy Spider",
                "Google AdSense",
                "Heritrix",
                "Python-urllib",
                "Alexa (IA Archiver)",
                "Ask",
                "Exabot",
                "Custo",
                "OutfoxBot/YodaoBot",
                "yacy",
                "SurveyBot",
                "legs",
                "lwp-trivial",
                "Nutch",
                "StackRambler",
                "The web archive (IA Archiver)",
                "Perl tool",
                "MJ12bot",
                "Netcraft",
                "MSIECrawler",
                "WGet tools",
                "larbin",
                "Fish search",
                //其它蜘蛛,
            );
            foreach ($spiderSite as $val) {
                $str = strtolower($val);
                if (strpos($agent, $str) !== false) {
                    return true;
                }
            }
        }
        return false;
    }
}
