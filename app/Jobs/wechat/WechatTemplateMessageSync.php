<?php
/**
 * Created by PhpStorm.
 * User: katherine
 * Date: 19-4-4
 * Time: 上午10:56
 */

namespace App\Jobs\wechat;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;

class WechatTemplateMessageSync extends Job
{
    use SerializesModels;

    protected $platform;
    protected $wxAppId;

    public function __construct($platform, $wxAppId)
    {
        $this->platform = $platform;
        $this->wxAppId = $wxAppId;
    }

    protected function parseTemplateContent(array &$template)
    {
        $keywords = [];
        $contentArray = explode("\n", $template['content']);
        foreach ($contentArray as $content) {
            $content = explode("{{", $content);
            if ($content[0]) {
                $keywords[] = $content[0];
            }
        }
        $template['keywords'] = $keywords;
    }
}