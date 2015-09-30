<?php

App::uses('RegisteredMail', 'Postman.Network/Email');
App::uses('HttpSocket', 'Network/Http');

class CensorshipModeTest extends CakeTestCase
{
    public $fixtures = array(
        'plugin.postman.registered_mail_log',
    );

    public function setUp() {
        Configure::write('Postman.censorship.mode', false);
        $this->email = new RegisteredMail();
        $this->email->config(array(
            'transport' => 'Smtp',
            'from' => array('form@example.com' => 'from'),
            'to' => 'to+unknown-to@mailback.me',
            'cc' => 'to+unknown-cc@mailback.me',
            'bcc' => 'to+unknown-bcc@mailback.me',
            'host' => 'mail.mailback.me',
            'port' => 25,
            'timeout' => 30,
            'log' => false,
            'charset' => 'utf-8',
            'headerCharset' => 'utf-8',
        ));
    }

    public function tearDown() {
        unset($this->email);
    }

    /**
     * test_CensorshipOn
     *
     */
    public function test_Censorship(){
        $hash = sha1(uniqid('',true));
        $to = 'to+'.$hash.'@mailback.me';

        Configure::write('Postman.censorship.mode', true);
        Configure::write('Postman.censorship.config', array(
            'transport' => 'Smtp',
            'from' => array('form@mailback.me' => 'from'),
            'to' => $to,
            'host' => 'mail.mailback.me',
            'port' => 25,
            'timeout' => 30,
            'log' => false,
            'charset' => 'utf-8',
            'headerCharset' => 'utf-8',
        ));

        $message = 'Censored';
        $expect = $message;
        $this->email->subject('メールタイトル');
        $this->email->send($expect);

        sleep(5);

        $url = 'http://mailback.me/to/'.$hash.'.body';

        App::uses('HttpSocket', 'Network/Http');
        $HttpSocket = new HttpSocket();
        $results = $HttpSocket->get($url, array());

        $body = trim(str_replace(array("\r\n", "\n", ' '), '', $results->body));
        $message = trim(str_replace(array("\r\n", "\n", ' '), '', $message));

        $this->assertIdentical($body, $message);
    }
}
