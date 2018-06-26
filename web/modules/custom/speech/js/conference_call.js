(function ($, Drupal) {

    'use strict';

    var callClient;
    var call;

    // Hides the answer and hangup buttons.
    hideInput('#answer');
    hideInput('#hangup');
    hideInput('#proceed-to-transcription');
    $('audio#calling').trigger("pause");
    $('audio#ringtone').trigger("pause");

    navigator.mediaDevices.getUserMedia({
        audio: true
    }).then(function (stream) {
        var audio_context = new AudioContext;
        var input = audio_context.createMediaStreamSource(stream);
        var recorder = new Recorder(input);

        // Starts the sinchClient
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
                    if (usernameToCall) {
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
                    hideInput('#callLog');
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
                // Displays the caller username.
                $('div#callLog').append("<div>Incoming call from " + incomingCall.fromId + "</div>");
                $("input#username_to_call").val(incomingCall.fromId);
                $('audio#ringtone').prop("currentTime", 0);
                $('audio#ringtone').trigger("play");
            },
            onCallEstablished: function (call) {
                hideInput('#call');
                hideInput('#answer');
                hideInput('#callLog');
                showInput('#hangup');
                $('audio#calling').trigger("pause");
                $('audio#ringtone').trigger("pause");
                $('audio#incoming').attr('src', call.incomingStreamURL);
                recorder.record();
                //Stops the recording when it reaches 1 minute.
                setTimeout(stopRecording, 60000);
            },
            onCallEnded: function (call) {
                enableInput('#username_to_call');
                showInput('#call');
                hideInput('#answer');
                hideInput('#hangup');
                hideInput('#callLog');
                showInput('#username_to_call');
                $('audio#calling').trigger("pause");
                $('audio#incoming').attr('src', '');
                if (recorder.recording) {
                    stopRecording();
                }

                showInput('#proceed-to-transcription');
            }
        };

        // Stops and downloads the audio recording.
        function stopRecording() {
            recorder.stop();

            // Downloads the audio file.
            recorder.exportWAV(function(blob) {
                var url = URL.createObjectURL(blob);
                var audio = document.createElement('audio');
                var href = document.createElement('a');
                audio.controls = true;
                audio.src = url;
                href.href = url;
                href.download = new Date().toDateString() + ' - ' + $("input#username_to_call").val() + '.wav';
                href.innerHTML = href.download;
                href.click();
            });
        }
    });

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

})(jQuery, Drupal);
