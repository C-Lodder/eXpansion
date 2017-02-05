<?php

namespace ManiaLivePlugins\eXpansion\Faq\Gui\Controls;

/**
 * Description of Header
 *
 * @author Reaby
 */
class Header extends FaqControl
{

    public function __construct($text, $level = 1)
    {
        parent::__construct($text);
        $this->label->setStyle("TextRaceMessageBig");
        $this->label->setTextSize(4 - (.5*($level+1)));
        $this->label->setTextColor("fff");
        $this->setSizeY(7);
        $this->setAlign("left", "top");
    }
}
