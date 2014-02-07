lbl_clock.SetText(""^TextLib::SubString(CurrentLocalDateText, 11, 2)^":"^TextLib::SubString(CurrentLocalDateText, 14, 2)^":"^TextLib::SubString(CurrentLocalDateText, 17, 2));

declare nbSpec = 0;
declare nbPlayer = 0;
foreach (Player in Players) {
    if(Player.Login != serverLogin){
        if(!Player.RequestsSpectate){
            //log(Player.Login^"Is Player");
            nbPlayer += 1;
        }else{
            //log(Player.Login^"Is Spec");
            nbSpec += 1;
        }
    }
} 

declare playerLabel = (Page.GetFirstChild("nbPlayer") as CMlLabel);
declare specLabel = (Page.GetFirstChild("nbSpec") as CMlLabel);

playerLabel.SetText(""^nbPlayer);
specLabel.SetText(""^nbSpec);

sleep(1000);