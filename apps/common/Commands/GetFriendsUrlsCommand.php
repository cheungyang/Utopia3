<?php
namespace Utopia\Projects\Common\Commands;

use Utopia\Components\DataParser\DataParser;
use Utopia\Components\Logger\Logger;
use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\Helper\HelperFactory;
use Utopia\Components\Console\BaseCommand;
use Utopia\Components\ClassDispatcher\ClassDispatcher;

class GetFriendsUrlsCommand extends BaseCommand
{
    protected $name = 'frds.urls.get';
    protected $command_details = <<<EOF
aliases: []
description: 'Gets urls from website'
help: >
  Execute controllers in command line
  usage: ./run <comment>frds.urls.get</comment>
spec: ~
EOF;

    public function execute($input, $output) {
        $files = array();
        $fp = fopen('friends_files.csv', 'w');
        fwrite($fp, "season,episode,file\n");

        for($season=1; $season<=10; $season++){
            $html = true;
            $episode = 1;
            while(false != $html) {
                Logger::summon()->log("loading season-{$season}/episode-{$episode}", ComponentRoot::LEVEL_DEBUG);
                $url = "http://vids.tv/friends/tv/season-{$season}/episode-{$episode}";
                $html = HelperFactory::summon()->util_curl($url);
                if ($html) {
                    preg_match_all('/"http:\/\/loombo.com\/(.*)"/', $html, $matches);
                    foreach($matches[0] as $match) {
                        $html = HelperFactory::summon()->util_curl(trim($match, "\""));
                        //need some trick here to get the url i want
//                        preg_match('/name="id"\s*value="(.*)"/', $html, $id);
//                        $html = HelperFactory::summon()->util_curl("http://loombo.com/{$id[1]}");
                        preg_match("/'file'\s*,\s*'(.*)'/", $html, $file);
                        print_r($file);
                        echo $html; die();
                        $files[$season][$episode] = $file[1];
                        fwrite($fp, "{$season},{$episode},{$file[1]}\n");
                    }
                    $episode++;
                } else {
                    Logger::summon()->log("loading season-{$season}/episode-{$episode} failed, proceed to next season", ComponentRoot::LEVEL_DEBUG);
                }
            }
        }

        fclose($fp);
        print_r($files);
    }
}