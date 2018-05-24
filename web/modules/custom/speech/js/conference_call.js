(function ($) {

    'use strict';

    var callClient;
    var call;

    // Hides the answer and hangup buttons.
    hideInput('#answer');
    hideInput('#hangup');
    $('audio#calling').trigger("pause");
    $('audio#ringtone').trigger("pause");

    // Starts the sinchClient.
    $.get('/ticket', function (sinch_data)  {
        var sinch_data = JSON.parse(sinch_data);
        var app_key = sinch_data.application_key;
        delete sinch_data.application_key;

        // Defines the Sinch client.
        var sinchClient = new SinchClient({
            applicationKey: app_key,
            capabilities: {
                calling: true
                // video: true
            },
            supportActiveConnection: true
        });

        // Starts the sinch client.
        sinchClient.start(sinch_data, afterStartSinchClient());

        // Does the logic after the sinch client has started.
        function afterStartSinchClient() {
            // Starts the active connection.
            sinchClient.startActiveConnection();
            // Initiates the stream.
            callClient = sinchClient.getCallClient();
            // Initiates the stream.
            callClient.initStream();
            // Adds the call listeners.
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
                    disableInput('#username_to_call');
                    hideInput('#call');
                    showInput('#hangup');
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
                showInput('#username_to_call');
                enableInput('#username_to_call');
                showInput('#call');
                hideInput('#answer');
                hideInput('#hangup');
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
    });

    // The call listeners.
    var callListeners = {
        onIncomingCall: function (incomingCall) {
            call = incomingCall;
            hideInput('#call');
            showInput('#answer');
            showInput('#hangup');
            hideInput('#username_to_call');
            $('audio#ringtone').prop("currentTime", 0);
            $('audio#ringtone').trigger("play");
        },
        onCallEstablished: function(call) {
            hideInput('#call');
            hideInput('#answer');
            showInput('#hangup');
            $('audio#calling').trigger("pause");
            $('audio#ringtone').trigger("pause");
            $('audio#incoming').attr('src', call.incomingStreamURL);
        },
        onCallEnded: function(call) {
            enableInput('#username_to_call');
            showInput('#call');
            hideInput('#answer');
            hideInput('#hangup');
            showInput('#username_to_call');
            $('audio#calling').trigger("pause");
            $('audio#incoming').attr('src', '');
        }
    };

    // Displays the error.
    var handleError = function(error) {
        console.log(error);
    };

    // Disables an input.
    function disableInput(id) {
        $(id).prop('disabled', true);
    }

    // Enables an input.
    function enableInput(id) {
        $(id).prop('disabled', false);
    }

    // Hides an input.
    function hideInput(id) {
        $(id).hide();
    }

    // Displays an input.
    function showInput(id) {
        $(id).show();
    }

})(jQuery);
