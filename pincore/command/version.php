<?php

namespace pinoox\command;


use pinoox\app\com_pinoox_manager\component\Notification;
use pinoox\component\store\Config;
use pinoox\component\Console;
use pinoox\component\helpers\HelperString;
use pinoox\component\HttpRequest;
use pinoox\component\interfaces\CommandInterface;
use pinoox\component\Lang;
use pinoox\component\Request;
use pinoox\component\Url;
use Symfony\Component\HttpClient\HttpClient;


class version extends Console implements CommandInterface
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "version";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Check version of pinoox.";

    /**
     * The console command Arguments.
     *
     * @var array
     */
    protected $arguments = [
        //[ name , is_required , description , default ],
    ];

    /**
     * The console command Options.
     *
     * @var array
     */
    protected $options = [
        //[ name , short_name , description , default ],
    ];

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        self::handleing();
    }

    public static function handleing()
    {
        $data = self::getVersions();
        if ($data['isNewVersion']) {
            self::info(sprintf("Your pinoox version: %s", $data['client']['version_name']));
            self::newLine();
            self::info(sprintf("Last version release: %s", $data['server']['version_name']));
            self::error('You need to update pinoox!');
        } else {
            self::success(sprintf("You use the last version of pinoox. (%s)", $data['client']['version_name']));
            self::newLine();
            self::newLine();
        }
    }

    private static function getVersions()
    {
        $server_version = self::getServerVersion() ?? [];
        $server_version = [
            'version_code' => @$server_version['version_code'],
            'version_name' => @$server_version['version_name'],
        ];
        $client_version = Config::init('~pinoox')->get();
        $client_version = [
            'version_code' => $client_version['version_code'],
            'version_name' => $client_version['version_name'],
        ];
        $server_version_code = (isset($server_version['version_code'])) ? $server_version['version_code'] : 0;
        $isNewVersion = ($server_version_code > $client_version['version_code']);

        // if ($isNewVersion)
        //    self::notificationCheckVersion($server_version);

        return ['server' => $server_version, 'client' => $client_version, 'isNewVersion' => $isNewVersion];
    }

    private static function notificationCheckVersion($version)
    {
        Lang::app('com_pinoox_manager');
        $title = Lang::get('notification.release_new_version.title');
        $message = Lang::replace('notification.release_new_version.message', ['version' => $version['version_name']]);

        Notification::action('release_new_version_' . $version['version_code'], $version);
        Notification::push($title, $message, 0, true);
    }

    private static function getServerVersion()
    {
        $pinoox = Config::init('~pinoox')->get();
        $response = Request::sendPost(
            'https://www.pinoox.com/api/v1/update/checkVersion/',
            [
                'version_name' => $pinoox['version_name'],
                'version_code' => $pinoox['version_code'],
                'php' => phpversion()
            ]);

        return $response?->getStatusCode() == 200 ? $response->toArray() : [];
    }
}