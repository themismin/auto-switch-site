<?php
/**
 * 站点切换配置
 */
return [
    // cookie_名称, 是否锁定站点, 1 记住锁, 不根据ip切换; 0 清除切换锁, 根据ip切换; null 根据ip切换;
    'cookie_name'  => 'lock_site',

    // 站点
    'site_domains' => [
        'zh_TW' => [
            'address' => ['台湾'],                                             // IP地址匹配
            'domain'  => env('ZH_TW_DOMAIN', 'https://www.lanshauk.com.tw'), // 跳转域名
            'domains' => [ // 域名匹配
                env('ZH_TW_DOMAIN', 'https://www.lanshauk.com.tw'),
                'https://www.dev.lanshauk.com.tw',
                'https://www.beta.lanshauk.com.tw',
                'https://www.lanshauk.com.tw',
            ],
        ],
        'zh_HK' => [
            'address' => ['香港'],
            'domain'  => env('ZH_HK_DOMAIN', 'https://www.lanshauk.com.hk'),
            'domains' => [
                env('ZH_HK_DOMAIN', 'https://www.lanshauk.com.hk'),
                'https://www.dev.lanshauk.com.hk',
                'https://www.beta.lanshauk.com.hk',
                'https://www.lanshauk.com.hk',
            ],
        ],
        'zh_CN' => [
            'address' => ['中国'],
            'domain'  => env('ZH_CN_DOMAIN', 'https://www.lanshauk.com'),
            'domains' => [
                env('ZH_CN_DOMAIN', 'https://www.lanshauk.com'),
                'https://www.dev.lanshauk.com',
                'https://www.beta.lanshauk.com',
                'https://www.lanshauk.com',
            ],
        ],
        'en'    => [
            'address' => [], // 空为其它不匹配则默认切换到该地址, 放在最后
            'domain'  => env('EN_DOMAIN', 'https://en.lanshauk.com'),
            'domains' => [
                env('EN_DOMAIN', 'https://en.lanshauk.com'),
                'https://en.dev.lanshauk.com',
                'https://en.beta.lanshauk.com',
                'https://en.lanshauk.com',
            ],
        ],
    ],
];
