<?php

namespace ManiaLivePlugins\eXpansion\Gui\Windows;

use ManiaLivePlugins\eXpansion\Gui\Config;

/**
 * @abstract
 */
class Window extends \ManiaLive\Gui\Window {

    protected $_titlebar;
    protected $_title;
    protected $_mainWindow;
    protected $mainFrame;
    protected $_mainText;
    protected $_closebutton;
    protected $_minbutton;
    protected $_closeAction;
    protected $_showCoords = 'False';
    protected $_windowFrame;
    protected $_windowPos;
    
    protected function onConstruct() {
        parent::onConstruct();
        $config = Config::getInstance();
       $this->_closeAction = \ManiaLive\Gui\ActionHandler::getInstance()->createAction(array($this, 'closeWindow'));
        
       $this->_windowPos = new \ManiaLib\Gui\Elements\Entry();
       $this->_windowPos->setName("_pos");
       $this->_windowPos->setId("windowPosition");
       $this->_windowPos->setScriptEvents(true);
       $this->_windowPos->setPosition(0,80);
       // $this->addComponent($this->_windowPos);
        
       $this->_windowFrame = new \ManiaLive\Gui\Controls\Frame();
       $this->_windowFrame->setScriptEvents(true);       
       $this->_windowFrame->setAlign("left", "top");


       $this->_mainWindow = new \ManiaLib\Gui\Elements\Quad($this->sizeX, $this->sizeY);        
       $this->_mainWindow->setId("MainWindow");        
       $this->_mainWindow->setStyle("Bgs1InRace");
       $this->_mainWindow->setSubStyle("BgWindow2");        
       $this->_mainWindow->setScriptEvents(true);       
       $this->_windowFrame->addComponent($this->_mainWindow);

       $this->_titlebar = new \ManiaLib\Gui\Elements\Quad($this->sizeX, $this->sizeY);
       $this->_titlebar->setId("Titlebar");
       $this->_titlebar->setImage($config->windowTitlebar);
       $this->_titlebar->setScriptEvents(true);
       $this->_windowFrame->addComponent($this->_titlebar);

       $this->_title = new \ManiaLib\Gui\Elements\Label(60, 4);
       $this->_title->setId("TitlebarText");
       $this->_title->setStyle("TextCardInfoSmall");
       $this->_title->setScale(0.9);
       $this->_windowFrame->addComponent($this->_title);

       $this->_closebutton = new \ManiaLib\Gui\Elements\Quad(5, 5);
       $this->_closebutton->setScriptEvents(true);
       $this->_closebutton->setId("Close");
       $this->_closebutton->setHalign("right");
       $this->_closebutton->setImage($config->windowClosebutton);
       $this->_closebutton->setImageFocus($config->windowClosebuttonActive);
       $this->_closebutton->setPosZ($this->posZ - 1);
       $this->_closebutton->setAction($this->_closeAction);
       $this->_windowFrame->addComponent($this->_closebutton);

       $this->_minbutton = new \ManiaLib\Gui\Elements\Quad(5, 5);
       $this->_minbutton->setScriptEvents(true);
       $this->_minbutton->setId("Minimize");
       $this->_minbutton->setHalign("right");
       $this->_minbutton->setImage($config->windowMinbutton);
       $this->_minbutton->setImageFocus($config->windowMinbuttonActive);
       $this->_minbutton->setPosZ($this->posZ - 1);
       $this->_windowFrame->addComponent($this->_minbutton);


       $this->_mainText = new \ManiaLib\Gui\Elements\Label($this->sizeX, 3);
       $this->_mainText->setPosition(4, -6);
       $this->_windowFrame->addComponent($this->_mainText);

       $this->mainFrame = new \ManiaLive\Gui\Controls\Frame();
       $this->mainFrame->setPosY(-3);
       $this->_windowFrame->addComponent($this->mainFrame);

        $this->addComponent($this->_windowFrame);

        $this->xml = new \ManiaLive\Gui\Elements\Xml();
        $this->xml->setContent('
        <timeout>0</timeout>            
        <script><!--
                       main () {     
                        declare Window <=> Page.GetFirstChild("' . $this->getId() . '");    
                        declare CMlLabel TitlebarText <=> (Page.GetFirstChild("TitlebarText") as CMlLabel);
                       // declare CMlEntry windowPos <=> (Page.GetFirstChild("windowPosition") as CMlEntry);
                        declare showCoords = '.$this->_showCoords.';
                        declare MoveWindow = False;
                        declare CloseWindow = False;   
                        declare isMinimized = False;   
                        declare Real CloseCounter = 1.0;
                        declare Real OpenCounter = 0.0;                        
                        declare CenterWindow = False;
                        declare Vec3 LastDelta = <Window.RelativePosition.X, Window.RelativePosition.Y, 0.0>;
                        declare Vec3 DeltaPos = <0.0, 0.0, 0.0>;
                        declare Real lastMouseX = 0.0;
                        declare Real lastMouseY =0.0;                                 
                                                                               
                        while(True) {                                                               
                                
                               if (showCoords) {                               
                                    declare coords = "$fffX:" ^ (MouseX - Window.PosnX) ^ " Y:" ^ (MouseY - Window.PosnY + 3 );                                   
                                    TitlebarText.Value = coords;
                                }
                                
                                if (MoveWindow) {                                                                                                    
                                    DeltaPos.X = MouseX - lastMouseX;
                                    DeltaPos.Y = MouseY - lastMouseY;
                                    LastDelta += DeltaPos;
                                    Window.RelativePosition = LastDelta;      
                                   // windowPos.Value = Window.PosnX ^ "," ^ Window.PosnY;
                                    lastMouseX = MouseX;
                                    lastMouseY = MouseY;
                                    }
                                
                                if (CenterWindow == True) {                                                                            
                                    if (Window.PosnX <= -140) {                                    
                                    Window.PosnX +=5.0;
                                    } 
                                    if (Window.PosnX >= -60) {                
                                    CenterWindow = False;
                                    }

                                }
                               
                                if (Window.Scale >= 0.25 && isMinimized) {
                                    Window.Scale -=0.075;  
                                    CenterWindow = True;
                                }   
                                
                                if (Window.Scale <= 1 && !CloseWindow && !isMinimized) {
                                   Window.Scale +=0.075;                                                                      
                                }
                                

                                if (CloseWindow)
                                {                                                                       
                                        Window.Scale = CloseCounter;
                                        if (CloseCounter <= 0) {
                                                Window.Hide();
                                                CloseWindow = False;
                                        }                                
                                        CloseCounter -=0.075;
                                }
                                
                               if (MouseLeftButton == True || MouseMiddleButton == True) {
                                
                       
                                        foreach (Event in PendingEvents) {
                                                        if (Event.Type == CMlEvent::Type::MouseClick && Event.ControlId == "Titlebar")  {                                                          
                                                            lastMouseX = MouseX;
                                                            lastMouseY = MouseY;                                                   
                                                            MoveWindow = True;   
                                                        }                                     
                                                }
                                        }
                                        
                                else {
                                        MoveWindow = False;
                                } 
                                
                                
                             
                                foreach (Event in PendingEvents) {                                                
                                    if (Event.Type == CMlEvent::Type::MouseClick && Event.ControlId == "Close") {
                                            CloseWindow = True;
                                    }   
                                    if (Event.Type == CMlEvent::Type::MouseClick && Event.ControlId == "Minimize") {                                            
                                            isMinimized = True;                                                                                       
                                    }   
                                    if (Event.Type == CMlEvent::Type::MouseClick && Event.ControlId == "MainWindow") {                                            
                                            isMinimized = False;                                            
                                    }                                  
                                    
                                }
                                yield;                        
                        }
                  
                  
                } 
                --></script>');
        $this->addComponent($this->xml);
        $this->setPositionZ(-75);
    }

    function onResize($oldX, $oldY) {
        parent::onResize($oldX, $oldY);              
       $this->_windowFrame->setSize($this->sizeX, $this->sizeY);
       $this->_mainWindow->setSize($this->sizeX + 0.6, $this->sizeY);
       $this->_mainWindow->setPosX(-0.4);
       $this->_mainText->setSize($this->sizeX, $this->sizeY);
       $this->_title->setSize($this->sizeX, 4);
       $this->_title->setPosition(($this->_title->sizeX / 2), 0);
       $this->_title->setHalign("center");

       $this->_titlebar->setSize($this->sizeX, 4);
       $this->_closebutton->setSize(4, 4);
       $this->_closebutton->setPosition($this->sizeX - 1, 0);
       $this->_minbutton->setSize(4, 4);
       $this->_minbutton->setPosition($this->sizeX - 5, 0);
       $this->mainFrame->setSize($this->sizeX - 4, $this->sizeY - 8);
        //$this->mainFrame->setPosY(-6);
        $this->setPositionZ(75);
    }

    function onShow() {        
        
    }
    
    function setDebug($bool) {
        if ($bool) {
           $this->_showCoords = 'True';            
        }
    }
    
    function setText($text) {
       $this->_mainText->setText($text);
    }

    function setTitle($text) {
       $this->_title->setText('$fff' . $text);
    }
    
    function closeWindow() {
        $this->erase($this->getRecipient());        
    }
    
    function destroy() {       
        echo "window destroy";                                     
      $this->_windowFrame->destroy();       
      $this->mainFrame->destroy();                     
       
        $this->clearComponents();
       
       \ManiaLive\Gui\ActionHandler::getInstance()->deleteAction($this->_closeAction);
      $this->_closeAction = null;
       
       parent::destroy();
        
    }

}

?>
