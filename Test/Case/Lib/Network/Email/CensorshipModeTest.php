<?php

App::uses('RegisteredMail', 'Postman.Network/Email');
App::uses('HttpSocket', 'Network/Http');

class CensorshipModeTest extends CakeTestCase
{
    public $fixtures = array(
        // 'plugin.postman.registered_mail_log',
    );

    public function setUp() {
        Configure::write('Postman.censorship.mode', false);
        $this->hash = sha1(uniqid('',true));
        $this->email = new RegisteredMail();
        $this->email->config(array(
            'transport' => 'Smtp',
            'from' => array('form@example.com' => 'from'),
            'to' => 'to+unknown-to@mailback.me',
            'cc' => array('to+unknown-cc-' . $this->hash . '@mailback.me'),
            'bcc' => array('to+unknown-bcc-' . $this->hash . '@mailback.me'),
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
     * test_Censorship
     *
     */
    public function test_Censorship(){
        $hash = $this->hash;
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

        sleep(15);

        $url = 'http://mailback.me/to/'.$hash.'.body';

        App::uses('HttpSocket', 'Network/Http');
        $HttpSocket = new HttpSocket();
        $results = $HttpSocket->get($url, array());

        $body = trim(str_replace(array("\r\n", "\n", ' '), '', $results->body));
        $message = trim(str_replace(array("\r\n", "\n", ' '), '', $message));

        $this->assertIdentical($results->code, '200');
        $this->assertIdentical($body, $message);

        // CC
        $ccUrl = 'http://mailback.me/to/unknown-cc-'.$hash.'.body';
        $results = $HttpSocket->get($ccUrl, array());
        $this->assertContains('404', $results->body);
        
        // BCC
        $bccUrl = 'http://mailback.me/to/unknown-bcc-'.$hash.'.body';
        $results = $HttpSocket->get($bccUrl, array());
        $this->assertContains('404', $results->body);
    }
}
