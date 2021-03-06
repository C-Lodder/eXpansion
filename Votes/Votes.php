<?php

namespace ManiaLivePlugins\eXpansion\Votes;

use ManiaLivePlugins\eXpansion\AdminGroups\AdminGroups;
use ManiaLivePlugins\eXpansion\Votes\Gui\Windows\VoteSettingsWindow;
use Maniaplanet\DedicatedServer\Structures\GameInfos;

class Votes extends \ManiaLivePlugins\eXpansion\Core\types\ExpPlugin
{
    /** @var Config */
    private $config;
    private $useQueue = false;
    private $timer = 0;
    private $voter = "";
    private $counters = array();
    private $update = false;
    private $resCount = 0;
    private $lastMapUid = "";
    /** @var int */
    private $origTimeValue = 0;


    public function eXpOnInit()
    {
        $this->config = Config::getInstance();
    }

    /**
     * returns managedvote with key of command name
     *
     * @return \ManiaLivePlugins\eXpansion\Votes\Structures\ManagedVote[]
     */
    private function getVotes()
    {
        $out = array();
        for ($x = 0; $x < count($this->config->managedVote_commands); $x++) {
            $vote = new Structures\ManagedVote();
            $vote->managed = $this->config->managedVote_enable[$this->config->managedVote_commands[$x]];
            $vote->command = $this->config->managedVote_commands[$x];
            $vote->ratio = $this->config->managedVote_ratios[$this->config->managedVote_commands[$x]];
            $vote->timeout = $this->config->managedVote_timeouts[$this->config->managedVote_commands[$x]];
            $vote->voters = $this->config->managedVote_voters[$this->config->managedVote_commands[$x]];
            $out[$vote->command] = $vote;
        }

        return $out;
    }

    public function eXpOnLoad()
    {
        $cmd = $this->registerChatCommand("replay", "vote_Restart", 0, true);
        $cmd->help = 'Start a vote to restart a map';
        $cmd = $this->registerChatCommand("restart", "vote_Restart", 0, true);
        $cmd->help = 'Start a vote to restart a map';
        $cmd = $this->registerChatCommand("res", "vote_Restart", 0, true);
        $cmd->help = 'Start a vote to restart a map';

        $cmd = $this->registerChatCommand("skip", "vote_Skip", 0, true);
        $cmd->help = 'Start a vote to skip a map';

        $cmd = AdminGroups::addAdminCommand('cancelvote', $this, 'cancelVote', 'cancel_vote');
        $cmd->setHelp = 'Cancel current running callvote';
        AdminGroups::addAlias($cmd, "cancel");
    }

    public function eXpOnReady()
    {
        $this->enableDedicatedEvents();
        $this->enableTickerEvent();

        $this->counters = array();
        $this->timer = time();
        $this->setPublicMethod("vote_restart");
        $this->setPublicMethod("vote_skip");
        $this->setPublicMethod("showVotesConfig");

        if ($this->storage->gameInfos->gameMode == GameInfos::GAMEMODE_SCRIPT) {
            $this->setPublicMethod("showTimeOptions");
            $this->setPublicMethod("vote_time");

        }

        $cmd = AdminGroups::addAdminCommand('votes', $this, 'showVotesConfig', 'server_votes'); //
        $cmd->setHelp('shows config window for managing votes');
        $cmd->setMinParam(0);


        $this->lastMapUid = $this->storage->currentMap->uId;

        if ($this->isPluginLoaded('\ManiaLivePlugins\\eXpansion\\Maps\\Maps') && $this->config->restartVote_useQueue) {
            $this->useQueue = true;
            $this->debug("[exp\Votes] Restart votes set to queue");
        } else {
            $this->debug("[exp\Votes] Restart vote set to normal");
        }

        $this->update = true;
    }

    public function showTimeOptions($login)
    {

    }

    public function vote_time($login, $seconds)
    {

    }

