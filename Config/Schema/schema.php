<?php
class PostmanSchema extends CakeSchema
{

    public function before($event = array())
    {
        return true;
    }

    public function after($event = array())
    {
        return true;
    }

    public $registered_mail_logs = array(
        'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
        'from' => array('type' => 'text', 'null' => true, 'default' => null),
        'sender' => array('type' => 'text', 'null' => true, 'default' => null),
        'reply_to' => array('type' => 'text', 'null' => true, 'default' => null),
        'read_receipt' => array('type' => 'text', 'null' => true, 'default' => null),
        'return_path' => array('type' => 'text', 'null' => true, 'default' => null),
        'to' => array('type' => 'text', 'null' => true, 'default' => null),
        'cc' => array('type' => 'text', 'null' => true, 'default' => null),
        'bcc' => array('type' => 'text', 'null' => true, 'default' => null),
        'headers' => array('type' => 'text', 'null' => true, 'default' => null),
        'headers_sent' => array('type' => 'text', 'null' => true, 'default' => null),
        'subject' => array('type' => 'text', 'null' => true, 'default' => null),
        'subject_raw' => array('type' => 'text', 'null' => true, 'default' => null),
        'message' => array('type' => 'text', 'null' => true, 'default' => null),
        'message_sent' => array('type' => 'text', 'null' => true, 'default' => null),
        'status' => array('type' => 'text', 'null' => true, 'default' => null),
        'error_message' => array('type' => 'text', 'null' => true, 'default' => null),
        'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
        'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
        'indexes' => array(
            'PRIMARY' => array('column' => 'id', 'unique' => 1)
        )
    );

}
