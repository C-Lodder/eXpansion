<?php

namespace ManiaLivePlugins\eXpansion\Custom321Go;

use ManiaLivePlugins\eXpansion\Core\types\ExpPlugin;

/**
 * Description of Custom321Go
 *
 * @author Petri Järvisalo <petri.jarvisalo@gmail.com>
 */
class Custom321Go extends ExpPlugin
{

    public function exp_onReady()
    {
        parent::exp_onReady();

        $this->enableDedicatedEvents();
        $window = Gui\Hud\CountdownHud::create();
        $window->show();
    }

    public function onEndMatch($rankings, $winnerTeamOrMap)
    {
        Gui\Hud\CountdownHud::EraseAll();
    }

    public function onStatusChanged($statusCode, $statusName)
    {
        if ($statusCode == 4) {
            $window = Gui\Hud\CountdownHud::create();
            $window->show();
        }
    }

}
