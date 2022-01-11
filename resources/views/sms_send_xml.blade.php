<?php echo '<?xml version="1.0" encoding="UTF-8" ?>'?>

<TELEMESSAGE>
    <TELEMESSAGE_CONTENT>
        <MESSAGE>
            <MESSAGE_INFORMATION>
                <SUBJECT></SUBJECT>
            </MESSAGE_INFORMATION>
            <USER_FROM>
                <CIML>
                    <NAML>
                        <LOGIN_DETAILS>
                            <USER_NAME>{{$user}}</USER_NAME>
                            <PASSWORD>{{$passwd}}</PASSWORD>
                        </LOGIN_DETAILS>
                    </NAML>
                </CIML>
            </USER_FROM>
            <MESSAGE_CONTENT>
                <TEXT_MESSAGE>
                    <MESSAGE_INDEX>{{$index}}</MESSAGE_INDEX>
                    <TEXT>{{$smsText}}</TEXT>
                </TEXT_MESSAGE>
            </MESSAGE_CONTENT>
            <USER_TO>
                <CIML>
                    <DEVICE_INFORMATION>
                        <DEVICE_TYPE DEVICE_TYPE="SMS"/>
                        <DEVICE_VALUE>{{$mobileNo}}</DEVICE_VALUE>
                    </DEVICE_INFORMATION>
                </CIML>
            </USER_TO>
        </MESSAGE>
    </TELEMESSAGE_CONTENT>
    <VERSION>1.6</VERSION>
</TELEMESSAGE>