    public function syncSettings()
    {

        $managedVotes = $this->getVotes();

        foreach ($managedVotes as $cmd => $vote) {
            $ratios[] = new \Maniaplanet\DedicatedServer\Structures\VoteRatio($vote->command, $vote->ratio);
        }
        $this->connection->setCallVoteRatios($ratios, true);
        if ($this->config->use_votes == false) {
            $this->connection->setCallVoteTimeOut(0);
        } else {
            $this->connection->setCallVoteTimeOut(($this->config->global_timeout * 1000));
        }
    }

    public function onBeginMatch()
    {
        $this->counters = array();
        $this->timer = time();

        if ($this->storage->currentMap->uId == $this->lastMapUid) {
            $this->resCount++;
        } else {
            $this->lastMapUid = $this->storage->currentMap->uId;
            $this->resCount = 0;
        }
    }

    public function onTick()
    {
        if ($this->update) {
            $this->update = false;
            $this->syncSettings();
        }
    }

    public function onEndMap($rankings, $map, $wasWarmUp, $matchContinuesOnNextMap, $restartMap)
    {

    }

    public function vote_Restart($login)
    {
        try {
            $managedVotes = $this->getVotes();

            // if vote is not managed...
            if (!array_key_exists('RestartMap', $managedVotes)) {
                return;
            }
            // if vote is not managed...
            if ($managedVotes['RestartMap']->managed == false) {
                return;
            }
            if ($managedVotes['RestartMap']->ratio == -1.) {
                $this->eXpChatSendServerMessage(eXpGetMessage("#error#Restart vote is disabled!"), $login);

                return;
            }
            $config = Config::getInstance();
            if ($config->restartLimit != 0 && $config->restartLimit <= $this->resCount) {
                $this->eXpChatSendServerMessage(
                    eXpGetMessage("#error#Map limit for voting restart reached."),
                    $login,
                    array($this->config->restartLimit)
                );
                return;
            }

            $this->voter = $login;
            $vote = $managedVotes['RestartMap'];
            $this->debug("[exp\\Votes] Calling Restart (queue) vote..");
            $vote->callerLogin = $this->voter;
            $vote->cmdName = "Replay";
            $vote->cmdParam = array("the current map");
            $this->connection->callVote($vote, $vote->ratio, ($vote->timeout * 1000), $vote->voters);

            $player = $this->storage->getPlayerObject($login);
            $msg = eXpGetMessage('#variable#%s #vote#initiated restart map vote..');
            $this->eXpChatSendServerMessage(
                $msg,
                null,
                array(\ManiaLib\Utils\Formatting::stripCodes($player->nickName, 'wosnm'))
            );
        } catch (\Exception $e) {
            $this->connection->chatSendServerMessage("[Notice] " . $e->getMessage(), $login);
        }
    }

    public function vote_Skip($login)
    {

        try {
            $managedVotes = $this->getVotes();
            // if vote is not managed...
            if (!array_key_exists('NextMap', $managedVotes)) {
                return;
            }
            // if vote is not managed...
            if ($managedVotes['NextMap']->managed == false) {
                return;
            }
            if ($managedVotes['NextMap']->ratio == -1.) {
                $this->eXpChatSendServerMessage(eXpGetMessage("#error#Skip vote is disabled!"), $login);

                return;
            }

            $this->voter = $login;
            $vote = $managedVotes['NextMap'];
            $this->debug("[exp\Votes] Calling Skip vote..");
            $vote->callerLogin = $this->voter;
            $vote->cmdName = "Skip";
            $vote->cmdParam = array("the current map");
            $this->connection->callVote($vote, $vote->ratio, ($vote->timeout * 1000), $vote->voters);

            $player = $this->storage->getPlayerObject($login);
            $msg = eXpGetMessage('#variable#%1$s #vote#initiated skip map vote..');
            $this->eXpChatSendServerMessage(
                $msg,
                null,
                array(\ManiaLib\Utils\Formatting::stripCodes($player->nickName, 'wosnm'))
            );
        } catch (\Exception $e) {
            $this->connection->chatSendServerMessage("[Notice] " . $e->getMessage(), $login);
        }
    }

