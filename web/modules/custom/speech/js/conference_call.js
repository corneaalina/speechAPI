(function ($, Drupal) {

    'use strict';

    /**
     * @type {Drupal~behavior}
     */
    Drupal.behaviors.conferenceCall = {
        attach: function (context, settings) {
            var callClient;
            var call;

            // Hides the answer and hangup buttons.
            hideInput('answer');
            hideInput('hangup');
            $('audio#calling').trigger("pause");
            $('audio#ringtone').trigger("pause");

            // Defines the Sinch client.
            var sinchClient = new SinchClient({
                applicationKey: "1ece0175-849e-42d7-9b4b-b9fd1a1c1358",
                capabilities: {
                    calling: true
                    // video: true
                },
                supportActiveConnection: true
            });

            // Starts the sinchClient.
            $.get('/ticket', function (userTicket)  {
                var ticket = JSON.parse(userTicket);
                sinchClient.start(ticket, afterStartSinchClient());

            });

            var callListeners = {
                onIncomingCall: function (incomingCall) {
                    call = incomingCall;
                    hideInput('call');
                    showInput('answer');
                    showInput('hangup');
                    $('audio#ringtone').prop("currentTime", 0);
                    $('audio#ringtone').trigger("play");
                },
                onCallEstablished: function(call) {
                    hideInput('call');
                    hideInput('answer');
                    $('audio#calling').trigger("pause");
                    $('audio#ringtone').trigger("pause");
                    $('audio#incoming').attr('src', call.incomingStreamURL);
                },
                onCallEnded: function(call) {
                    enableInput('username_to_call');
                    showInput('call');
                    hideInput('answer');
                    hideInput('hangup');
                    $('audio#calling').trigger("pause");
                    $('audio#incoming').attr('src', '');
                }
            };

            function afterStartSinchClient() {
                sinchClient.startActiveConnection();
                callClient = sinchClient.getCallClient();
                callClient.initStream();
                callClient.addEventListener(callListeners);
            }

            // Initiates a call.
            var callingButton = '.calling-button';
            $(callingButton).on('click', function(event) {
                event.preventDefault();
                if (!call) {
                    var usernameToCall = $("input#username_to_call").val();
                    call = callClient.callUser(usernameToCall);
                    // If the call has been successfully initiated, hide the "call" button and show the "hangup" button.
                    if (call) {
                        $('audio#calling').prop("currentTime", 0);
                        $('audio#calling').trigger("play");
                        disableInput('username_to_call');
                        hideInput('call');
                        showInput('hangup');
                    }
                    call.addEventListener(callListeners);
                 }
            });

            // Hangs up the call.
            var hangupButton = '.hangup-button';
            $(hangupButton).on('click', function(event) {
                event.preventDefault();
                if (call) {
                    call.hangup();
                    call = null;
                    enableInput('username_to_call');
                    showInput('call');
                    hideInput('answer');
                    hideInput('hangup');
                    $('audio#ringtone').trigger("pause");
                }
            });

            // Answers the call.
            var answerButton = '.answer-button';
            $(answerButton).on('click', function(event) {
                event.preventDefault();
                if (call) {
                    try {
                        call.answer();
                    }catch(error) {
                        handleError();
                    }
                    call.addEventListener(callListeners);
                }
            });

            // Displays the error.
            var handleError = function(error) {
                console.log(error);
            };

            // Disables an input.
            function disableInput(id) {
                document.getElementById(id).disabled = true;
            }

            // Enables an input.
            function enableInput(id) {
                document.getElementById(id).disabled = false;
            }

            // Hides an input.
            function hideInput(id) {
                document.getElementById(id).style.display = 'none';
            }

            // Displays an input.
            function showInput(id) {
                document.getElementById(id).style.display = 'block';
            }

        }
    };

})(jQuery, Drupal);
