(function($) {
   $(document).ready(function(){
    $(".otp-input input").keyup(function () {
        if (this.value.length == this.maxLength) {
          $(this).next('.otp-input input').focus();
        }
    });
    $("#otp-form .otp-submit").click(function(e){
        e.preventDefault();
        let searchParams = new URLSearchParams(window.location.search);

        let ot1 = $("#ot1").val();
        let ot2 = $("#ot2").val();
        let ot3 = $("#ot3").val();
        let ot4 = $("#ot4").val();
        let ot5 = $("#ot5").val();
        let ot6 = $("#ot6").val();

        let get_otp = ot1+ot2+ot3+ot4+ot5+ot6;

        let cvalue = $("#cvalue").val();
        console.log(cvalue);

        if(searchParams.has('ac') && get_otp){
            let param = searchParams.get('ac');
            $.ajax({
                url: "/sa-otpmatch?nid="+param+"&otp="+get_otp,
                success: function (data) {
                    Success = true;
                    if(data.result == "otp_match"){
                        $("#myModal").css("display","block");
                        $(".error-msg-otp").text("OTP Match");
                        $(".error-msg-otp").css("color","green");
                        window.location.replace("/campaign-account-approval?ac="+param+"&type="+cvalue);
                    }else{
                        $(".error-msg-otp").text("OTP Wrong");
                        $(".error-msg-otp").css("color","red");
                        $(".otp-input input").val("");
                    }
                    
                }
            });
            //window.location.replace("/signature-account-approval?ac="+param);
        }
        
    });

    $('.FAQ-section .main .heading').click(function(){
        $(this).next().slideToggle();
        $('.FAQ-section .content').not($(this).next()).slideUp();
        $(this).toggleClass("open");
        $('.FAQ-section .main .heading').not($(this)).removeClass('open');
        //$('.FAQ-section .main .heading').removeClass('open');

        
        //$(this).removeClass('closed').addClass('open');
        //$('.FAQ-section .main .heading').not($(this)).removeClass('open').addClass('closed');
        /*if($(this).hasClass("open")){
            $(this).removeClass('open');
        }else{
            $(this).removeClass('closed').addClass('open');
        }*/
    });

    $("#myModal").css("display","none");
    $("#myBtn").click(function(){
        let searchParams = new URLSearchParams(window.location.search);
        if(searchParams.has('ac')){
            let param = searchParams.get('ac');
            $.ajax({
                url: "/sa-sendotp?ac="+param,
                success: function (data) {
                    Success = true;
                    if(data.result == "sms_sent"){
                        $("#myModal").css("display","block");
                    }else{
                        $(".error-msg").text("There are some techincal issues, please try again later");
                    }
                    console.log(data.result);
                }
            });
        }
        
    });

    $("#otp-form .otp-resend").click(function(e){
        e.preventDefault();

        $(this).addClass("disabled"); //After clicking to get the verification code, disable the button and start the countdown
        var time = 60; //Countdown time, custom
        var $this = $(this); //Backup, the timer is asynchronous, this is not this
        var timer = setInterval(function () {
            time--; //Start countdown
            if (time == 0) { //When the countdown is 0 seconds, turn off the timer, change the button display text and set it to be clickable
                clearInterval(timer);
                $this.val('Resend verification code');
                $this.removeClass("disabled");
                return;
            }
            $this.val(time); //Display remaining seconds
        }, 1000); //The timer goes once a second, and decreases by one each time it counts down

        let searchParams = new URLSearchParams(window.location.search);
        if(searchParams.has('ac')){
            let param = searchParams.get('ac');
            $.ajax({
                url: "/sa-sendotp?ac="+param,
                success: function (data) {
                    Success = true;
                    if(data.result == "sms_sent"){
                        $(".error-msg-otp").text("SMS sent successfully");
                        $(".error-msg-otp").css("color","green");
                    }else{
                        $(".error-msg-otp").text("There are some techincal issues, please try again later");
                        $(".error-msg-otp").css("color","red");
                    }
                    console.log(data.result);
                }
            });
        }
        
    });

    $(".close").click(function(){
        $("#myModal").css("display","none");
    });


    $("#faqModal").css("display","none");
    $("#faqBtn").click(function(){
        $("#faqModal").css("display","block");
    });
    $(".closefaq").click(function(){
        $("#faqModal").css("display","none");
    });


    let typeParams = new URLSearchParams(window.location.search);
    let getctype = typeParams.get('type');
    //alert(getctype);

    if(getctype == "signature"){
        $(".path-campaign-account-approval .region-content,.path-campaign-account-thanks .region-content").prepend('<div class="signature-landing-page"><div class="header-section"><div class="top-section"><div class="logo-left"><img src="modules/custom/d8campaign/images/svc_bank_logo_gold.png" alt="logo"></div><div class="logo-right"></div></div></div></div>');
    }
    if(getctype == "classic"){
        $(".path-campaign-account-approval .region-content,.path-campaign-account-thanks .region-content").prepend('<div class="signature-landing-page"><div class="header-section"><div class="top-section" style="background:white;"><div class="logo-left"><img src="modules/custom/d8campaign/images/classic/single-New-Logo_2016.jpg" alt="logo"></div><div class="logo-right"></div></div></div></div>');
    }
    
    

   })
})(jQuery);