    public function onVoteUpdated($stateName, $login, $cmdName, $cmdParam)
    {
        // in case managed votes are disabled, return..
        $config = Config::getInstance();

        if ($config->use_votes == false) {
            return;
        }

        $managedVotes = $this->getVotes();

        // disable default votes... and replace them with our own implementations
        if ($stateName == "NewVote") {
            if ($cmdName == "RestartMap") {
                $this->connection->cancelVote();
                $this->voter = $login;
                $this->vote_Restart($login);

                return;
            }
            if ($cmdName == "SkipMap") {
                $this->connection->cancelVote();
                $this->voter = $login;
                $this->vote_Skip($login);

                return;
            }
        }
        // check for our stuff...
        if ($stateName == "NewVote") {
            $login = $this->voter;
            foreach ($managedVotes as $cmd => $vote) {
                if ($cmdName == $cmd) {
                    if ($vote->ratio == -1.) {
                        $this->connection->cancelVote();
                    }
                }
            }

            if (!isset($this->counters[$cmdName])) {
                $this->counters[$cmdName] = 0;
            }

            $this->counters[$cmdName]++;

            if ($config->limit_votes > 0) {
                if ($this->counters[$cmdName] > $config->limit_votes) {
                    $this->connection->cancelVote();
                    $msg = eXpGetMessage("Vote limit reached.");
                    $this->eXpChatSendServerMessage($msg);
                    return;
                }
            }
        }


        // own votes handling...

        if ($stateName == "VotePassed") {
            if ($cmdName != "Replay" && $cmdName != "Skip") {
                return;
            }

            $msg = eXpGetMessage('#vote_success# $iVote passed!');
            $this->eXpChatSendServerMessage($msg, null);
            $voter = $this->voter;
            if ($cmdName == "Replay") {
                if (sizeof($this->storage->players) == 1) {
                    $this->callPublicMethod('\ManiaLivePlugins\\eXpansion\\Maps\\Maps', 'replayMapInstant', $voter);
                } else {
                    $this->callPublicMethod('\ManiaLivePlugins\\eXpansion\\Maps\\Maps', 'replayMap', $voter);
                }
            }
            if ($cmdName == "Skip") {
                $this->connection->nextMap();
            }
            if ($cmdName == "Time") {

            }
            $this->voter = null;
        }

        if ($stateName == "VoteFailed") {
            if ($cmdName != "Replay" && $cmdName != "Skip") {
                return;
            }
            $msg = eXpGetMessage('#vote_failure# $iVote failed!');
            $this->eXpChatSendServerMessage($msg, null);
            $this->voter = null;
        }


    }

    public function cancelVote($login)
    {
        $player = $this->storage->getPlayerObject($login);
        $vote = $this->connection->getCurrentCallVote();
        if (!empty($vote->cmdName)) {
            $this->connection->cancelVote();
            $msg = eXpGetMessage('#admin_action#Admin #variable#%1$s #admin_action# cancelled the vote!');
            $this->eXpChatSendServerMessage(
                $msg,
                null,
                array(\ManiaLib\Utils\Formatting::stripCodes($player->nickName, 'wosnm'), $login)
            );

            return;
        } else {
            $this->connection->chatSendServerMessage('Notice: Can\'t cancel a vote, no vote in progress!', $login);
        }
    }

    public function showVotesConfig($login)
    {
        $window = Gui\Windows\VoteSettingsWindow::Create($login);
        $window->setSize(120, 96);
        $window->setTitle(__("Configure Votes", $login));
        $window->addLimits();
        $window->populateList($this->getVotes(), $this->metaData);
        $window->addMxVotes();
        $window->show($login);
    }

    public function eXpOnUnload()
    {
        VoteSettingsWindow::EraseAll();
    }

    public function onSettingsChanged(\ManiaLivePlugins\eXpansion\Core\types\config\Variable $var)
    {
        if ($var->getConfigInstance() instanceof Config) {
            $this->update = true;
        }
    }
}
