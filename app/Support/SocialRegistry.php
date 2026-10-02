<?php

namespace App\Support;

class SocialRegistry
{
    public const PLATFORMS = [
        'instagram',
        'facebook',
        'linkedin',
        'twitter',
        'tiktok',
        'youtube',
        'pinterest',
        'snapchat',
        'whatsapp',
        'telegram',
        'reddit',
        'discord',
        'tumblr',
        'spotify',
        'twitch',
        'threads',
        'behance',
        'dribbble',
        'medium',
        'vimeo',
        'sharechat',
        'moj',
        'koo',
        'quora',
        'github',
        'weibo',
        'signal',
        'clubhouse',
    ];

    public const TASK_PLATFORMS = [
        'instagram',
        'facebook',
        'linkedin',
        'twitter',
        'tiktok',
        'youtube',
        'pinterest',
        'snapchat',
        'whatsapp',
        'telegram',
        'reddit',
        'discord',
        'tumblr',
        'spotify',
        'twitch',
        'threads',
        'behance',
        'dribbble',
        'medium',
        'vimeo',
        'sharechat',
        'moj',
        'koo',
        'quora',
        'github',
        'weibo',
        'signal',
        'clubhouse',
        'website',
        'other',
    ];

    public const CONTENT_TYPES = [
        'post',
        'reel',
        'story',
        'carousel',
        'video',
    ];

    public const IMAGE_PROOF_PLATFORMS = [
        'whatsapp',
        'telegram',
        'signal',
        'discord',
    ];

    public static function platformValidationList(): string
    {
        return implode(',', self::PLATFORMS);
    }

    public static function contentTypeValidationList(): string
    {
        return implode(',', self::CONTENT_TYPES);
    }
}
