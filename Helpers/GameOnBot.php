<?php

class GameOnBot
{
    private string $secret;
    private string $userId;

    public function __construct(string $secret, string $userId)
    {
        $this->secret = $secret;
        $this->userId = $userId;
    }

    public function generateUserHash(): string
    {
        return hash_hmac('sha256', $this->userId, $this->secret);
    }

    public function getEmbedScript(): string
    {
        $userHash = $this->generateUserHash();
        $userId = htmlspecialchars($this->userId, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!-- Chatbase GameOnBot Integration -->
<script>
(function(){
    window.chatbaseUserHash = "$userHash";
    window.chatbaseUserId = "$userId";
    if(!window.chatbase||window.chatbase("getState")!=="initialized"){
        window.chatbase=(...arguments)=>{
            if(!window.chatbase.q){window.chatbase.q=[]}
            window.chatbase.q.push(arguments)
        };
        window.chatbase=new Proxy(window.chatbase,{
            get(target,prop){
                if(prop==="q"){return target.q}
                return(...args)=>target(prop,...args)
            }
        })
    }
    const onLoad=function(){
        const script=document.createElement("script");
        script.src="https://www.chatbase.co/embed.min.js";
        script.id="2Ou9EfKrBfGz8JV2GAcqr";
        script.domain="www.chatbase.co";
        document.body.appendChild(script)
    };
    if(document.readyState==="complete"){
        onLoad()
    }else{
        window.addEventListener("load",onLoad)
    }
})();
</script>
<style>
/* Ajusta la posición del widget si es necesario */
#chatbase-bot {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 9999;
}
</style>
HTML;
    }
}
?>