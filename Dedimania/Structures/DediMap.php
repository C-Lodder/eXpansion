<?php

namespace ManiaLivePlugins\eXpansion\Dedimania\Structures;

class DediMap extends \DedicatedApi\Structures\AbstractStructure {

    /** @var string|null */
    public $uId = null;

    /** @var int */
    public $mapMaxRank = 30;

    /** @var string */
    public $allowedGameModes = "TA,Rounds";

    public function __construct($uid, $maxrank, $allowedgamemodes) {
	$this->uId = $uid;
	$this->mapMaxRank = $maxrank;
	$this->allowedGameModes = $allowedgamemodes;
    }

}

?>