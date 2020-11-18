<?php

namespace OCA\EmlViewer\AppInfo;

use Exception;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Http\EmptyContentSecurityPolicy;
use OCP\Util;
use \OCA\EmlViewer\Storage\AuthorStorage;

class Application extends App implements IBootstrap{

    const APP_ID = 'emlviewer';

    public function __construct() {
        parent::__construct(self::APP_ID);

        $manager = \OC::$server->getContentSecurityPolicyManager();
        $policy = new EmptyContentSecurityPolicy();

        $policy->addAllowedStyleDomain('\'self\'');
        $policy->addAllowedStyleDomain('*');
        $policy->addAllowedFontDomain('*');
        $policy->addAllowedScriptDomain('\'self\'');

        $policy->addAllowedImageDomain('*');
        $policy->addAllowedImageDomain('data:');
        $policy->addAllowedImageDomain('blob:');
        $policy->addAllowedImageDomain('cid:');

        $policy->addAllowedMediaDomain('\'self\'');
        $policy->addAllowedMediaDomain('blob:');

        $policy->addAllowedChildSrcDomain('\'self\'');
        $policy->addAllowedChildSrcDomain('blob:');

        $policy->addAllowedConnectDomain('\'self\'');

        $manager->addDefaultPolicy($policy);

        /**
         * Storage Layer
         */
        $container = $this->getContainer();
        $container->registerService('AuthorStorage', function($c) {
            return new AuthorStorage($c->get('RootStorage'));
        });

        $container->registerService('RootStorage', function($c) {
            return $c->get('ServerContainer')->getUserFolder();
        });

    }
    public function register(IRegistrationContext $context): void {
        // ... registration logic goes here ...

        if ((@include_once __DIR__ . '/../../vendor/autoload.php')===false) {
            throw new Exception('Cannot include autoload. Did you run install dependencies using composer?');
        }

        $this->registerScripts();
    }

    public function boot(IBootContext $context): void {
        // ... boot logic goes here ...
    }


    protected function registerScripts()
    {
        $eventDispatcher = \OC::$server->getEventDispatcher();
        $eventDispatcher->addListener('OCA\Files::loadAdditionalScripts', function() {
            script(self::APP_ID, 'script');
            style(self::APP_ID, 'style');
        });
        $eventDispatcher->addListener('OCA\Files_Sharing\Event\BeforeTemplateRenderedEvent', function() {
            Util::addScript(self::APP_ID, 'script');
            Util::addStyle(self::APP_ID, 'style');
        });
    }
}