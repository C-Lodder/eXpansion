<?php

namespace ManiaLivePlugins\eXpansion\Adm\Gui\Windows;

use \ManiaLivePlugins\eXpansion\Gui\Elements\Button as myButton;
use \ManiaLivePlugins\eXpansion\Gui\Elements\Inputbox;
use \ManiaLivePlugins\eXpansion\Gui\Elements\Checkbox;
use \ManiaLivePlugins\eXpansion\Gui\Elements\Ratiobutton;
use ManiaLivePlugins\eXpansion\Adm\Gui\Controls\MatchSettingsFile;
use ManiaLive\Gui\ActionHandler;

/**
 * Server Controlpanel Main window
 * 
 * @author Petri
 */
class ServerControlMain extends \ManiaLivePlugins\eXpansion\Gui\Windows\Window {

    /** @var \DedicatedApi\Connection */
    private $connection;

    /** @var \ManiaLive\Data\Storage */
    private $storage;

    /** @var \ManiaLivePlugins\eXpansion\Adm\Adm */
    public static $mainPlugin;
    private $frame;
    private $closeButton;
    private $actions;
    private $btn1, $btn2, $btn3, $btn4, $btn5;

    function onConstruct() {
        parent::onConstruct();
        $config = \ManiaLive\DedicatedApi\Config::getInstance();
        $this->connection = \DedicatedApi\Connection::factory($config->host, $config->port);
        $this->storage = \ManiaLive\Data\Storage::getInstance();

        $this->frame = new \ManiaLive\Gui\Controls\Frame();
        $this->frame->setLayout(new \ManiaLib\Gui\Layouts\Line());

        $this->actions = new \stdClass();
        $this->actions->close = ActionHandler::getInstance()->createAction(array($this, "close"));
        $this->actions->serverOptions = ActionHandler::getInstance()->createAction(array($this, "serverOptions"));
        $this->actions->gameOptions = ActionHandler::getInstance()->createAction(array($this, "gameOptions"));
        $this->actions->matchSettings = ActionHandler::getInstance()->createAction(array($this, "matchSettings"));
        $this->actions->serverManagement = ActionHandler::getInstance()->createAction(array($this, "serverManagement"));
        $this->actions->adminGroups = ActionHandler::getInstance()->createAction(array($this, "adminGroups"));

        $this->btn1 = new myButton();
        $this->btn1->setText(__("Server management"));
        $this->btn1->setAction($this->actions->serverManagement);
        $this->btn1->colorize("f00");
        $this->frame->addComponent($this->btn1);

        $this->btn2 = new myButton();
        $this->btn2->setText(__("Server options"));
        $this->btn2->setAction($this->actions->serverOptions);
        $this->frame->addComponent($this->btn2);

        $this->btn3 = new myButton();
        $this->btn3->setText(__("Game options"));
        $this->btn3->setAction($this->actions->gameOptions);
        $this->frame->addComponent($this->btn3);

        $this->btn4 = new myButton();
        $this->btn4->setText(__("Admin Groups"));
        $this->btn4->setAction($this->actions->adminGroups);
        $this->btn4->colorize("0d0");
        $this->frame->addComponent($this->btn4);

        $this->btn5 = new myButton();
        $this->btn5->setText(__("Match settings"));
        $this->btn5->setAction($this->actions->matchSettings);
        $this->frame->addComponent($this->btn5);

        $this->mainFrame->addComponent($this->frame);

        $this->closeButton = new myButton();
        $this->closeButton->setText(__("Close"));
        $this->closeButton->setAction($this->actions->close);
        $this->mainFrame->addComponent($this->closeButton);
    }

    function serverOptions($login) {
        self::$mainPlugin->serverOptions($login);
    }

    function gameOptions($login) {
        self::$mainPlugin->gameOptions($login);
    }

    function matchSettings($login) {
        self::$mainPlugin->matchSettings($login);
    }

    function serverManagement($login) {
        self::$mainPlugin->serverManagement($login);
    }

    function adminGroups($login) {
        self::$mainPlugin->adminGroups($login);
    }

    function close() {
        $this->Erase($this->getRecipient());
    }

    function onResize($oldX, $oldY) {
        parent::onResize($oldX, $oldY);
        $this->frame->setPosition(8, -10);
        $this->closeButton->setPosition($this->sizeX - 18, -($this->sizeY - 6));
    }

    function destroy() {
        ActionHandler::getInstance()->deleteAction($this->actions->close);
        ActionHandler::getInstance()->deleteAction($this->actions->serverOptions);
        ActionHandler::getInstance()->deleteAction($this->actions->gameOptions);
        ActionHandler::getInstance()->deleteAction($this->actions->matchSettings);
        ActionHandler::getInstance()->deleteAction($this->actions->serverManagement);
        unset($this->actions);
        $this->btn1->destroy();
        $this->btn2->destroy();
        $this->btn3->destroy();
        $this->btn4->destroy();
        $this->btn5->destroy();
        $this->frame->clearComponents();        
        $this->connection = null;
        $this->storage = null;

        parent::destroy();
    }

}

?>